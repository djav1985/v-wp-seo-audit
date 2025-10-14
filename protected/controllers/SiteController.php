<?php
/**
 * File: SiteController.php
 *
 * @package V_WP_SEO_Audit
 */

class SiteController extends Controller {

	/**
	 * actionIndex function.
	 */
	public function actionIndex() {
		$this->title = Yii::t( 'meta', 'Index page title', array( '{Brandname}' => Yii::app()->name ) );

		Yii::app()->clientScript->registerMetaTag( Yii::t( 'meta', 'Index page keywords' ), 'keywords' );
		Yii::app()->clientScript->registerMetaTag( Yii::t( 'meta', 'Index page description', array( '{Brandname}' => Yii::app()->name ) ), 'description' );

		Yii::app()->clientScript->registerMetaTag( Yii::t( 'meta', 'Og property title', array( '{Brandname}' => Yii::app()->name ) ), null, null, array( 'property' => 'og:title' ) );
		Yii::app()->clientScript->registerMetaTag( Yii::t( 'meta', 'Og property description', array( '{Brandname}' => Yii::app()->name ) ), null, null, array( 'property' => 'og:description' ) );
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

}
