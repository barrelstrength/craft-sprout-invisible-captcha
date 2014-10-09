<?php
namespace Craft;

/**
 *
 */
class SproutInvisibleCaptcha_InvisibleCaptcha_SproutFormsFieldType extends BaseSproutFormsFieldType
{
	public $isNakedField = true;
	
	/**
	 * Returns the field's input HTML.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @return string
	 */
	public function getInputHtml($field, $value, $settings)
	{
		return craft()->templates->render('fields/sproutinvisiblecaptcha_invisiblecaptcha/input', array(
			'name'  => $field->handle,
			'value'=> $value,
		));
	}

	// Don't wrap invisible captcha in the 'fields' namespace
	public function getNamespace()
	{
		return false;
	}

}
