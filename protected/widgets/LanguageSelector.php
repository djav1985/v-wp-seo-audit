<?php
/**
 * File: LanguageSelector.php
 *
 * @package V_WP_SEO_Audit
 */

class LanguageSelector extends CWidget {
	public function run() {
		$currentLang = Yii::app()->language;
		$languages   = Yii::app()->params['app.languages'];

		if (count( $languages ) < 2 or ! Yii::app()->params['url.multi_language_links'] or Yii::app()->errorHandler->error) {
			return null;
		}
		$this->render(
			'languageSelector',
			array(
				'currentLang' => $currentLang,
				'languages'   => $languages,
			)
		);
	}
}
