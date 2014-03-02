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
	 * Define database column
	 *
	 * @return AttributeType::String
	 */
	public function defineContentAttribute()
	{
		return false;
	}

	/**
	 * Display our fieldtype
	 *
	 * @param string $name  Our fieldtype handle
	 * @return string Return our fields input template
	 */
	public function getInputHtml($name, $value)
	{   
		return craft()->templates->render('sproutinvisiblecaptcha/_fields/input', array(
			'name'  => $name,
			'value' => $value
		));
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
