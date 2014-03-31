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
		return '0.5.5';
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
	
	public function init()
	{
	    craft()->on('contactForm.beforeSend', array($this, 'contactFormOnBeforeSend'));
	}
	
	/**
	 * Contact form handler
	 * 
	 * @param ContactFormEvent $event
	 * @return void
	 */
	public function contactFormOnBeforeSend(ContactFormEvent $event)
	{
	    // 'redirectOnFailure' and 'onSuccessRedirect' will cause a redirect directly from the service;
	    // we want to know the status instead, so we'll temporarily disable this by unsetting these vars
	    $onSuccessRedirect = craft()->request->getPost('onSuccessRedirect');
	    $redirectOnFailure = craft()->request->getPost('redirectOnFailure');
	    $redirect = craft()->request->getPost('redirect');
	    unset($_POST['onSuccessRedirect']);
	    unset($_POST['redirectOnFailure']);	    
	    unset($_POST['redirect']);
	    
	    if ( ! $this->sproutFormsPrePost()) {
	        $event->isValid = false; // problem
	    } else {
	        $event->isValid = true; // all good
	    }
	    
	    // put things back where we found them, just in case
	    $_POST['onSuccessRedirect'] = $onSuccessRedirect;
	    $_POST['redirectOnFailure'] = $redirectOnFailure;	
	    $_POST['redirect']          = $redirect;
	    
	    return $event;
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
	 * Setup Invisible Captcha to work with Sprout Forms
	 * 
	 * @return true or redirect Allow form to post if clear, otherwise redirect
	 */
	public function sproutFormsPrePost()
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
			return craft()->sproutInvisibleCaptcha->verifySubmission();	
		}	
	}
}
