<?php
namespace Craft;

class SproutInvisibleCaptchaService extends BaseApplicationComponent
{
	const METHOD_FULL				= 1;
	const METHOD_TIME				= 2;
	const METHOD_ORIGIN 		= 3;
	const METHOD_HONEYPOT		= 4;

	const MIN_ELAPSED_TIME = 5; // Fallback in seconds

	const METHOD_FULL_STRING      = 'FULL';
	const METHOD_TIME_STRING      = 'TIME';
	const METHOD_ORIGIN_STRING    = 'ORIGIN';
	const METHOD_HONEYPOT_STRING  = 'HONEYPOT';

	protected $methodMap = array(
		self::METHOD_FULL     => self::METHOD_FULL_STRING,
		self::METHOD_TIME     => self::METHOD_TIME_STRING,
		self::METHOD_ORIGIN   => self::METHOD_ORIGIN_STRING,
		self::METHOD_HONEYPOT => self::METHOD_HONEYPOT_STRING
	);

	// Used to record failed submissions when logging is enabled
	private $_originMethodFailed    = 0;
	private $_honeypotMethodFailed  = 0;
	private $_timeMethodFailed      = 0;


	/**
	 * getProtection()
	 * This method will generate the fields for the spam guard during a GET request
	 *
	 * @see 	The $methodMap above for the valid values to use as a $method argument
	 *
	 * @param  string $method The type of protection to implement (may use pipe delimited)
	 * @return string The hidden fields we need for GET requests
	 */
	public function getProtection( $config=array() )
	{
		if ( is_array($config) && array_key_exists('method', $config) && !empty($config['method']) ) {
			$method = $config['method'];
		} else {
			$method = $this->getSavedMethod();
		}

		$output   = '';
		$method   = strtoupper( trim($method) );
		$methods  = array();

		// Optimize for full protection
		if ( is_string($method) && $method == self::METHOD_FULL_STRING ) {
			$output .= $this->getFullProtection();
			$output .= $this->getMethodField( $method );
		} else {
			// Handle pipe delimited methods
			if ( is_string($method) ) {
				if ( stripos($method, '|') !== false ) {
					$method = array_map('trim', explode('|', $method) );
				} else {
					$method = strtoupper($method);

					if ( empty($method) || ! in_array($method, $this->getMethodMap() ) ) {
						$method = self::METHOD_FULL_STRING;
					}
				}
			}

			// Optimize for single method
			if ( is_string($method) && strlen($method) ) {
				$m = $this->getMethodTranslation($method);

				if ( $m && method_exists($this, $m ) ) {
					$output .= $this->$m();
					$output .= $this->getMethodField( $method );
				}
			} else {
				// if it was already an array or converted to one
				if ( in_array(self::METHOD_FULL_STRING, $method) ) {
					$output .= $this->getFullProtection();
					$methods = self::METHOD_FULL_STRING;
				} else {
					foreach ($method as $m) {
						array_push($methods, $m);

						$m = $this->getMethodTranslation($m);

						if ( $m && method_exists($this, $m) ) {
							$output .= $this->$m();

						}
					}
				}

				// Create the input field with pipe delimited method
				$output .= $this->getMethodField( $methods );
			}
		}

		$output .= $this->methodOptionFields( $config );

		return $this->safeOutput( $output );
	}

	/**
	 * verifySubmission()
	 *
	 * This method will run the validation method assigned to __METHOD
	 * The goal is to use checks to determine whether the submission is spammy or not
	 *
	 * @return void
	 */
	public function verifySubmission()
	{
		// 1. Get the request instance (aliasing)
		$req = craft()->request;

		// 2. Ignore if not a POST request
		if ( ! $req->getPost() ) { return $this->rejectSubmission(); }

		// 3. Figure out what validation method we need to run
		$method = $req->getPost('__METHOD'); 			// Pipe delimited list: 1|2|3|4
		$method = $this->getValidationMethods($method); // Array of methods: array('full') | array('time', 'origin', 'honeypot')

		// 4. No __METHOD no validation
		if ($method) {
			if ( $this->spammySubmission($method) ) {				
				return $this->rejectSubmission();
			}
		}
		
		return $this->approveSubmission();
	}

