<?php

class Mailform_Model_EntryHandler extends Pengin_Model_AbstractDynamicHandler
{
	/**
	 * テーブルIDをセットする
	 * @param int $formId
	 */
	public function setTableId($formId)
	{
		$this->table = $this->_getTableName($formId);
	}

	/**
	 * 作成者IDから投稿をすべて返す
	 * @param int $creatorId
	 * @return Mailform_Model_Entry[]
	 */
	public function findByCreatorId($creatorId)
	{
		$criteria = new Pengin_Criteria();
		$criteria->add('creator_id', $creatorId);
		return $this->find($criteria, 'id', 'ASC');
	}

	/**
	 * すべてのレコードをイテレータとして返す (たとえ、全レコードを取ってもメモリリークにならないはず)
	 * @return Mailform_Component_EntryResultIterator 失敗した場合FALSE
	 */
	public function findModelsAsIterator()
	{
		$query = 'SELECT * FROM %s ORDER BY id ASC';
		$query = sprintf($query, $this->table);
		$result = $this->db->query($query);

		try {
			return new Mailform_Component_EntryResultIterator($result, $this);
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * テーブルを作成する
	 * @param int $formId
	 * @return bool
	 */
	public function createTable($formId)
	{
		$fieldModels = $this->_getFieldModels($formId);

		if ( count($fieldModels) === 0 ) {
			return false;
		}

		$createTableQuery = $this->_getCreateTableQuery($formId, $fieldModels);

		if ( $createTableQuery === false ) {
			return false;
		}

		/**
		 * CREATE TABLE は 「暗黙のトランザクションコミット」を引き起こす。
		 * そのため、ここではわざと別コネクションでDBに接続し、CREATE TABLEを実行する
		 * @see http://dev.mysql.com/doc/refman/5.1/ja/implicit-commit.html MySQL :: MySQL 5.1 リファレンスマニュアル :: 12.4.3 暗黙のコミットを引き起こすステートメント
		 * @see http://qiita.com/items/2545 MySQLの「暗黙のトランザクションコミット」対策：トランザクション中でも安全にCREATE TABLEなどをする方法
		 */
		$connection = Mailform_Component_DatabaseFactory::getConnection();
		$result = $connection->query($createTableQuery);

		if ( $result === false ) {
			return false;
		}

		return true;
	}

	/**
	 * テーブルがあれば削除する
	 * @param int $formId
	 * @return bool
	 */
	public function dropTableIfExists($formId)
	{
		$tableName = $this->_getTableName($formId);
		$query = 'DROP TABLE IF EXISTS %s';
		$query = sprintf($query, $tableName);

		/* DROP TABLEは「暗黙のトランザクションコミット」を引き起こすので、別コネクションで行う */
		$connection = Mailform_Component_DatabaseFactory::getConnection();
		$result = $connection->query($query);

		if ( $result === false ) {
			return false;
		}

		return true;
	}

	/**
	 * CREATE TABLE構文を返す
	 * @param int $formId
	 * @return bool
	 */
	public function getCreateTableSyntax($formId)
	{
		$tableName = $this->_getTableName($formId);
		$query = 'SHOW CREATE TABLE %s';
		$query = sprintf($query, $tableName);

		/* SHOW CREATE TABLE も念のために別コネクションで行う */
		$connection = Mailform_Component_DatabaseFactory::getConnection();
		$result = $connection->query($query);

		if ( $result === false ) {
			return false;
		}

		$row = $connection->fetchArray($result);

		if ( is_array($row) === false or isset($row['Create Table']) === false ) {
			return false;
		}

		return $row['Create Table'];
	}

	/**
	 * テーブルが変更可能かを返す
	 * @return bool
	 */
	public function isTableChangable()
	{
		$count = $this->count();

		if ( $count > 0 ) {
			return false;
		}

		return true;
	}
	
	/**
	 * フィールドモデルを返す
	 * @param int $formId
	 * @return array
	 */
	protected function _getFieldModels($formId)
	{
		$handler = $this->_getFieldHandler();
		return $handler->findByFormId($formId);
	}

	/**
	 * テーブル作成クエリを返す
	 * @param int $formId
	 * @param array $fieldModels
	 * @return bool|string
	 */
	protected function _getCreateTableQuery($formId, array $fieldModels)
	{
		$tableBody = array();
		$tableBody[] = "`id` int(11) unsigned NOT NULL AUTO_INCREMENT";

		/** @var Mailform_Model_Field $fieldModel */
		foreach ( $fieldModels as $fieldModel ) {
			$column = $fieldModel->getColumnDefinition();

			if ( $column === false ) {
				return false;
			}

			$tableBody[] = $column;
		}

		$tableBody[] = "`created` datetime DEFAULT NULL";
		$tableBody[] = "`creator_id` mediumint(8) unsigned DEFAULT NULL";
		$tableBody[] = "`modified` datetime DEFAULT NULL";
		$tableBody[] = "`modifier_id` mediumint(8) unsigned DEFAULT NULL";
		$tableBody[] = "PRIMARY KEY (`id`)";
		$tableBody = implode(",\n", $tableBody);

		$tableName = $this->_getTableName($formId);

		$createTable = "CREATE TABLE `%s` (\n%s\n) ENGINE=InnoDB";
		$createTable = sprintf($createTable, $tableName, $tableBody);
		return $createTable;
	}

	/**
	 * テーブル名を返す
	 * @param int $formId
	 * @return string
	 */
	protected function _getTableName($formId)
	{
		return $this->db->prefix($this->dirname.'_entry_'.$formId);
	}

	/**
	 * フィールドハンドラーを返す
	 * @return Mailform_Model_FieldHandler
	 */
	protected function _getFieldHandler()
	{
		$pengin = Pengin::getInstance();
		return $pengin->getModelHandler('Field', 'mailform');
	}
}
