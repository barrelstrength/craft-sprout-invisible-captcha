<?php
namespace Craft;

/**
 * Class SproutFieldsBaseField
 *
 * @package Craft
 */
abstract class SproutInvisibleCaptchaBaseField extends SproutFormsBaseField
{
	/**
	 * @return string
	 */
	public function getTemplatesPath()
	{
		return craft()->path->getPluginsPath().'sproutinvisiblecaptcha/templates/_integrations/sproutforms/fields/';
	}
}