	public function methodOptionFields( $config=array() )
	{
		$output = '';
		$config = array_merge( $this->getSavedOptions(), is_array($config) ? $config : array() );

		if ( is_array($config) && count($config) ) {
			unset( $config['method'] );
			foreach ( $config as $option => $value ) {
				$output .= $this->createField($option, $value);
			}
		}
		return $output;
	}

	protected function createField( $name, $value, $type='hidden' )
	{
		return sprintf( '<input type="%s" id="%s" name="%s" value="%s" />', $type, $name, $name, $value );
	}

	//-------------------------------------------------------------------------------
	// @=CONSUMER METHOD (DYNAMIC METHOD CALL)
	//--------------------------------------------------------------------------------

	protected function spammySubmission( $methods=array() )
	{
		if ( is_array($methods) && count($methods) ) {
			foreach ($methods as $method) {
				$method = $this->getMethodTranslation($method, 'verify', 'Submission');

				if ( method_exists($this, $method) ) {
					if ( ! $this->$method() ) {
						return true;
					}
				}

				return false;
			}
		}

		return true;
	}

	//-------------------------------------------------------------------------------
	// @=VALIDATION METHODS
	//-------------------------------------------------------------------------------

	// Compare elapsed time between GET and POST requests
	public function verifyTimeSubmission()
	{
		$time   = time();
		$posted = (int) craft()->request->getPost('__UATIME', time() );

		// Time operations must be done after values have been properly assigned and casted
		$diff   = ($time - $posted);
		$min 	= (int) $this->getMinElapsedTime();

		// Flag it as a spammy submission based on time
		// @TODO: May convert the minElapsedTime into a global setting
		$verified = (bool) ($diff > $min );
		if ($verified)
		{
			return true;
		}
		else
		{
			$this->_timeMethodFailed = 1;
			return false;
		}
	}

	//-------------------------------------------------------------------------------

	public function verifyOriginSubmission()
	{
		$uahash = craft()->request->getPost('__UAHASH');
		$uahome = craft()->request->getPost('__UAHOME');

		// Run a user agent check
		if ( ! $uahash || $uahash != $this->getUaHash() ) {
			return false;
		}

		// Run originating domain check
		if ( ! $uahome || $uahome != $this->getDomainHash() ) {
			$this->_originMethodFailed = 1;
			return false;
		}

		// Passed
		return true;

	}

	//-------------------------------------------------------------------------------

	public function verifyHoneypotSubmission()
	{
		// The honeypot field must be left blank
		if ( craft()->request->getPost('chp') ) {
			$this->_honeypotMethodFailed = 1;
			return false;
		}

		return true;
	}

	//-------------------------------------------------------------------------------

	public function verifyFullSubmission()
	{	
		return
			$this->verifyTimeSubmission() &&
			$this->verifyOriginSubmission() &&
			$this->verifyHoneypotSubmission()
			? true : false;
	}

	//-------------------------------------------------------------------------------
	// @=HELPER METHODS (POST)
	//--------------------------------------------------------------------------------

	public function getValidationMethods($methodString=self::METHOD_FULL)
	{
		$methods 	= array();
		$methodMap 	= $this->getMethodMap();

		if ( is_string($methodString) ) {
			if ( stripos($methodString, '|') !== false ) {
				$methodsUsed = array_map('trim', explode('|', $methodString)); // 1|2|3|4
			} else {
				$methodsUsed = array($methodString);
			}
		} else {
			$methodsUsed = $methodString;
		}

		foreach ($methodsUsed as $methodKey) {
			if ( $methodKey <= 0 || $methodKey > count($methodMap) ) {
				throw new \Exception('Please ensure you are using validation methods properly @ '.__METHOD__);
			}

			if ($methodKey == self::METHOD_FULL) {
				return array(self::METHOD_FULL_STRING);
			}

			if ( array_key_exists($methodKey, $methodMap) ) {
				$methods[] = $methodMap[$methodKey];
			}

		}

		// Be cautious
		if ( count($methods) <= 0) {
			return array(self::METHOD_FULL);
		}

		return $methods;
	}

	//-------------------------------------------------------------------------------

