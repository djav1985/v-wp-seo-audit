<?php
/**
 * File: Controller.php
 *
 * @package v_wpsa
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
		$this->setupLanguage();
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
	 * 
	 * Simplified to use default language only since multi-language is disabled.
	 */
	protected function setupLanguage() {
		// Multi-language support is disabled, use default language only
		$lang = Yii::app()->params['app.default_language'];
		Yii::app()->language = $lang;
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
