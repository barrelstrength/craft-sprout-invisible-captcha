<?php
namespace Craft;

class SproutInvisibleCaptchaPlugin extends BasePlugin
{
	public function getName()
	{
		return Craft::t('Invisible Captcha');
	}

	public function getVersion()
	{
		return '0.5.1';
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
		return craft()->templates->render('sproutinvisiblecaptcha/settings/index.html', array(
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
		craft()->sproutInvisibleCaptcha->verifySubmission();
	}

	public function verifyCaptchaSubmission()
	{
		return craft()->sproutInvisibleCaptcha->verifySubmission();
	}
}

//------------------------------------------------------------
// Change Log
//------------------------------------------------------------
/*

v0.5.1
Added: Add sproutinvisiblecaptcha_log table
Added: rejectSubmission() function logs submissions that get blocked
Improved: Rename plugin from InvisibleCaptcha => SproutInvisibleCaptcha
Fixed: rejectSubmission() function now tries to redirect in the following 
			 order: 1) redirectOnFailure value 2) redirect value 3) request path
			 and exits just to make sure the script doesn't continue

v0.5.0
Begin tracking version numbers

/*