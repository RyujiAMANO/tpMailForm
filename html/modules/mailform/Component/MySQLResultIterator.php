<?php

/**
 * MySQLの結果イテレータ
 */
class Mailform_Component_MySQLResultIterator implements SeekableIterator, Countable
{
	/** @var resource */
	protected $result   = null;
	protected $total    = 0;
	protected $position = 0;

	/**
	 * @param resource $result
	 */
	public function __construct($result)
	{
		if ( is_resource($result) === false or get_resource_type($result) !== 'mysql result' ) {
			throw new InvalidArgumentException("Result type must be MySQL result set.");
		}

		$total = mysql_num_rows($result);

		if ( $total === false ) {
			throw new RuntimeException("Failed to detect row count.");
		}

		$this->result = $result;
		$this->total  = $total;
	}

	/**
	 * 最初の行に巻き戻す
	 */
	public function rewind()
	{
		$this->position = 0;

		if ( $this->_isValid($this->position) === true ) {
			$this->_seek($this->position);
		}
	}

	/**
	 * 現在の行を返す
	 * @return array
	 */
	public function current()
	{
		$current = mysql_fetch_assoc($this->result);
		$this->_seek($this->position); // 進んだぶん、戻す
		return $current;
	}

	/**
	 * 現在の行のキーを返す
	 * @return int
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * 次の行に進む
	 * @return void
	 */
	public function next()
	{
		$this->position += 1;

		if ( $this->_isValid($this->position) === false ) {
			return;
		}

		$this->_seek($this->position);
	}

	/**
	 * 現在位置が有効かどうかを調べる
	 * @return bool
	 */
	public function valid()
	{
		return $this->_isValid($this->position);
	}

	/**
	 * 行数を返す
	 * @return int
	 */
	public function count()
	{
		return $this->total;
	}

	/**
	 * 行を移動する
	 * @param int $position
	 * @throws OutOfBoundsException
	 */
	public function seek($position)
	{
		$position = intval($position);

		if ( $this->_isValid($position) === false ) {
			throw new OutOfBoundsException();
		}

		$this->_seek($position);
	}

	protected function _isValid($position)
	{
		return ( $position < $this->total );
	}

	protected function _seek($position)
	{
		mysql_data_seek($this->result, $position);
	}
}
