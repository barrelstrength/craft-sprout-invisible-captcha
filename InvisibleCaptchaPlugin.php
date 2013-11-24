<?php
namespace Craft;

class InvisibleCaptchaPlugin extends BasePlugin
{
	public function getName()
	{
		return Craft::t('Invisible Captcha');
	}

	public function getVersion()
	{
		return '0.5.0';
	}

	public function getDeveloper()
	{
		return 'Barrel Strength Design';
	}

	public function getDeveloperUrl()
	{
		return 'http://barrelstrengthdesign.com';
	}

	public function hasCpSection()
	{
		return false;
	}

	//--------------------------------------------------------------------------------

	public function defineSettings()
	{
		return array(
			'captchaMethod'	=> array( AttributeType::String, 'default' => 'full'),
			'methodOptions'	=> array( AttributeType::Mixed, 'default' => array('elapsedTime'=>5) )
		);
	}

	//--------------------------------------------------------------------------------

	public function getSettingsHtml()
	{
		return craft()->templates->render('invisibleCaptcha/settings/index.html', array(
			'settings' => $this->getSettings()
		));
	}

	public function prepSettings($settings)
	{
		$methodArray 	= $settings['captchaMethod'];
		$methodString 	= implode('|', $methodArray);

		if ( in_array('time', $methodArray) && in_array('origin', $methodArray) && in_array('honeypot', $methodArray) ) {
			$methodString = 'full';
		}

		$settings['captchaMethod'] = $methodString;

		return $settings;
	}

	//----------------------------------------------------------------
	// @=HOOKS
	//----------------------------------------------------------------
	public function senorformPrePost()
	{
		craft()->invisibleCaptcha->verifySubmission();
	}

	public function verifyCaptchaSubmission()
	{
		return craft()->invisibleCaptcha->verifySubmission();
	}
}
