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

		$domain  = Yii::app()->request->getQuery( 'domain' );
		$website = Yii::app()->db->createCommand()
			->select( '*' )
			->from( '{{website}}' )
			->where(
				'md5domain=:md5domain',
				array(
					':md5domain' => md5( $domain ),
				)
			)
			->queryRow();
		if ( ! $website) {
			throw new CHttpException( 404, Yii::t( 'app', "The page you are looking for doesn't exists" ) );
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

		$lang_id = Yii::app()->language;

		$p = new PageSpeedInsights( $domain, Yii::app()->params['googleApiKey'] );
		$p->setLocale( Yii::app()->language );
		$results    = $p->getResults();
		$jsonResult = @json_encode( $results );

		try {
			$sql     = 'INSERT INTO {{pagespeed}} (wid, data, lang_id) VALUES (:wid, :data, :lang_id) ON DUPLICATE KEY UPDATE data=:data';
			$command = Yii::app()->db->createCommand( $sql );
			$command->bindParam( ':wid', $wid );
			$command->bindParam( ':data', $jsonResult );
			$command->bindParam( ':lang_id', $lang_id );
			$command->execute();
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
					'error' => array( Yii::t( 'app', 'Error Code 101' ) ),
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
		$results = Yii::app()->db->createCommand()->select( 'data' )->from( '{{pagespeed}}' )->where(
			'wid=:wid AND lang_id=:lang_id',
			array(
				':wid'     => $wid,
				':lang_id' => Yii::app()->language,
			)
		)->queryScalar();
		return @json_decode( $results, true );
	}
}
