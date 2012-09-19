<?php

class Mailform_Platform_UpdateTo200
{
	protected $dirname = '';

	public function __construct($dirname)
	{
		$this->dirname = $dirname;
	}

	public function update()
	{
		$formTable = $this->_getTableName('form');
		$this->_query("ALTER TABLE `{$formTable}` ADD `status` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '1'  AFTER `options`");
		$this->_query("ALTER TABLE `{$formTable}` ADD `finish_message` mediumtext NOT NULL  AFTER `header_description`;");
		$this->_query("UPDATE {$formTable} SET status = 2");
		$this->_createEntryTables();
	}

	protected function _createEntryTables()
	{
		$formIds = $this->_getFormIds();

		foreach ( $formIds as $formId ) {
			$this->_createEntryTable($formId);
		}
	}

	protected function _createEntryTable($formId)
	{
		$handler = new Mailform_Model_EntryHandler($this->dirname);
		$handler->setTableId($formId);
		$handler->dropTableIfExists($formId);
		$handler->createTable($formId);
	}

	/**
	 * @return array
	 */
	protected function _getFormIds()
	{
		$db = $this->_getDatabase();

		$formTable = $this->_getTableName('form');
		$result = $db->query("SELECT id FROM {$formTable} ORDER BY id ASC");

		$formIds = array();

		while ( $row = $db->fetchArray($result) ) {

			if ( isset($row['id']) === false ) {
				continue;
			}

			$formIds[] = intval($row['id']);
		}

		return $formIds;
	}

	protected function _getTableName($table)
	{
		$db = $this->_getDatabase();
		return $db->prefix($this->dirname.'_'.$table);
	}
	
	protected function _query($query)
	{
		$db = $this->_getDatabase();
		return $db->query($query);
	}

	/**
	 * @return XoopsMySQLDatabase
	 */
	protected function _getDatabase()
	{
		$root = XCube_Root::getSingleton();
		return $root->mController->mDB;
	}
}
