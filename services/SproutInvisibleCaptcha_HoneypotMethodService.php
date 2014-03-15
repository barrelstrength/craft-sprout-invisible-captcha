<?php
namespace Craft;

class SproutInvisibleCaptcha_HoneypotMethodService extends BaseApplicationComponent
{

	public function verifySubmission()
	{
		// The honeypot field must be left blank
		if ( craft()->request->getPost('chp') ) {
			craft()->sproutInvisibleCaptcha->honeypotMethodFailed = 1;
			return false;
		}

		return true;
	}

	public function getProtection()
	{
		return $this->getField();
	}

	public function getField()
	{
		$honeypot = '<div class="chp">'.
					'<label for="chp">Leave this field blank</label>'.
					'<input type="text" id="chp" name="chp" />'.
					'</div><style>.chp{ display: none; }</style>';

		return $honeypot;
	}
	
}