	protected function rejectSubmission()
	{	

		$settings = craft()->plugins->getPlugin('sproutinvisiblecaptcha')->getSettings();
		
		// Log failed submissions if enabled
		if ( $settings->logFailedSubmissions )
		{
			// Log our rejected submission so we can see what's being blocked
			$model = new SproutInvisibleCaptcha_LogModel();

			$attributes['postData'] 	= json_encode($_POST);
			$attributes['ipAddress'] 	= $_SERVER["REMOTE_ADDR"];
			$attributes['originMethodFailed'] 		= $this->_originMethodFailed;
			$attributes['honeypotMethodFailed'] 	= $this->_honeypotMethodFailed;
			$attributes['timeMethodFailed'] 			= $this->_timeMethodFailed;

			$model->setAttributes($attributes);

			$logRecord = SproutInvisibleCaptcha_LogRecord::model();
			$record = $logRecord->create();

			$record->setAttributes($model->getAttributes(), false);

			// Let's assume this works.  If not, carry on.
			// No need to disrupt the user experience
			$record->save();
		}
		
		//------------------------------------------------------------

		// See if we should redirect to a different URL on failure
		// otherwise, fallback to Craft redirect
		if ( $url = craft()->request->getPost('redirectOnFailure') ) 
		{
			craft()->request->redirect($url);
		}
		else
		{
			// NOTE: this code was taken from the redirectToPostedUrl() function 
			// in the BaseController since we can't access it in the service layer.
			$url = craft()->request->getPost('redirect');

			if ($url === null)
			{
				$url = craft()->request->getPath();
			}

			craft()->request->redirect($url);
		}

		// Make sure we don't let anything through
		exit;
	}

	protected function approveSubmission()
	{
		$redirectUrl= craft()->request->getPost('onSuccessRedirect');

		if ( !empty($redirectUrl) ) {
			craft()->request->redirect( $redirectUrl );
		}

		return true;
	}

	//-------------------------------------------------------------------------------
	// @=GENERATOR INTERFACES
	//--------------------------------------------------------------------------------

	public function getTimeProtection()
	{
		return $this->getTimeCheckField();
	}

	//--------------------------------------------------------------------------------

	public function getOriginProtection()
	{
		return $this->getOriginCheckField();
	}

	//--------------------------------------------------------------------------------

	public function getHoneypotProtection()
	{
		return $this->getHoneypotCheckField();
	}

	//--------------------------------------------------------------------------------

	public function getFullProtection()
	{
		return
			$this->getTimeCheckField().
			$this->getOriginCheckField().
			$this->getHoneypotCheckField();
	}

	//-------------------------------------------------------------------------------
	// @=FIELD GENERATORS
	//--------------------------------------------------------------------------------

	public function getMethodField( $methodName=self::METHOD_FULL_STRING )
	{
		$methods = array();

		if ( is_array($methodName) ) {
			foreach ($methodName as $name) {
				if ( in_array( strtoupper($name), $this->getMethodMap() ) ) {
					$methods[] = $this->getMethodId($name);
				}
			}

			$method = implode('|', $methods);
		} else {
			$method = $this->getMethodId($methodName);

			return sprintf('<input type="hidden" id="__METHOD" name="__METHOD" value="%s" />', $method );
		}

		// Keep output on __METHOD relevant
		if ( in_array(self::METHOD_FULL, $methods) ) {
			$method = self::METHOD_FULL;
		}

		return sprintf('<input type="hidden" id="__METHOD" name="__METHOD" value="%s" />', $method );
	}

	//-------------------------------------------------------------------------------

	protected function getTimeCheckField()
	{
		return sprintf('<input type="hidden" id="__UATIME" name="__UATIME" value="%s" />', time() );
	}

	//-------------------------------------------------------------------------------

	protected function getOriginCheckField()
	{
		$output = '';
		$domain = craft()->request->getHostInfo();

		$output .= sprintf('<input type="hidden" id="__UAHOME" name="__UAHOME" value="%s" />', $this->getDomainHash() );
		$output .= sprintf('<input type="hidden" id="__UAHASH" name="__UAHASH" value="%s"/>', $this->getUaHash() );

		return $output;
	}

	//-------------------------------------------------------------------------------

	protected function getHoneypotCheckField()
	{
		$honeypot = '<div class="chp">'.
					'<label for="chp">Leave this field blank</label>'.
					'<input type="text" id="chp" name="chp" />'.
					'</div><style>.chp{ display: none; }</style>';

		return $honeypot;
	}

