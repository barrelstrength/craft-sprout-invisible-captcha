<?php

namespace barrelstrength\sproutinvisiblecaptcha\integrations\sproutforms;

use barrelstrength\sproutforms\contracts\BaseCaptcha;
use Craft;

/**
 * Class InvisibleCaptcha
 */
class InvisibleCaptcha extends BaseCaptcha
{
    public function getName()
    {
        return 'Invisible Captcha';
    }

    public function getCaptchaHtml()
    {
        return '<b>Invisible Captcha!</b>';
    }
}