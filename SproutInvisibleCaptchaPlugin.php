<?php
namespace Craft;

class SproutInvisibleCaptchaPlugin extends BasePlugin
{
	public function getName()
	{
		return Craft::t('Sprout Invisible Captcha');
	}

	public function getVersion()
	{
		return '0.5.3';
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

	//------------------------------------------------------------

	public function defineSettings()
	{
		return array(
			'captchaMethod'	=> array( AttributeType::String, 'default' => 'full'),
			'methodOptions'	=> array( AttributeType::Mixed, 'default' => array('elapsedTime'=>5) ),
			'logFailedSubmissions' => array( AttributeType::String ),
		);
	}

	//------------------------------------------------------------

	public function getSettingsHtml()
	{
		return craft()->templates->render('sproutinvisiblecaptcha/settings/index', array(
			'settings' => $this->getSettings()
		));
	}

	public function prepSettings($settings)
	{
		$methodArray = $settings['captchaMethod'];
		$methodString = implode('|', $methodArray);

		if ( in_array('time', $methodArray) && in_array('origin', $methodArray) && in_array('honeypot', $methodArray) ) {
			$methodString = 'full';
		}

		$settings['captchaMethod'] = $methodString;

		return $settings;
	}

	//----------------------------------------------------------------
	// @=HOOKS
	//----------------------------------------------------------------
	
	/**
	 * Initialize our plugin to support several events
	 */
	public function init()
	{
		// Support Sprout Forms plugin
    craft()->on('sproutForms.onBeforeSubmitForm', function(SproutForms_OnBeforeSubmitFormEvent $event) {
    	craft()->sproutInvisibleCaptcha->verifySubmission();
    });

		// Support P&T Contact Form plugin
    craft()->on('contactForm.beforeSend', function(ContactFormEvent $event) {
    	craft()->sproutInvisibleCaptcha->verifySubmission();
    });

    // Support P&T Guest Entries plugin
    craft()->on('guestEntries.beforeSave', function(GuestEntriesEvent $event) {
    	craft()->sproutInvisibleCaptcha->verifySubmission();
    });
	}

	/**
	 * @DEPRECATED - Setup Invisible Captcha to work with Sprout Forms
	 * Use sproutForms.onBeforeSubmitForm Event instead
	 * 
	 * @return true or redirect Allow form to post if clear, otherwise redirect
	 */
	public function sproutFormsPrePost()
	{
		$this->_verifySubmission();
	}

	private function _verifySubmission()
	{
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
}
