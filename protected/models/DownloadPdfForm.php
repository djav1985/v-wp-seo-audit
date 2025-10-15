<?php
/**
 * File: DownloadPdfForm.php
 *
 * @package v_wpsa
 */

class DownloadPdfForm extends CFormModel {
	/**
	 * Validation token for reCAPTCHA.
	 *
	 * @var string
	 */
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
				'message'    => "Please confirm you're not a robot",
			);
		}
		return $rules;
	}

	/**
	 * attributeLabels function.
	 */
	public function attributeLabels() {
		return array(
			'validation' => "Please confirm you're not a robot",
		);
	}
}
