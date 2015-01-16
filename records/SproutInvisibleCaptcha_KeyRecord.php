<?php
namespace Craft;

class SproutInvisibleCaptcha_KeyRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'sproutinvisiblecaptcha_keys';
	}

	protected function defineAttributes()
	{
		return array(
			'key' => array(AttributeType::String),
			'ipAddress' => array(AttributeType::String)
		);
	}

	/**
	 * Create a new instance of the current class. This allows us to
	 * properly unit test our service layer.
	 *
	 * @return BaseRecord
	 */
	public function create()
	{
		$class = get_class($this);
		$record = new $class();

		return $record;
	}
}
