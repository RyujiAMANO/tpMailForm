<?php

class Mailform_Component_MySQLDatabase extends XoopsMySQLDatabaseSafe
{
	/**
	 * connect to the database
	 *
	 * @param bool $selectdb select the database now?
	 * @return bool successful?
	 */
	function connect($selectdb = true)
	{
		if ( XOOPS_DB_PCONNECT == 1 ) {
			$this->conn = @mysql_pconnect(XOOPS_DB_HOST, XOOPS_DB_USER, XOOPS_DB_PASS, true, MYSQL_CLIENT_FOUND_ROWS);
		} else {
			$this->conn = @mysql_connect(XOOPS_DB_HOST, XOOPS_DB_USER, XOOPS_DB_PASS, true, MYSQL_CLIENT_FOUND_ROWS);
		}

		if ( !$this->conn ) {
			$this->logger->addQuery('', $this->error(), $this->errno());
			return false;
		}

		if ( $selectdb != false ) {
			if ( !mysql_select_db(XOOPS_DB_NAME) ) {
				$this->logger->addQuery('', $this->error(), $this->errno());
				return false;
			}
		}
		return true;
	}
}
