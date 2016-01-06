<?php
namespace Craft;

class SproutInvisibleCaptcha_InvisibleCaptchaFieldType extends BaseFieldType
{
	/**
	 * Fieldtype name
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Invisible Captcha');
	}

	/**
	 * @return array
	 */
	public function defineSettings()
	{
		return array(
			'allowEdits' => array(AttributeType::Bool),
			'value' => array(AttributeType::String),
		);
	}

	/**
	 * @return string
	 */
	public function getInputHtml($name, $value)
	{
		$vars = array(
			'id'       => $name,
			'name'     => $name,
			'value'    => $value,
			'settings' => $this->getSettings()
		);
		//sproutinvisiblecaptcha/_fields/input
		return craft()->templates->render('sproutinvisiblecaptcha/_fieldtypes/invisiblecaptcha/input', $vars);
	}

	/**
	 * @return string
	 */
	public function getSettingsHtml()
	{
		$vars = array(
			'settings' => $this->getSettings(),
		);

		return craft()->templates->render('sproutinvisiblecaptcha/_fieldtypes/invisiblecaptcha/settings', $vars);
	}

	/**
	 * Prepare our field for the page
	 *
	 * Since we don't store any data, all this does is output the invisible Captcha
	 * Global settings.  @TODO - in the future, we will allow someone to customize
	 * the captcha in their field and output a captcha based on their field settings.
	 *
	 * @return Invisible Captcha Output
	 */
	public function prepValue($value)
	{
		return craft()->sproutInvisibleCaptcha->getProtection();
	}
}
