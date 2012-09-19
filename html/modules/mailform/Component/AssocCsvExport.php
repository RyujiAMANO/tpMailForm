<?php

/**
 * 連想配列でCSV出力を行うクラス
 *
 * ヘッダ行を連想配列で定義すると、ヘッダ行のカラムの順番に合わせて、値を出力する。
 * ヘッダ行にないキーは無視される。
 * 順番が保証されない場合に使う。
 */
class Mailform_Component_AssocCsvExport extends Pengin_CsvExport
{
	protected $header = array();
	protected $rowPrototype = array();

	/**
	 * @param array $header ヘッダ行の連想配列
	 */
	public function __construct(array $header)
	{
		parent::__construct();
		$this->header = $header;

		// 値が空の連想配列を作る
		$this->rowPrototype = array_fill_keys(array_keys($header), null);
	}

	/**
	 * ヘッダ行を追加する
	 */
	public function addHeaderRow()
	{
		$this->addRow($this->header);
	}

	/**
	 * 連想配列で行を追加する
	 * @param array $assoc
	 *
	 * 注意: ヘッダ行に対応しない要素はカラムとして追加されません。
	 */
	public function addRowAssoc(array $assoc)
	{
		$row = $this->rowPrototype;

		foreach ( $assoc as $name => $value ) {
			if ( array_key_exists($name, $row) === true ) {
				$row[$name] = $value;
			}
		}

		$this->addRow($row);
	}
}
