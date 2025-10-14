<?php
/**
 * File: ParseController.php
 *
 * @package V_WP_SEO_Audit
 */
class ParseController extends Controller {

	/**
	 * filters function.
	 */
	public function filters() {
		return Yii::app()->params['param.instant_redirect'] ? array( 'ajaxOnly + index' ) : array();
	}

	/**
	 * actionIndex function.
	 */
	public function actionIndex() {
		$model = new WebsiteForm();
		$this->performValidation( $model );
	}

	/**
	 * performValidation function.
	 *
	 * @param mixed $model Parameter.
	 */
	protected function performValidation( $model) {
		if (isset( $_GET['Website'] ) and is_array( $_GET['Website'] )) {
			$model->attributes = $_GET['Website'];
			if ( ! $model->validate()) {
				echo json_encode( $model->getErrors() );
			} else {
				$url     = $this->createAbsoluteUrl( 'websitestat/generateHTML', array( 'domain' => $model->domain ) );
				$instant = (bool) Yii::app()->request->getQuery( 'redirect' );
				if ( ! $instant) {
					echo json_encode( $url );
				} else {
					$this->redirect( $url );
				}
			}
		}
	}

	/**
	 * actionPagespeed function.
	 */
	public function actionPagespeed() {
		Yii::import( 'application.vendors.Webmaster.Google.*' );

		$domain = Yii::app()->request->getQuery( 'domain' );
		
		// Use WordPress native database class.
		if ( ! class_exists( 'V_WP_SEO_Audit_DB' ) ) {
			$this->jsonResponse(
				array(
					'error' => array( 'Database error' ),
				)
			);
			return;
		}
		
		$db      = new V_WP_SEO_Audit_DB();
		$website = $db->get_website_by_domain( $domain );
		
		if ( ! $website) {
			throw new CHttpException( 404, "The page you are looking for doesn't exists" );
		}
		$wid = $website['id'];

		if ($results = $this->getPageSpeedResults( $wid )) {
			if (empty( $results )) {
				$this->jsonResponse(
					array(
						'error' => array( 'Unable to pull Page Speed Insight report' ),
					)
				);
			} else {
				$this->jsonResponse(
					array(
						'content' => $this->renderPartial(
							'//websitestat/pagespeed_web',
							array(
								'results' => $results,
								'website' => $website,
							),
							true
						),
					)
				);
			}
		}

		$lang_id = 'en';

		$p = new PageSpeedInsights( $domain, Yii::app()->params['googleApiKey'] );
		$p->setLocale( 'en' );
		$results    = $p->getResults();
		$jsonResult = @json_encode( $results );

		try {
			$db->upsert_pagespeed( $wid, $jsonResult, $lang_id );
			if ( ! empty( $results )) {
				$this->jsonResponse(
					array(
						'content' => $this->renderPartial(
							'//websitestat/pagespeed_web',
							array(
								'results' => $results,
								'website' => $website,
							),
							true
						),
					)
				);
			} else {
				$this->jsonResponse(
					array(
						'error' => array( 'Unable to pull Page Speed Insight report' ),
					)
				);
			}
		} catch (Exception $e) {
			$this->jsonResponse(
				array(
					'error' => array( 'Server temporary unavailable' ),
				)
			);
		}
	}

	/**
	 * getPageSpeedResults function.
	 *
	 * @param mixed $wid Parameter.
	 */
	protected function getPageSpeedResults( $wid) {
		// Use WordPress native database class.
		if ( ! class_exists( 'V_WP_SEO_Audit_DB' ) ) {
			return array();
		}
		
		$db      = new V_WP_SEO_Audit_DB();
		$results = $db->get_pagespeed_data( $wid, 'en' );
		return @json_decode( $results, true );
	}
}
