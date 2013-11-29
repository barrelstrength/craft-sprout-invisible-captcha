<?php
namespace Craft;

class SproutInvisibleCaptchaVariable
{
	public function getName()
	{
		return craft()->plugins->getPlugin('sproutInvisibleCaptcha')->getName();
	}

	public function getVersion()
	{
		return craft()->plugins->getPlugin('sproutInvisibleCaptcha')->getVersion();
	}

	public function setCaptcha( $methodString='' )
	{
		return craft()->sproutInvisibleCaptcha->getProtection($methodString);
	}

	public function isMethodSet( $methodName )
	{
		return craft()->sproutInvisibleCaptcha->isMethodSet($methodName);
	}

	public function getMethodOption( $option )
	{
		return craft()->sproutInvisibleCaptcha->getMethodOption($option);
	}

	public function hasMethodOption( $option )
	{
		return craft()->sproutInvisibleCaptcha->hasMethodOption($option);
	}
}
