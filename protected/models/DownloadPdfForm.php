<?php
/**
 * File: DownloadPdfForm.php
 *
 * @package V_WP_SEO_Audit
 */

class DownloadPdfForm extends CFormModel {
	public $validation;

	/**
	 * rules function.
	 */
	public function rules() {
		$rules = array();
		if (Utils::isRecaptchaEnabled()) {
			$rules[] = array(
				'validation',
				'ext.recaptcha2.ReCaptcha2Validator',
				'privateKey' => Yii::app()->params['recaptcha.private'],
				'message'    => Yii::t( 'app', "Please confirm you're not a robot" ),
			);
		}
		return $rules;
	}

	/**
	 * attributeLabels function.
	 */
	public function attributeLabels() {
		return array(
			'validation' => Yii::t( 'app', "Please confirm you're not a robot" ),
		);
	}
}
