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
		return '0.5.2';
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
			'methodOptions'	=> array( AttributeType::Mixed, 'default' => array('elapsedTime'=>5) ),
			'logFailedSubmissions'	=> array( AttributeType::String ),
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

		// @TODO - only process Invisible Captcha when appropriate

		// How do we only check for this if Senor Form is 
		// using Invisible Captcha?  Right now it does it 
		// every time?

		//  We can check to make sure we have an invisibleCaptcha field?
		// Is this reliable?  What if someone submitted a form directly?
		// Señor Form wouldn't know to check for the Invisible Captcha field?

		// What if we make Invisible Captcha a fieldtype?  Then Señor Form will
		// know it exists.. but we might need to do processing a bit differently
		// as it won't know until later after the form is submitted.

		$useInvisibleCaptcha = false;

		switch (true) {
			case (isset($_POST['__UATIME']) ? true : false):
				$useInvisibleCaptcha = true;
				break;

			case (isset($_POST['__UAHOME']) ? true : false):
				$useInvisibleCaptcha = true;
				break;

			case (isset($_POST['__UAHASH']) ? true : false):
				$useInvisibleCaptcha = true;
				break;

			case (isset($_POST['chp']) ? true : false):
				$useInvisibleCaptcha = true;
				break;
			
			default:
				# code...
				break;
		}
		
		if ($useInvisibleCaptcha == true)
		{
			craft()->sproutInvisibleCaptcha->verifySubmission();	
		}
		
	}

	// @TODO - what is this for?
	public function verifyCaptchaSubmission()
	{
		return craft()->sproutInvisibleCaptcha->verifySubmission();
	}
}

//------------------------------------------------------------
// Change Log
//------------------------------------------------------------
/*

v0.5.2
Added: Log now records which tests were failed
Added: Logging can be turned on or off in plugin settings
Improved: Elapsed time settings now auto-expand if selected by default
Improved: Senor Form hook now checks for Invisible Captcha 
					fields before trying to validate

v0.5.1
Added: Add sproutinvisiblecaptcha_log table
Added: rejectSubmission() function logs submissions that get blocked
Improved: Rename plugin from InvisibleCaptcha => SproutInvisibleCaptcha
Fixed: rejectSubmission() function now tries to redirect in the following 
			 order: 1) redirectOnFailure value 2) redirect value 3) request path
			 and exits just to make sure the script doesn't continue

v0.5.0
Begin tracking version numbers

*/