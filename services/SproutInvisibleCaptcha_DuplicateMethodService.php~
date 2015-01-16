<?php
namespace Craft;

class SproutInvisibleCaptcha_DuplicateMethodService extends BaseApplicationComponent implements SproutInvisibleCaptcha_MethodInterfaceService
{
	//basically we give the form a unique id and clear it after a successful submit.
	//if the form is submited more than once then the 'form_token' doesn't get set properly thus 
	//automatically returning a spam report the second time. Only the first submission will be recorded.
	public function verifySubmission()
	{		
		if(isset($_SESSION['form_token']))
		{			
			unset($_SESSION['form_token']);		
			return true;			
		}		 
		else
		{
			craft()->sproutInvisibleCaptcha->duplicateMethodFailed = 1;
			return false;
		}
	}

	public function getProtection()
	{	 						
		/*** create the unique token  ***/
		$form_token = uniqid();

		/*** create session variable ***/
		$_SESSION['form_token'] = $form_token;		

		return $this->getField();
	}

	public function getField()
	{		
	}

}
