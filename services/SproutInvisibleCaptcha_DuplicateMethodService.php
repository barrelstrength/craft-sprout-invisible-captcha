<?php
namespace Craft;

class SproutInvisibleCaptcha_DuplicateMethodService extends BaseApplicationComponent implements SproutInvisibleCaptcha_MethodInterfaceService
{
	public function verifySubmission()
	{		
		if(isset($_SESSION['form_token']))
		{	
			// If there is a valid unique token set, unset it and return true.		
			unset($_SESSION['form_token']);		
			return true;			
		}		 
		else
		{
			// If there is no token set fail the method to prevent duplicate submission, log in the database, and return false
			craft()->sproutInvisibleCaptcha->duplicateMethodFailed = 1;
			return false;
		}
	}

	public function getProtection()
	{	 						
		// Create the unique token 
		$form_token = uniqid();

		// Create session variable
		$_SESSION['form_token'] = $form_token;		

		return $this->getField();
	}

	public function getField()
	{		
	}

}
