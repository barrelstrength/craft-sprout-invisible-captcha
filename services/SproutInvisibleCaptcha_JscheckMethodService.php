<?php
namespace Craft;

class SproutInvisibleCaptcha_JscheckMethodService extends BaseApplicationComponent implements SproutInvisibleCaptcha_MethodInterfaceService
{
	public function verifySubmission()
	{		
		$jsset = craft()->request->getPost('__JSCHK');
 
		if(strlen($jsset) > 0)
		{	
			// If there is a valid unique token set, unset it and return true.
			// This token was created and set by javascript.		
			unset($_SESSION['form_js']);		
			return true;			
		}		 
		else
		{
			// If there is no token, set to fail; javascript is not present
			craft()->sproutInvisibleCaptcha->duplicateMethodFailed = 1;
			return false;
		}
	}

	public function getProtection()
	{	 						
		// Create the unique token 
		$form_token = uniqid();

		// Create session variable to test for javascript
		$_SESSION['form_js'] = $form_token;		

		return $this->getField();
	}

	public function getField()
	{			 
		// Set a hidden field with no value and use javascript to set it.
		$output = '';		
		$output .= sprintf('<input type="hidden" id="__JSCHK" name="__JSCHK" />');
		$output .= sprintf('<script type="text/javascript">document.getElementById("__JSCHK").value = "%s";</script>', $_SESSION['form_js']); 
 		
		return $output;
	}

}