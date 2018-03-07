<?php

namespace barrelstrength\sproutinvisiblecaptcha\services;

use barrelstrength\sproutinvisiblecaptcha\services\Javascript;
use craft\base\Component;

class App extends Component
{
    // Used to record failed submissions when logging is enabled
    protected $originMethodFailed = 0;
    protected $honeypotMethodFailed = 0;
    protected $timeMethodFailed = 0;
    protected $duplicateMethodFailed = 0;
    protected $javascriptMethodFailed = 0;

    /**
     * @var Javascript
     */
    public $javascript;

    public function init()
    {
        $this->javascript = new Javascript();
    }
}