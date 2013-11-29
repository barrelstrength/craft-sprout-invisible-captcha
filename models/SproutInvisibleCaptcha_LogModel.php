<?php
namespace Craft;

class SproutInvisibleCaptcha_LogModel extends BaseModel
{

    protected function defineAttributes()
    {
        return array(
        		'id'					=> array(AttributeType::Number),
            'postData'		=> array(AttributeType::Mixed),
            'ipAddress'		=> array(AttributeType::String)
        );
    }

}
