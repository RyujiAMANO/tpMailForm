<?php
class Mailform_Model_Field extends Mailform_Abstract_Model
{
	const REQUIRED_YES = 1;
	const REQUIRED_NO  = 0;

	protected $selectable = null;
	protected $selections = null;

	public function __construct()
	{
		$this->val('id', self::INTEGER, null, 11);
		$this->val('form_id', self::INTEGER, null, 11);
		$this->val('name', self::STRING, null, 255);
		$this->val('label', self::STRING, null, 255);
		$this->val('type', self::STRING, null, 100);
		$this->val('required', self::INTEGER, null, 1);
		$this->val('weight', self::INTEGER, null, 3);
		$this->val('description', self::TEXT, null);
		$this->val('options', self::JSON_ARRAY, null);
		$this->val('created', self::DATETIME, null);
		$this->val('creator_id', self::INTEGER, null, 8);
		$this->val('modified', self::DATETIME, null);
		$this->val('modifier_id', self::INTEGER, null, 8);
	}

	/**
	 * プラグインのクラスを返す
	 * @return bool|string
	 */
	public function getPluginClass()
	{
		$type = $this->get('type');
		$manager = $this->_getPluginManager();
		$pluginClass = $manager->getPluginClass($type);
		return $pluginClass;
	}
	
	/**
	 * メールアドレス型かを返す
	 * @return bool
	 */
	public function isEmailType()
	{
		if ( $this->get('type') === 'Email' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 名前型かを返す
	 * @return bool
	 */
	public function isNameType()
	{
		if ( $this->get('type') === 'Name' ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 選択型のフィールドかを返す
	 * @return bool
	 */
	public function isSelectable()
	{
		if ( $this->selectable === null ) {
			$this->selectable = $this->_isSelectable();
		}

		return $this->selectable;
	}

	/**
	 * 選択肢を返す
	 * @return array 選択型でないフィールドは選択肢がないので FALSE が返る
	 */
	public function getSelections()
	{
		if ( $this->selections === null ) {
			$this->selections = $this->_getSelections();
		}

		return $this->selections;
	}

	/**
	 * CSV出力ヘッダを返す
	 * @return array
	 */
	public function getCsvHeader()
	{
		$headers = array();
		$name = $this->get('name');
		$label = $this->get('label');

		if ( $this->isSelectable() === true ) {
			$selections = $this->getSelections();
			// 選択肢ごとに1列
			foreach ( $selections as $index => $selection ) {
				$headers[$name.'.'.$index] = $label.':'.$selection;
			}
		} else {
			$headers[$name] = $label;
		}

		return $headers;
	}

	/**
	 * CSV出力値を返す
	 * @param mixed $value
	 * @return array
	 */
	public function getCsvValues($value)
	{
		$values = array();
		$name = $this->get('name');
		$selections = $this->_getCsvValues($value);

		if ( $this->isSelectable() === true ) {
			// 選択肢ごとに1列
			foreach ( $selections as $index => $value ) {
				$values[$name.'.'.$index] = $value;
			}
		} else {
			$values[$name] = reset($selections);
		}

		return $values;
	}
	
	/**
	 * DBカラム定義を返す
	 * @return bool|string
	 */
	public function getColumnDefinition()
	{
		$type = $this->get('type');
		$name = $this->get('name');
		$manager = $this->_getPluginManager();
		$pluginClass = $manager->getPluginClass($type);

		if ( $pluginClass === false ) {
			return false;
		}

		$columnDefinition = call_user_func(array($pluginClass, 'getColumnDefinition'));
		$columnDefinition = sprintf('`%s` %s', $name, $columnDefinition);
		return $columnDefinition;
	}

	/**
	 * 入力値を文字列にして返す
	 * @param Mailform_Model_Entry $entry
	 * @return string
	 */
	public function valueToString(Mailform_Model_Entry $entry)
	{
		$name        = $this->get('name');
		$options     = $this->get('options');
		$pluginClass = $this->getPluginClass();
		$value = $entry->get($name);
		$value = call_user_func(array($pluginClass, 'valueToString'), $value, $options);
		return $value;
	}

	/**
	 * 選択肢を返す
	 * @return array 選択型でないフィールドは選択肢がないので FALSE が返る
	 */
	protected function _getSelections()
	{
		$pluginClass = $this->getPluginClass();

		if ( $this->isSelectable() === false ) {
			return false;
		}

		$options = $this->get('options');
		$options = $options['options'];
		$options = call_user_func(array($pluginClass, 'optionTextToArray'), $options);
		return $options;
	}

	/**
	 * @param $value
	 * @return array
	 */
	protected function _getCsvValues($value)
	{
		$pluginClass = $this->getPluginClass();
		$options = $this->get('options');
		$values = call_user_func(array($pluginClass, 'valueToArray'), $value, $options);
		return $values;
	}

	/**
	 * 選択型のフィールドかを返す
	 * @return bool
	 */
	protected function _isSelectable()
	{
		// TODO >> プラグインに委譲する
		$type = $this->get('type');
		$selectableTypes = array('Checkbox', 'Radio', 'Select');
		return in_array($type, $selectableTypes);
	}

	/**
	 * プラグインマネージャを返す
	 * @return Mailform_Plugin_Manager
	 */
	protected function _getPluginManager()
	{
		return Mailform_Plugin_Manager::getInstance();
	}
}
