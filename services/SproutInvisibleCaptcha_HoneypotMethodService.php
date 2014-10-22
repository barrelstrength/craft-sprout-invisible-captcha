<?php
namespace Craft;

class SproutInvisibleCaptcha_HoneypotMethodService extends BaseApplicationComponent implements SproutInvisibleCaptcha_MethodInterfaceService
{

	public function verifySubmission()
	{
		// @TODO - clean up the way we access settings
		$honeypotFieldName = craft()->sproutInvisibleCaptcha->getMethodOption('honeypotFieldName');
		$honeypotUseDatabase = craft()->sproutInvisibleCaptcha->getMethodOption('honeypotUseDatabase');
		$honeypotRequireJavascript = craft()->sproutInvisibleCaptcha->getMethodOption('honeypotRequireJavascript');

		if ($honeypotRequireJavascript)
		{
			$length = strlen($honeypotFieldName);

			foreach (craft()->request->getPost() as $key => $value) 
			{	
				if (substr($key, 0, $length) === $honeypotFieldName) 
				{	
					$honeypotFieldName = $key;
				}
			}
		}
		
		// The honeypot field must be left blank or, if we are using the database, 
		// the field name much match the database field name
		if ( $honeypotValue = craft()->request->getPost($honeypotFieldName) ) 
		{
			// If our honeypot uses the database, make sure our unique field
			// if ($honeypotRequireJavascript)
			// {
			// 	// query the db and see if the value matches, return true if so
			// 	$key = craft()->db->createCommand()
			// 			->select('keys.key')
			// 			->from('sproutinvisiblecaptcha_keys as keys')
			// 			->where('keys.key=:key', array(':key'=> $honeypotValue))
			// 			->queryScalar();

			// 	if ($key) return true;
				
			// 	// if the value doesn't match, return false
			// 	craft()->sproutInvisibleCaptcha->honeypotMethodFailed = 1;
			// 	return false;
			// }

			craft()->sproutInvisibleCaptcha->honeypotMethodFailed = 1;
			return false;
			
		}

		return true;
	}

	public function getProtection()
	{
		return $this->getField();
	}

	public function getField()
	{
		// @TODO - clean up the way we access settings
		$honeypotFieldName = craft()->sproutInvisibleCaptcha->getMethodOption('honeypotFieldName');
		$honeypotScreenReaderMessage = craft()->sproutInvisibleCaptcha->getMethodOption('honeypotScreenReaderMessage');
		$honeypotRequireJavascript = craft()->sproutInvisibleCaptcha->getMethodOption('honeypotRequireJavascript');
		$formKeyDuration = craft()->sproutInvisibleCaptcha->getMethodOption('formKeyDuration');
		
		// $honeypotFieldName .= "_" . $this->randomString();
		$dummyValue = $this->randomString();
		$honeypotKey = "";

		// If our honeypot uses the database, make our honeypot field unique
		// if ( $honeypotRequireJavascript )
		// {
		// 	// Log our rejected submission so we can see what's being blocked
		// 	$model = new SproutInvisibleCaptcha_KeyModel();

		// 	$attributes['key'] 	= $this->randomString();
		// 	$attributes['ipAddress'] 	= $_SERVER["REMOTE_ADDR"];

		// 	$model->setAttributes($attributes);

		// 	$logRecord = SproutInvisibleCaptcha_KeyRecord::model();
		// 	$record = $logRecord->create();

		// 	$record->setAttributes($model->getAttributes(), false);

		// 	if ($record->save())
		// 	{
		// 		$honeypotKey = $record->key;
		// 	}
			
		// 	// And while we're at it, let's clean up any
		// 	// old keys that are not needed anymore based on the 
		// 	// amount of time we have set in our settings
		// 	$dateToDeleteFrom = date( "Y-m-d H:i:s", strtotime("now") - $formKeyDuration );

		// 	craft()->db->createCommand()->delete('sproutinvisiblecaptcha_keys', 'dateCreated<=:dateToDeleteFrom', array( ':dateToDeleteFrom' => $dateToDeleteFrom ));
		// }
	
			$honeypot = '
<div id="'.$honeypotFieldName.'_wrapper">
<label>'.$honeypotScreenReaderMessage.'</label>
<input type="text" id="'.$honeypotFieldName.'" name="'.$honeypotFieldName.'" value="" />
</div>
<style>#'.$honeypotFieldName.'_wrapper{display:none;}</style>';

// @TODO - Enable Javascript Test
// $honeypot = '
// <div id="'.$honeypotFieldName.'_wrapper">
// <label>'.$honeypotScreenReaderMessage.'</label>
// <input type="text" id="'.$honeypotFieldName.'" name="'.$honeypotFieldName.'" value="'.$dummyValue.'" />
// </div>
// <style>#'.$honeypotFieldName.'_wrapper{display:none;}</style>
// <script>document.getElementById("'.$honeypotFieldName.'").value="'.$honeypotKey.'"</script>';

		return $honeypot;
	}

	/**
	 * Generate a random string
	 * 
	 * http://salman-w.blogspot.com/2009/06/generate-random-strings-using-php.html
	 * @return string random
	 */
	function randomString()
	{
			$characterSetArray = array();

			$characterSetArray[] = array(
				'count' => 10, 
				'characters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
			);
			$characterSetArray[] = array(
				'count' => 5, 
				'characters' => '0123456789'
			);

			$tempArray = array();
			foreach ($characterSetArray as $characterSet) 
			{
					for ($i = 0; $i < $characterSet['count']; $i++) 
					{
							$tempArray[] = $characterSet['characters'][rand(0, strlen($characterSet['characters']) - 1)];
					}
			}
			shuffle($tempArray);
			return implode('', $tempArray);
	}
}
