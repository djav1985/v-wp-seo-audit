<?php
/**
 * File: Controller.php
 *
 * @package V_WP_SEO_Audit
 */
class Controller extends CController {

	public $title;

	public function init() {
		parent::init();
		$this->setupLanguage();
		$this->registerJsGlobalVars();
		CHtml::$errorCss = 'is-invalid';
	}

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

	protected function setupLanguage() {
		$languages    = Yii::app()->params['app.languages'];
		$request_lang = Yii::app()->request->getQuery( 'language' );

		if ( ! Yii::app()->params['url.multi_language_links']) {
			$lang = Yii::app()->language;
		} elseif (isset( $languages[ $request_lang ] )) {
			$lang                                    = $request_lang;
			$cookie                                  = new CHttpCookie( 'language', $lang );
			$cookie->sameSite                        = Yii::app()->params['cookie.same_site'];
			$cookie->path                            = Yii::app()->params['app.base_url'];
			$cookie->secure                          = Yii::app()->params['cookie.secure'];
			$cookie->expire                          = time() + ( 60 * 60 * 24 * 365 );
			Yii::app()->request->cookies['language'] = $cookie;
		} elseif (isset( Yii::app()->request->cookies['language'] )) {
			$lang = Yii::app()->request->cookies['language']->value;
		} else {
			$lang = Yii::app()->getRequest()->getPreferredLanguage();
		}

		if ( ! isset( $languages[ $lang ] )) {
			$lang = Yii::app()->params['app.default_language'];
		}
		Yii::app()->language = $lang;
	}

	public function jsonResponse( $response) {
		header( 'Content-type: application/json' );
		echo json_encode( $response );
		Yii::app()->end();
	}
}
