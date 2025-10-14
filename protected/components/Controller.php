<?php
/**
 * File: Controller.php
 *
 * @package V_WP_SEO_Audit
 */
class Controller extends CController {

	/**
	 * Page title.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * init function.
	 */
	public function init() {
		parent::init();
		// Language is now hardcoded to English, no need to setup
		$this->registerJsGlobalVars();
		CHtml::$errorCss = 'is-invalid';
	}

	/**
	 * registerJsGlobalVars function.
	 */
	protected function registerJsGlobalVars() {
		$baseUrl = Yii::app()->request->getBaseUrl( true );
		Yii::app()->clientScript->registerScript(
			'globalVars',
			"
			var _global = {
				baseUrl: '{$baseUrl}',
				proxyImage: " . (int) Yii::app()->params['thumbnail.proxy'] . '
			};
		',
			CClientScript::POS_HEAD
		);
	}

	/**
	 * setupLanguage function.
	 * Note: Language support removed - English is hardcoded.
	 * This method is kept for backwards compatibility but does nothing.
	 */
	protected function setupLanguage() {
		// Language is now hardcoded to English
		Yii::app()->language = 'en';
	}

	/**
	 * jsonResponse function.
	 *
	 * @param mixed $response Parameter.
	 */
	public function jsonResponse( $response) {
		header( 'Content-type: application/json' );
		echo json_encode( $response );
		Yii::app()->end();
	}
}
