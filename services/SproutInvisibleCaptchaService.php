<?php
namespace Craft;

class SproutInvisibleCaptchaService extends BaseApplicationComponent
{
	const METHOD_FULL				= 1;
	const METHOD_TIME				= 2;
	const METHOD_ORIGIN 		= 3;
	const METHOD_HONEYPOT		= 4;

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
	public $originMethodFailed    = 0;
	public $honeypotMethodFailed  = 0;
	public $timeMethodFailed      = 0;

	protected $settings;

	public function __construct()
	{
		// Make it easier to access our settings
		$this->settings = craft()->plugins->getPlugin('sproutInvisibleCaptcha')->getSettings();		
	}

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
			if ( is_string($method) ) 
			{
				if ( stripos($method, '|') !== false ) 
				{
					$method = array_map('trim', explode('|', $method) );
				}
				else 
				{
					$method = strtoupper($method);

					if ( empty($method) || ! in_array($method, $this->getMethodMap() ) ) 
					{
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

		// If a method is not set, assume we are not using Invisible captcha
		// Craft Contact Form and Guest Entries run the on event each time
		// @TODO - review this workflow
		if ( ! $req->getPost('__METHOD') ) return $this->approveSubmission();


		// 3. Figure out what validation method we need to run
		$method = $req->getPost('__METHOD'); 			// Pipe delimited list: 1|2|3|4
		$method = $this->getValidationMethods($method); // Array of methods: array('full') | array('time', 'origin', 'honeypot')

		// 4. No __METHOD no validation
		if ($method) 
		{
			if ( $this->spammySubmission($method) ) 
			{				
				return $this->rejectSubmission();
			}
		}
		
		return $this->approveSubmission();
	}

	public function methodOptionFields( $config=array() )
	{
		$output = '';
		$config = array_merge( $this->getSavedOptions(), is_array($config) ? $config : array() );

		if ( is_array($config) && count($config) ) 
		{
			unset( $config['method'] );
			foreach ( $config as $option => $value ) 
			{
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
	// @=VALIDATION
	//-------------------------------------------------------------------------------

	public function verifyFullSubmission()
	{	
		return
			craft()->sproutInvisibleCaptcha_timeMethod->verifySubmission() &&
			craft()->sproutInvisibleCaptcha_originMethod->verifySubmission() &&
			craft()->sproutInvisibleCaptcha_honeypotMethod->verifySubmission()
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

	/**
	 * Reject the submission
	 * 
	 * @return [type] [description]
	 */
	protected function rejectSubmission()
	{	
		// Log failed submissions if enabled
		if ( $this->settings->logFailedSubmissions )
		{
			// Log our rejected submission so we can see what's being blocked
			$model = new SproutInvisibleCaptcha_LogModel();

			$attributes['postData'] 	= json_encode($_POST);
			$attributes['ipAddress'] 	= $_SERVER["REMOTE_ADDR"];
			$attributes['originMethodFailed'] 		= $this->originMethodFailed;
			$attributes['honeypotMethodFailed'] 	= $this->honeypotMethodFailed;
			$attributes['timeMethodFailed'] 			= $this->timeMethodFailed;

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

			// craft()->request->redirect($url);
		}

		// Make sure we don't let anything through
		return false;
	}

	protected function approveSubmission()
	{
		$redirectUrl= craft()->request->getPost('onSuccessRedirect');

		if ( !empty($redirectUrl) ) {
			craft()->request->redirect( $redirectUrl );
		}

		return true;
	}

	//--------------------------------------------------------------------------------

	public function getFullProtection()
	{
		return
			craft()->sproutInvisibleCaptcha_timeMethod->getField() .
			craft()->sproutInvisibleCaptcha_originMethod->getField() .
			craft()->sproutInvisibleCaptcha_honeypotMethod->getField();
	}

	//-------------------------------------------------------------------------------
	// @=FIELD GENERATORS
	//--------------------------------------------------------------------------------

	public function getMethodField( $methodName=self::METHOD_FULL_STRING )
	{
		$methods = array();

		if ( is_array($methodName) ) 
		{
			foreach ($methodName as $name) {
				if ( in_array( strtoupper($name), $this->getMethodMap() ) ) {
					$methods[] = $this->getMethodId($name);
				}
			}

			$method = implode('|', $methods);
		} 
		else 
		{
			$method = $this->getMethodId($methodName);

			return sprintf('<input type="hidden" id="__METHOD" name="__METHOD" value="%s" />', $method );
		}

		// Keep output on __METHOD relevant
		if ( in_array(self::METHOD_FULL, $methods) ) 
		{
			$method = self::METHOD_FULL;
		}

		return sprintf('<input type="hidden" id="__METHOD" name="__METHOD" value="%s" />', $method );
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
		return $this->settings->captchaMethod;
	}

	public function getSavedOptions()
	{
		return $this->settings->methodOptions;
	}

	//--------------------------------------------------------------------------------

	public function isMethodSet( $methodName )
	{
		$methodString	= empty($this->settings->captchaMethod) ? '' : $this->settings->captchaMethod;
		$methodArray	= explode('|', $methodString);

		return (in_array($methodName, $methodArray) || $methodString == 'full');
	}

	//--------------------------------------------------------------------------------

	public function getMethodOption( $option )
	{
		if ( empty($option) || ! is_string($option) ) { return false; }

		if ( array_key_exists($option, $this->settings->methodOptions) ) {
			return $this->settings->methodOptions[$option];
		} else {
			return false;
		}
	}

	//--------------------------------------------------------------------------------

	public function hasMethodOption( $option )
	{
		if ( empty($option) || ! is_string($option) ) { return false; }

		return array_key_exists($option, $this->settings->methodOptions);
	}

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
	
}
