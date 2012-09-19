<?php

/**
 * フォーム公開状況管理用のモデル
 * @property $nextStatus
 * @property $nextStatusLabel
 * @property $confirmMessage
 * @property $warningMessages
 * @property $informationMessages
 * @property $submitButtonLabel
 */
class Mailform_Component_FormStatusModel
{
	protected $nextStatus = 0;
	protected $nextStatusLabel = '';
	protected $confirmMessage = '';
	protected $warningMessages = array();
	protected $informationMessages = array();
	protected $submitButtonLabel = '';

	/**
	 * @param Mailform_Model_Form $form
	 */
	public function __construct(Mailform_Model_Form $form)
	{
		if ( $form->isPrivate() === true ) {
			$this->_constructPrivate();
		} elseif ( $form->isOpened() === true ) {
			$this->_constructOpened();
		} elseif ( $form->isClosed() === true ) {
			$this->_constructClosed();
		} else {
			throw new RuntimeException('Unexpected form status.');
		}
	}

	/**
	 * ゲッター
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->$name;
	}

	/**
	 * 遷移可能なステータスがあるかを返す
	 * @return bool
	 */
	public function hasNext()
	{
		if ( $this->nextStatus == false ) {
			return false;
		}

		return true;
	}

	/**
	 * 未公開ステータスのコンストラクタ
	 */
	protected function _constructPrivate()
	{
		$this->nextStatus = Mailform_Model_Form::STATUS_OPEN;
		$this->nextStatusLabel = t("Opened");
		$this->confirmMessage = t("Are you sure to open this form?");
		$this->warningMessages = array(
			t("If form is opened once, it will be unable to change 'Screen Preference' any more."),
			t("If form is opened once, it will be unable to rewind to private status."),
		);
		$this->informationMessages = $this->warningMessages;
		$this->submitButtonLabel = t("Open This Form");
	}

	/**
	 * 受付中ステータスのコンストラクタ
	 */
	protected function _constructOpened()
	{
		$this->nextStatus = Mailform_Model_Form::STATUS_CLOSE;
		$this->nextStatusLabel = t("Closed");
		$this->confirmMessage = t("Are you sure to close this form?");
		$this->warningMessages = array(
			t("If form is closed once, it will be unable to rewind to opened status."),
		);
		$this->informationMessages = $this->warningMessages;
		$this->submitButtonLabel = t("Close This Form");
	}

	/**
	 * 終了ステータスのコンストラクタ
	 */
	protected function _constructClosed()
	{
		$this->nextStatus = false;
	}
}
