<?php

class Mailform_Platform_XoopsCubeLegacy_Installer extends Pengin_Platform_XoopsCubeLegacy_Installer
{
	protected function _update()
	{
		parent::_update();
		$this->_applyUpdateSQL();
	}

	protected function _uninstall()
	{
		parent::_uninstall();
		$this->_dropEntryTables();
	}

	protected function _applyUpdateSQL()
	{
		if ( $this->currentVersion < 2.00 ) {
			$updator = new Mailform_Platform_UpdateTo200($this->dirname);
			$updator->update();
		}
	}

	/**
	 * エントリーテーブルを削除する
	 */
	protected function _dropEntryTables()
	{
		$db = $this->_getDatabase();
		$entryTables = $this->_getEntryTables();

		foreach ( $entryTables as $entryTable ) {
			$query = sprintf('DROP TABLE %s', $entryTable);

			if ( $db->query($query) ) {
				$this->_addMessage('Table <b>' . $entryTable . '</b> dropped.');
			} else {
				$this->_addError('ERROR: Could not drop table <b>' . $entryTable . '<b>.');
			}
		}
	}

	/**
	 * @return XoopsMySQLDatabase
	 */
	protected function _getDatabase()
	{
		$root = XCube_Root::getSingleton();
		return $root->mController->mDB;
	}

	/**
	 * エントリーテーブルを返す
	 * @return array
	 */
	protected function _getEntryTables()
	{
		$db = $this->_getDatabase();
		$entryTable = sprintf('%s_entry', $this->dirname);
		$entryTable = $db->prefix($entryTable);
		$query = "SHOW TABLES LIKE '{$entryTable}_%'";
		$result = $db->query($query);

		$entryTables = array();

		while ( $row = $db->fetchRow($result) ) {
			$entryTables[] = $row[0];
		}

		return $entryTables;
	}
}
