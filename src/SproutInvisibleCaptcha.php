<?php

namespace barrelstrength\sproutinvisiblecaptcha;


use barrelstrength\sproutforms\services\Forms;
use barrelstrength\sproutinvisiblecaptcha\integrations\sproutforms\InvisibleCaptcha;
use craft\base\Plugin;
use Craft;
use yii\base\Event;

/**
 * Class SproutInvisibleCaptcha
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

        self::$app = $this;

//        $this->setComponents([
//            'app' => App::class
//        ]);

//        Event::on(Entries::class, EntryElement::EVENT_BEFORE_SAVE, function(OnBeforeSaveEntryEvent $event) {
//            $response = SproutInvisibleCaptcha::$app->recaptcha->verifySubmission();
//            if (!$response->success){
//                $event->entry->addError('googleRecaptcha', 'ups!');
//            }
//        });

        Event::on(Forms::class, Forms::EVENT_REGISTER_CAPTCHAS, function(Event $event) {
            $event->types[] = InvisibleCaptcha::class;
        });

        // Support for displayForm() GoogleRecaptcha output via Hook (if enabled)
        Craft::$app->view->hook('sproutForms.modifyForm', function(&$context) {

//            $captcha = new InvisibleCaptcha();
//
//            return $captcha->getCaptchaHtml();


//            $sproutFormsSettings = Craft::$app->getPlugins()->getPlugin('sprout-forms')->getSettings();
//
//            if ($sproutFormsSettings->enableCaptchas && $sproutFormsSettings->enableGoogleRecaptcha){
//
//                $googleRecaptchaFile = SproutInvisibleCaptcha::$app->recaptcha->getScript();
//                Craft::$app->view->registerJsFile($googleRecaptchaFile);
//
//                return SproutInvisibleCaptcha::$app->recaptcha->getHtml();
//            }
//
//            return '';
        });
    }

}
