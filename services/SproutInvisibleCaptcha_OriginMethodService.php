<?php
namespace Craft;

class SproutInvisibleCaptcha_OriginMethodService extends BaseApplicationComponent implements SproutInvisibleCaptcha_MethodInterfaceService
{
	
	public function verifySubmission()
	{
		return true;
	}

	public function getProtection()
	{
		return $this->getField();
	}

	public function getField()
	{
		$output = '';

		return $output;
	}
}
