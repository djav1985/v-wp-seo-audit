<?php
class LanguageSelector extends CWidget {
	public function run() {
		$currentLang = Yii::app() -> language;
		$languages = Yii::app() -> params['app.languages'];

        if(count($languages) < 2 OR !Yii::app()->params['url.multi_language_links'] OR Yii::app()->errorHandler->error) {
            return null;
        }
		$this -> render ("languageSelector", array(
			'currentLang' => $currentLang,
			'languages' => $languages,
		));
	}
}