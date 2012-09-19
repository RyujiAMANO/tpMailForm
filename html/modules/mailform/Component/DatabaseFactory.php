<?php

require_once XOOPS_ROOT_PATH . '/class/database/' . XOOPS_DB_TYPE . 'database.php';

/**
 * 複数コネクションを扱えるようにしたDatabaseFactory
 */
class Mailform_Component_DatabaseFactory extends XoopsDatabaseFactory
{
	protected static $connections = array();

	/**
	 * DBコネクションを返す
	 * @static
	 * @param string $connectionName コネクション名
	 * @return XoopsMySQLDatabase|bool 失敗した場合 FALSE を返す
	 */
	public static function getConnection($connectionName = 'default')
	{
		if ( isset(self::$connections[$connectionName]) === false ) {

			/** @var XoopsDatabase $connection */
			$connection = new Mailform_Component_MySQLDatabase();
			$connection->setLogger(self::_getLogger());
			$connection->setPrefix(XOOPS_DB_PREFIX);

			if ( $connection->connect() === false ) {
				trigger_error('Unable to connect to database', E_USER_ERROR);
				return false;
			}

			self::$connections[$connectionName] = $connection;
		}

		return self::$connections[$connectionName];
	}

	/**
	 * @static
	 * @return XoopsLogger
	 */
	protected static function _getLogger()
	{
		$root = XCube_Root::getSingleton();
		return $root->mController->mLogger;
	}
}
