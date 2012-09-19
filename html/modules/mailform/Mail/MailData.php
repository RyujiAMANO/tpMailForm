<?php

/**
 * メールデータ
 */
class Mailform_Mail_MailData
{
	protected $form = null;
	/** @var Mailform_Model_Field[] */
	protected $fields = array();
	protected $entry = null;
	protected $data = array(
		'site_name'           => '',
		'form_title'          => '',
		'sender_email'        => false,
		'sender_name'         => false,
		'receiver_emails'     => array(),
		'receiver_main_email' => '',
		'fields'              => array(),
	);

	/**
	 * @param Mailform_Model_Form $formModel
	 * @param array $fieldModels
	 * @param Mailform_Model_Entry $entryModel
	 * @param array $xoopsConfig
	 */
	public function __construct(Mailform_Model_Form $formModel, array $fieldModels, Mailform_Model_Entry $entryModel, array $xoopsConfig)
	{
		$this->form = $formModel;
		$this->fields = $fieldModels;
		$this->entry = $entryModel;

		$this->data['site_name'] = $xoopsConfig['sitename'];
		$this->data['form_title'] = $this->form->get('title');
		$this->data['receiver_emails'] = $this->form->getReceiverEmails();
		$this->data['receiver_main_email'] = $this->form->getReceiverMainEmail();
		$this->data['sender_email'] = $this->_getSenderEmail();
		$this->data['sender_name'] = $this->_getSenderName();
		$this->data['fields'] = $this->_getEntryData();
	}

	/**
	 * データを返す
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * 送信者のメールアドレスを返す
	 * @return bool|string
	 */
	protected function _getSenderEmail()
	{
		$field = $this->_getEmailTypeField();

		if ( $field === false ) {
			return false;
		}

		$name = $field->get('name');
		return $this->entry->get($name);
	}

	/**
	 * メール型のフィールドを返す
	 * @return bool|Mailform_Model_Field
	 */
	protected function _getEmailTypeField()
	{
		foreach ( $this->fields as $field ) {
			if ( $field->isEmailType() === true ) {
				return $field;
			}
		}

		return false;
	}

	/**
	 * 送信者名を返す
	 * @return bool|string
	 */
	protected function _getSenderName()
	{
		$field = $this->_getNameTypeField();

		if ( $field === false ) {
			return false;
		}

		$name = $field->get('name');
		$senderName = $this->entry->get($name);

		if ( $senderName == '' ) {
			$senderName = $this->data['sender_email']; // 名前が不明な場合「email@example.com 様」という風にして対応する
		}

		if ( $senderName == '' ) {
			$senderName = 'No name'; // TODO
		}

		return $senderName;
	}

	/**
	 * 名前型のフィールドを返す
	 * @return bool|Mailform_Model_Field
	 */
	protected function _getNameTypeField()
	{
		foreach ( $this->fields as $field ) {
			if ( $field->isNameType() === true ) {
				return $field;
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	protected function _getEntryData()
	{
		$data = array();

		foreach ( $this->fields as $field ) {
			$name  = $field->get('name');
			$label = $field->get('label');
			$value = $field->valueToString($this->entry);

			if ( strpos($value, "\n") === false ) {
				// nothing to do.
			} else {
				$value = "\n".$value; // 改行があったときは項目名の次の行に送る
			}

			$data[$name] = array(
				'label' => $label,
				'value' => $value,
			);
		}

		return $data;
	}
}
