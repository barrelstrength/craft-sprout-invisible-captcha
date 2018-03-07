<?php
/**
 * Sprout Invisible Captcha plugin for Craft CMS 3.x
 *
 * Google Recaptcha solution for Sprout Forms
 *
 * @link      https://www.barrelstrengthdesign.com/
 * @copyright Copyright (c) 2018 Barrel Strength
 */

namespace barrelstrength\sproutinvisiblecaptcha\services;

use barrelstrength\sproutinvisiblecaptcha\SproutInvisibleCaptcha;
use Craft;
use craft\base\Component;

/**
 * @author    Barrel Strength
 * @package   SproutInvisibleCaptcha
 * @since     1.0.0
 */
class Javascript extends Component implements MethodInterface
{
    /**
     * @return bool
     */
    public function verifySubmission()
    {
        $jsset = null;

        foreach ($_POST as $key => $value) {
            // Fix issue on multiple forms on same page
            if (strpos($key, '__JSCHK') === 0) {
                $jsset = $_POST[$key];
                break;
            }
        }

        if (strlen($jsset) > 0) {
            // If there is a valid unique token set, unset it and return true.
            // This token was created and set by javascript.
            Craft::$app->getSession()->remove('invisibleCaptchaJavascriptId');
            return true;
        } else {
            Craft::error("A form submission failed because the user did not have Javascript enabled.",  __METHOD__);

            // If there is no token, set to fail; javascript is not present
            SproutInvisibleCaptcha::$app->javascriptMethodFailed = 1;
            return false;
        }
    }

    /**
     * @return string
     */
    public function getProtection()
    {
        // Create the unique token
        $uniqueId = uniqid();

        // Create session variable to test for javascript
        Craft::$app->getSession()->set('invisibleCaptchaJavascriptId', $uniqueId);

        return $this->getField();
    }

    /**
     * @return string
     */
    public function getField()
    {
        $jsCheck = Craft::$app->getSession()->get('invisibleCaptchaJavascriptId');

        // Set a hidden field with no value and use javascript to set it.
        $output = '';
        $output .= sprintf('<input type="hidden" id="__JSCHK_%s" name="__JSCHK_%s" />', $jsCheck, $jsCheck);
        $output .= sprintf('<script type="text/javascript">document.getElementById("__JSCHK_%s").value = "%s";</script>', $jsCheck, $jsCheck);

        return $output;
    }
}
