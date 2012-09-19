<?php

class Mailform_Component_EntryResultIterator extends Mailform_Component_MySQLResultIterator
{
	/** @var Mailform_Model_EntryHandler */
	protected $entryHandler = null;

	public function __construct($result, Mailform_Model_EntryHandler $entryHandler)
	{
		parent::__construct($result);
		$this->entryHandler = $entryHandler;
	}

	/**
	 * 現在の行を返す
	 * @return array
	 */
	public function current()
	{
		$current = parent::current();

		if ( $current === false ) {
			return false;
		}

		/** @var Mailform_Model_Entry $entryModel  */
		$entryModel = $this->entryHandler->create();
		$entryModel->setVars($current);

		return $entryModel;
	}
}
