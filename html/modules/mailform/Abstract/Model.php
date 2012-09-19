<?php

abstract class Mailform_Abstract_Model extends Pengin_Model_AbstractModel
{
	const JSON_ARRAY = 999;

	/**
	 * @param $name
	 * @param $value
	 * @return bool
	 *
	 * JSON_ARRAYに対応するためにオーバーライド
	 */
	public function setVar($name, $value)
	{
		if ( isset($this->vars[$name]) === false ) {
			return false;
		}

		$type = $this->vars[$name]['type'];

		if ( $type == self::JSON_ARRAY ) {

			if ( is_string($value) === true ) {
				$value = json_decode($value, true);
			}

			if ( is_array($value) === false ) {
				$value = array();
			}

			$this->vars[$name]['value'] = $value;
		} else  {
			parent::setVar($name, $value);
		}

		return true;
	}

	/**
	 * @param $name
	 * @return mixed
	 *
	 * JSON_ARRAYに対応するためにオーバーライド
	 */
	public function getVarSqlEscaped($name)
	{
		$type  = $this->vars[$name]['type'];

		if ( $type == self::JSON_ARRAY ) {
			$json = json_encode($this->vars[$name]['value']);
			return mysql_real_escape_string($json);
		} else  {
			return parent::getVarSqlEscaped($name);
		}
	}
}
