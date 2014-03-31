<?php
namespace Craft;

class SproutInvisibleCaptcha_KeyModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'         => array(AttributeType::Number),
			'key'        => array(AttributeType::String),
			'ipAddress'  => array(AttributeType::String)
		);
	}
}
