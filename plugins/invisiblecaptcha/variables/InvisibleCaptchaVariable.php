<?php
namespace Craft;

class InvisibleCaptchaVariable
{
	public function getName()
	{
		return craft()->plugins->getPlugin('invisibleCaptcha')->getName();
	}

	public function getVersion()
	{
		return craft()->plugins->getPlugin('invisibleCaptcha')->getVersion();
	}

	public function setCaptcha( $methodString='' )
	{
		return craft()->invisibleCaptcha->getProtection($methodString);
	}

	public function isMethodSet( $methodName )
	{
		return craft()->invisibleCaptcha->isMethodSet($methodName);
	}

	public function getMethodOption( $option )
	{
		return craft()->invisibleCaptcha->getMethodOption($option);
	}

	public function hasMethodOption( $option )
	{
		return craft()->invisibleCaptcha->hasMethodOption($option);
	}
}
