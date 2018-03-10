<?php

namespace barrelstrength\sproutinvisiblecaptcha\integrations\sproutforms;

use barrelstrength\sproutforms\contracts\BaseCaptcha;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutinvisiblecaptcha\SproutInvisibleCaptcha;
use Craft;

/**
 * Class HoneypotCaptcha
 */
class HoneypotCaptcha extends BaseCaptcha
{
    /**
     * @var string
     */
    public $honeypotFieldName;

    /**
     * @var string
     */
    public $honeypotScreenReaderMessage;

    public function getName()
    {
        return 'Honeypot Captcha';
    }

    public function getDescription()
    {
        return Craft::t('sprout-invisible-captcha','Block form submissions by robots who auto-fill all of your form fields ');
    }

    public function getCaptchaHtml()
    {
        return $this->getField();
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getCaptchaSettingsHtml()
    {
        $settings = $this->getSettings();

        $html = Craft::$app->getView()->renderTemplate('sprout-invisible-captcha/honeypot/_settings', [
            'settings' => $settings,
            'captchaId' => $this->getCaptchaId()
        ]);

        return $html;
    }

    /**
     * Verify Submission
     *
     * @param $event
     *
     * @return boolean
     */
    public function verifySubmission(OnBeforeSaveEntryEvent $event): bool
    {
        $honeypotFieldName = $this->getHoneypotFieldName();

        $honeypotValue = null;
        foreach ($_POST as $key => $value) {
            // Fix issue on multiple forms on same page
            if (strpos($key, $honeypotFieldName) === 0) {
                $honeypotValue = $_POST[$key];
                break;
            }
        }

        // The honeypot field must be left blank
        if ($honeypotValue) {
            SproutInvisibleCaptcha::error("A form submission failed the Honeypot test.");
            $event->isValid = false;
            $event->fakeIt = true;

            if (Craft::$app->getRequest()->getBodyParam('redirectOnFailure') != "") {
                $_POST['redirect'] = Craft::$app->getRequest()->getBodyParam('redirectOnFailure');
            }

            #craft()->sproutInvisibleCaptcha->honeypotMethodFailed = 1;
            return false;
        }

        return true;
    }

    public function getHoneypotFieldName()
    {
        $settings = $this->getSettings();

        return $settings['honeypotFieldName'] ?? 'beesknees';
    }

    public function getHoneypotScreenReaderMessage()
    {
        $settings = $this->getSettings();

        return $settings['honeypotScreenReaderMessage'] ?? Craft::t('sprout-invisible-captcha','Leave this field blank');
    }

    /**
     * @return string
     */
    private function getField()
    {
        $this->honeypotFieldName = $this->getHoneypotFieldName();
        $this->honeypotScreenReaderMessage = $this->getHoneypotScreenReaderMessage();

        // Create the unique token
        $uniqueId = $this->honeypotFieldName.'_'.uniqid();

        $honeypot = '
<div id="'.$uniqueId.'_wrapper" style="display:none;">
<label for="'.$uniqueId.'">'.$this->honeypotScreenReaderMessage.'</label>
<input type="text" id="'.$uniqueId.'" name="'.$uniqueId.'" value="" />
 </div>';

        return $honeypot;
    }
}



