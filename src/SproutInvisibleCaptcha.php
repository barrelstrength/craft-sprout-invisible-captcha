<?php

namespace barrelstrength\sproutinvisiblecaptcha;


use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutforms\services\Forms;
use barrelstrength\sproutinvisiblecaptcha\integrations\sproutforms\HoneypotCaptcha;
use barrelstrength\sproutinvisiblecaptcha\integrations\sproutforms\JavascriptCaptcha;
use craft\base\Plugin;
use yii\base\Event;

/**
 * Class SproutInvisibleCaptcha
 *
*/
class SproutInvisibleCaptcha extends Plugin
{
    use BaseSproutTrait;

    /**
     * Identify our plugin for BaseSproutTrait
     *
     * @var string
     */
    public static $pluginId = 'sprout-invisible-captcha';

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Event::on(Forms::class, Forms::EVENT_REGISTER_CAPTCHAS, function(Event $event) {
            $event->types[] = JavascriptCaptcha::class;
            $event->types[] = HoneypotCaptcha::class;
        });
    }

}
