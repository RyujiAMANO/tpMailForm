<?php
class Mailform_Model_Form extends Mailform_Abstract_Model
{
	const MAIL_TO_SENDER_YES = 1;
	const MAIL_TO_SENDER_NO  = 0;

	const MAIL_TO_RECEIVER_YES = 1;
	const MAIL_TO_RECEIVER_NO  = 0;

	// 公開状況フラグ
	const STATUS_PRIVATE = 1; // 未公開
	const STATUS_OPEN    = 2; // 受付中
	const STATUS_CLOSE   = 3; // 終了

	/** @var Mailform_Model_EntryHandler */
	protected $entryHandler = null;

	public function __construct()
	{
		$this->val('id', self::INTEGER, null, 11);
		$this->val('title', self::STRING, null, 255);
		$this->val('mail_to_sender', self::INTEGER, null, 1);
		$this->val('mail_to_receiver', self::INTEGER, null, 1);
		$this->val('receiver_email', self::TEXT, null);
		$this->val('header_description', self::TEXT, null);
		$this->val('finish_message', self::TEXT, null);
		$this->val('options', self::JSON_ARRAY, null);
		$this->val('status', self::INTEGER, 0, 1);
		$this->val('created', self::DATETIME, null);
		$this->val('creator_id', self::INTEGER, null, 8);
		$this->val('modified', self::DATETIME, null);
		$this->val('modifier_id', self::INTEGER, null, 8);
	}

	/**
	 * 未公開かを返す
	 * @return bool
	 */
	public function isPrivate()
	{
		return ( $this->get('status') == self::STATUS_PRIVATE );
	}

	/**
	 * 公開中かを返す
	 * @return bool
	 */
	public function isOpened()
	{
		return ( $this->get('status') == self::STATUS_OPEN );
	}

	/**
	 * 終了かを返す
	 * @return bool
	 */
	public function isClosed()
	{
		return ( $this->get('status') == self::STATUS_CLOSE );
	}

	/**
	 * 公開状況のリストを返す
	 * @static
	 * @return array
	 */
	public static function getStatusList()
	{
		return array(
			self::STATUS_PRIVATE => t("Private"),
			self::STATUS_OPEN    => t("Opened"),
			self::STATUS_CLOSE   => t("Closed"),
		);
	}

	/**
	 * 公開状況を文字列(文言)で返す
	 * @return string
	 */
	public function getStatusAsString()
	{
		$status = $this->get('status');
		$list = $this->getStatusList();
		return $list[$status];
	}

	/**
	 * メール処理を持つかを返す
	 * @return bool
	 */
	public function hasMailProccess()
	{
		if ( $this->mailsToSender() === true or $this->mailsToReceiver() === true ) {
			return true;
		}

		return false;
	}
	
	/**
	 * 送信者に控えメールを送信するかを返す
	 * @return bool
	 */
	public function mailsToSender()
	{
		return ( $this->get('mail_to_sender') == self::MAIL_TO_SENDER_YES );
	}

	/**
	 * 担当者に通知メールを送信するかを返す
	 * @return bool
	 */
	public function mailsToReceiver()
	{
		return ( $this->get('mail_to_receiver') == self::MAIL_TO_RECEIVER_YES );
	}

	/**
	 * カンマ区切りのメールアドレスを配列に変換する
	 * @static
	 * @param string $email
	 * @return array
	 */
	public static function emailTextToArray($email)
	{
		$array = explode(',', $email); // 分割
		$array = array_map('trim', $array); // 各要素をtrim()にかける
		$array = array_filter($array, 'strlen'); // 文字数が0のやつを取り除く
		$array = array_values($array); // これはキーを連番に振りなおしてるだけ
		return $array;
	}

	/**
	 * 配列のメールアドレスを文字列に変換する
	 * @static
	 * @param array $emails
	 * @return string
	 */
	public static function emailArrayToText(array $emails)
	{
		return implode(',', $emails);
	}
	
	/**
	 * 担当者のメールアドレスを配列で返す
	 * @return array
	 */
	public function getReceiverEmails()
	{
		$emails = $this->get('receiver_email');
		$emails = $this->emailTextToArray($emails);
		return $emails;
	}

	/**
	 * 担当者代表メールアドレスを返す
	 * @return bool|string
	 */
	public function getReceiverMainEmail()
	{
		$emails = $this->getReceiverEmails();

		if ( count($emails) === 0 ) {
			return false;
		}

		return reset($emails); // 代表メールは最初の1つめ
	}
	
	/**
	 * フィールドを返す
	 * @return array
	 */
	public function getFieldsWithNameAsKey()
	{
		$formId = $this->get('id');
		$handler = $this->_getFieldHandler();
		return $handler->findByFormIdWithNameAsKey($formId);
	}

	/**
	 * メールフィールドと名前フィールドを持っているかを返す
	 * @return bool
	 */
	public function hasEmailFieldAndNameField()
	{
		$formId = $this->get('id');
		$handler = $this->_getFieldHandler();
		return $handler->existsEmailAndNameForForm($formId);
	}

	/**
	 * エントリーハンドラーを返す
	 * @return Mailform_Model_EntryHandler
	 */
	public function getEntryHandler()
	{
		if ( $this->entryHandler === null ) {
			$this->entryHandler = $this->_createEntryHandler();
		}

		return $this->entryHandler;
	}

	/**
	 * エントリーハンドラーを作成する
	 * @return Mailform_Model_EntryHandler
	 */
	protected function _createEntryHandler()
	{
		$handler = new Mailform_Model_EntryHandler('mailform');
		$handler->setTableId($this->get('id'));
		return $handler;
	}

	/**
	 * フィールドハンドラーを返す
	 * @return Mailform_Model_FieldHandler
	 */
	protected function _getFieldHandler()
	{
		$pengin = Pengin::getInstance();
		$handler = $pengin->getModelHandler('Field', 'mailform');
		return $handler;
	}
}
