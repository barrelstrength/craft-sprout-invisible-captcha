<?php

namespace barrelstrength\sproutinvisiblecaptcha;


use barrelstrength\sproutforms\services\Forms;
use barrelstrength\sproutinvisiblecaptcha\integrations\sproutforms\InvisibleCaptcha;
use barrelstrength\sproutinvisiblecaptcha\services\App;
use craft\base\Plugin;
use Craft;
use yii\base\Event;

/**
 * Class SproutInvisibleCaptcha
 *
*/
class SproutInvisibleCaptcha extends Plugin
{
   /**
     * @var SproutInvisibleCaptcha
     */
    public static $app;

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

        $this->setComponents([
            'app' => App::class
        ]);

        self::$app = $this->get('app');

        Event::on(Forms::class, Forms::EVENT_REGISTER_CAPTCHAS, function(Event $event) {
            $event->types[] = InvisibleCaptcha::class;
        });
    }

}
