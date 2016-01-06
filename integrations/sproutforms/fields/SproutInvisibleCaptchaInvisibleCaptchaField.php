<?php
namespace Craft;

/**
 *
 */
class SproutInvisibleCaptchaInvisibleCaptchaField extends SproutInvisibleCaptchaBaseField
{

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'SproutInvisibleCaptcha_InvisibleCaptcha';
	}

	public function isPlainInput()
	{
		return true;
	}

	/**
	 * @param FieldModel $field
	 * @param mixed      $value
	 * @param mixed      $settings
	 * @param array|null $renderingOptions
	 *
	 * @return \Twig_Markup
	 */
	public function getInputHtml($field, $value, $settings, array $renderingOptions = null)
	{
		$this->beginRendering();

		try
		{
			$value = craft()->templates->renderObjectTemplate($settings['value'], parent::getFieldVariables());
		}
		catch (\Exception $e)
		{
			SproutFieldsPlugin::log($e->getMessage(), LogLevel::Error);
		}

		$rendered = craft()->templates->render(
			'invisiblecaptcha/input',
			array(
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'renderingOptions' => $renderingOptions
			)
		);

		$this->endRendering();

		return TemplateHelper::getRaw($rendered);
	}

}
