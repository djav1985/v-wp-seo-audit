<?php
/**
 * File: UrlManager.php
 *
 * @package V_WP_SEO_Audit
 */

class UrlManager extends CUrlManager {

	protected function processRules() {
		$this->rules = Yii::app()->params['url.multi_language_links'] ? $this->getMultiLanguageRules() : $this->getSingleLanguageRules();
		parent::processRules();
	}

	public function createUrl( $route, $params = array(), $ampersand = '&') {
		if ( ! isset( $params['language'] )) {
			$params['language'] = Yii::app()->language;
		}
		if ( ! Yii::app()->params['url.multi_language_links']) {
			unset( $params['language'] );
		}
		return parent::createUrl( $route, $params, $ampersand );
	}


	private function getMultiLanguageRules() {
		return array(
			'proxy'                                     => 'PagePeekerProxy/index',
			'<language:\w{2}>'                          => 'site/index',
			'<language:\w{2}>/www/<domain:[\w\d\-\.]+>' => 'websitestat/generateHTML',
			'<language:\w{2}>/pdf-review/<domain:[\w\d\-\.]+>.pdf' => 'websitestat/generatePDF',
			'<language:\w{2}>/<controller:[\w\-]+>/<action:[\w\-]+>/<id:\d+>' => '<controller>/<action>',
			'<language:\w{2}>/<controller:[\w\-]+>/<action:[\w\-]+>' => '<controller>/<action>',
			'<language:\w{2}>/<controller:[\w\-]+>'     => '<controller>/index',
			// Catch-all: route unknown controllers to site/index.
			'<language:\w{2}>/<_c:.+>'                  => 'site/index',
		);
	}

	private function getSingleLanguageRules() {
		return array(
			'proxy'                                 => 'PagePeekerProxy/index',
			''                                      => 'site/index',
			'www/<domain:[\w\d\-\.]+>'              => 'websitestat/generateHTML',
			'pdf-review/<domain:[\w\d\-\.]+>.pdf'   => 'websitestat/generatePDF',
			'<controller:[\w\-]+>/<action:[\w\-]+>/<id:\d+>' => '<controller>/<action>',
			'<controller:[\w\-]+>/<action:[\w\-]+>' => '<controller>/<action>',
			'<controller:[\w\-]+>'                  => '<controller>/index',
			// Catch-all: route unknown controllers to site/index.
			'<_c:.+>'                               => 'site/index',
		);
	}
}
