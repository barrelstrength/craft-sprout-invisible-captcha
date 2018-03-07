<?php

namespace barrelstrength\sproutinvisiblecaptcha\integrations\sproutforms;

use barrelstrength\sproutforms\contracts\BaseCaptcha;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutinvisiblecaptcha\SproutInvisibleCaptcha;
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
        return SproutInvisibleCaptcha::$app->javascript->getProtection();
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
        // Only do this on the front-end
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return true;
        }

        $event->isValid = SproutInvisibleCaptcha::$app->javascript->verifySubmission();

        if (!$event->isValid) {
            $event->fakeIt = true;

            if (Craft::$app->getRequest()->getBodyParam('redirectOnFailure') != "") {
                $_POST['redirect'] = Craft::$app->getRequest()->getBodyParam('redirectOnFailure');
            }

            return false;
        }

        return true;
    }
}