<?php
/**
 * File: SiteController.php
 *
 * @package v_wpsa
 */

class SiteController extends Controller {

	/**
	 * actionIndex function.
	 */
	public function actionIndex() {
		$brandname = Yii::app()->name;
		$this->title = $brandname . ' free seo tool';

		Yii::app()->clientScript->registerMetaTag( 'website review, website content analyser, free seo tool, content checker, content analysis, content analyzer', 'keywords' );
		Yii::app()->clientScript->registerMetaTag( $brandname . ' is a free seo tool which will help you analyse your web page', 'description' );

		Yii::app()->clientScript->registerMetaTag( $brandname . ' free seo tool', null, null, array( 'property' => 'og:title' ) );
		Yii::app()->clientScript->registerMetaTag( $brandname . ' is a free seo tool which will help you analyse your web page', null, null, array( 'property' => 'og:description' ) );
		Yii::app()->clientScript->registerMetaTag( Yii::app()->name, null, null, array( 'property' => 'og:site_name' ) );
		Yii::app()->clientScript->registerMetaTag( Yii::app()->getBaseUrl( true ) . '/assets/img/logo.png', null, null, array( 'property' => 'og:image' ) );

		$widget = $this->widget(
			'application.widgets.WebsiteList',
			array(
				'config' => array(
					'totalItemCount' => Yii::app()->params['param.index_website_count'],
					'pagination'     => array(
						'pageSize' => Yii::app()->params['param.index_website_count'],
					),
				),
			),
			true
		);

		$this->render(
			'index',
			array(
				'widget' => $widget,
			)
		);
	}

	/**
	 * actionError function.
	 */
	public function actionError() {
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest) {
				echo $error['message'];
			} else {
				$this->render( 'error', $error );
			}
		}
	}
}
