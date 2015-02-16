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
		return '0.6.2'; 
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
			'methodOptions'	=> array( AttributeType::Mixed, 'default' => array(
				'elapsedTime' => 3,
				'honeypotFieldName' => 'beesknees',
				'honeypotScreenReaderMessage' => 'Leave this field blank',
			)),
			'logFailedSubmissions' => array( AttributeType::String ),
			'sproutFormsDisplayFormTagOutput' => array( AttributeType::Bool ),
		);
	}

	//------------------------------------------------------------

	public function getSettingsHtml()
	{
		return craft()->templates->render('sproutinvisiblecaptcha/_cp/settings', array(
			'settings' => $this->getSettings()
		));
	}

	public function prepSettings($settings)
	{
		// Check if there is any selection made in options menu.	
		if(!isset($settings['captchaMethod']))
		{
			$settings['captchaMethod'] = 'none';
			return $settings;
		}	

		// If options are selected continue
		$methodArray = $settings['captchaMethod'];
		$methodString = implode('|', $methodArray);

		if ( in_array('time', $methodArray) && in_array('origin', $methodArray) 
			&& in_array('honeypot', $methodArray) && in_array('duplicate', $methodArray)
			&& in_array('javascript', $methodArray)  ) 
		{
			$methodString = 'full';
		} 

		$settings['captchaMethod'] = $methodString;
 
		return $settings;
	}

	//----------------------------------------------------------------
	// @=HOOKS
	//----------------------------------------------------------------

	/**
	 * Adds support for Sprout Forms front-end field
	 * @return class name
	 */
	public function registerSproutField()
	{
		return 'SproutInvisibleCaptcha_InvisibleCaptcha';
	}

	/**
	 * Initialize our plugin to support several events
	 */
	public function init()
	{
		// Support Sprout Forms plugin
		craft()->on('sproutForms.beforeSaveEntry', function(SproutForms_OnBeforeSaveEntryEvent $event) {
			$event->isValid = craft()->sproutInvisibleCaptcha->verifySubmission(true);

			if (!$event->isValid) 
			{
				$event->fakeIt = true;

				if (craft()->request->getPost('redirectOnFailure') != "") 
				{
					$_POST['redirect'] = craft()->request->getPost('redirectOnFailure');
				}
			}

			return $event;
		});

		$self = $this;

		// Support for displayForm() tag Invisible Captcha output via Hook (if enabled)
		craft()->templates->hook('sproutForms.modifyForm', function(&$context) use($self)
		{
			if ($self->getSettings()->sproutFormsDisplayFormTagOutput)
			{
				return craft()->sproutInvisibleCaptcha->getProtection();
			}
		});

		// Support P&T Contact Form plugin
		craft()->on('contactForm.beforeSend', function(ContactFormEvent $event) {
			$event->isValid = craft()->sproutInvisibleCaptcha->verifySubmission(true);

			if (!$event->isValid) 
			{
				$event->fakeIt = true;

				if (craft()->request->getPost('redirectOnFailure') != "") 
				{
					$_POST['redirect'] = craft()->request->getPost('redirectOnFailure');
				}
			}

			return $event;
		});

		// Support P&T Guest Entries plugin
		craft()->on('guestEntries.beforeSave', function(GuestEntriesEvent $event) {
			$event->isValid = craft()->sproutInvisibleCaptcha->verifySubmission(true);

			if (!$event->isValid) 
			{
				$event->fakeIt = true;

				if (craft()->request->getPost('redirectOnFailure') != "") 
				{
					$_POST['redirect'] = craft()->request->getPost('redirectOnFailure');
				}
			}
			
			return $event;
		});

		// @TODO - Add support for User Registration
		// Support User Registration
		// craft()->on('users.onBeforeSaveUser', function(Event $event) {

		// 	$isValid = craft()->sproutInvisibleCaptcha->verifySubmission(true);

		// 	if (!$isValid) 
		// 	{
		// 		if (craft()->request->getPost('redirectOnFailure') != "") 
		// 		{
		// 			$url = craft()->request->getPost('redirectOnFailure');
		// 		}
		// 		else
		// 		{
		// 			$url = craft()->request->getPost('redirect');
		// 		}

		// 		// craft()->request->redirect($url);

		// 		// $route = craft()->urlManager->parseUrl(craft()->request);
		// 		// craft()->runController($route);
		// 		// craft()->end();
		// 	}
		// });
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
		$honeypotFieldName = craft()->sproutInvisibleCaptcha->getMethodOption('honeypotFieldName');

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

			case (isset($_POST[$honeypotFieldName]) ? true : false):
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