	//-------------------------------------------------------------------------------
	// @=HELPER METHODS
	//--------------------------------------------------------------------------------

	public function getMethodMap()
	{
		return $this->methodMap;
	}

	//--------------------------------------------------------------------------------

	public function getMethodName( $methodId )
	{
		$methodId	= (int) $methodId;
		$methodMap	= $this->getMethodMap();

		foreach ($methodMap as $id => $name) {
			if ($methodId == $id) {
				return $name;
			}
		}

		return false;
	}

	//--------------------------------------------------------------------------------

	public function getMethodId( $methodName )
	{
		$methodMap 	= $this->getMethodMap();
		$methodName = strtoupper($methodName);

		foreach ($methodMap as $id => $name) {
			if ($methodName == $name) {
				return $id;
			}
		}

		return false;
	}

	//--------------------------------------------------------------------------------

	public function getMethodTranslation($method='', $prepend='get', $append='Protection')
	{
		if ( ! empty($method) ) {
			return $prepend.ucfirst( strtolower( trim($method) ) ).$append;
		}

		return false;
	}

	//--------------------------------------------------------------------------------

	public function getSavedMethod()
	{
		$settings = craft()->plugins->getPlugin('sproutinvisiblecaptcha')->getSettings();

		return $settings->captchaMethod;
	}

	public function getSavedOptions()
	{
		$settings = craft()->plugins->getPlugin('sproutinvisiblecaptcha')->getSettings();

		return $settings->methodOptions;
	}

	//--------------------------------------------------------------------------------

	public function isMethodSet( $methodName )
	{
		$settings		= craft()->plugins->getPlugin('sproutInvisibleCaptcha')->getSettings();
		$methodString	= empty($settings->captchaMethod) ? '' : $settings->captchaMethod;
		$methodArray	= explode('|', $methodString);

		return (in_array($methodName, $methodArray) || $methodString == 'full');
	}

	//--------------------------------------------------------------------------------

	public function getMethodOption( $option )
	{
		if ( empty($option) || ! is_string($option) ) { return false; }

		$settings = craft()->plugins->getPlugin('sproutInvisibleCaptcha')->getSettings();

		if ( array_key_exists($option, $settings->methodOptions) ) {
			return $settings->methodOptions[$option];
		} else {
			return false;
		}
	}

	//--------------------------------------------------------------------------------

	public function hasMethodOption( $option )
	{
		if ( empty($option) || ! is_string($option) ) { return false; }

		$settings = craft()->plugins->getPlugin('sproutInvisibleCaptcha')->getSettings();

		return array_key_exists($option, $settings->methodOptions);
	}

	//--------------------------------------------------------------------------------

	/*
	 * safeOutput()
	 *
	 * Marks html content as safe for output within templates
	 *
	 * @param string $content The content to mark as safe
	 * @param string $charset The (optional) charset to use
	 */
	public function safeOutput($content, $charset=null)
	{
		if ( is_null($charset) ) {
			$charset = craft()->templates->getTwig()->getCharset();
		}

		return new \Twig_Markup($content, (string) $charset);
	}

	//--------------------------------------------------------------------------------

	protected function getMinElapsedTime()
	{
		$plugin 	= craft()->plugins->getPlugin('sproutInvisibleCaptcha');
		$settings	= $plugin->getSettings();

		if ( ($elapsedTime = $this->getMethodOption('elapsedTime')) ) {
			return $elapsedTime;
		}

		return self::MIN_ELAPSED_TIME;
	}

	//--------------------------------------------------------------------------------

	protected function getDomainHash()
	{
		$domain = craft()->request->getHostInfo();

		return $this->getHash( $domain );
	}

	//--------------------------------------------------------------------------------

	/*
	 * getUaHash()
	 *
	 * Grab the user agent string and return a hashed version of it
	 *
	 * @return string The hashed value of the user agent string
	 */
	protected function getUaHash()
	{
		return $this->getHash( craft()->request->getUserAgent() );
	}

	//--------------------------------------------------------------------------------

	/**
	 * getHash()
	 *
	 * Simple string hashing to encode data (Do not use for encryption)
	 *
	 * @param  string $str The string to encode
	 * @return string The hashed value of $str (32 Chars)
	 */
	protected function getHash($str)
	{
		return md5( sha1($str) );
	}
}