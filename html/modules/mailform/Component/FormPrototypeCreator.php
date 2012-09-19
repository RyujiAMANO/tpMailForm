<?php

/**
 * お問い合わせフォームのプロトタイプ作成を担当するクラス
 */
class Mailform_Component_FormPrototypeCreator
{
	/** @var Mailform_Model_Form */
	protected $formModel = null;

	/**
	 * 作成する
	 * @param string $title
	 * @return bool
	 */
	public function create($title)
	{
		if ( $this->_createForm($title) === false ) {
			return false;
		}

		$formId = $this->formModel->get('id');
		$fields = $this->_getDefaultFields();

		if ( $this->_createFields($formId, $fields) === false ) {
			return false;
		}

		if ( $this->_createEntryTable($formId) === false ) {
			return false;
		}

		return true;
	}

	/**
	 * フォームIDを返す
	 * @return integer
	 */
	public function getFormId()
	{
		if ( is_object($this->formModel) === false ) {
			return false;
		}

		return $this->formModel->get('id');
	}

	/**
	 * 現在のユーザのメールアドレスを返す
	 * @return string
	 */
	protected function _getCurrentUserEmail()
	{
		$root = XCube_Root::getSingleton();

		/** @var XoopsUser $user */
		$user = $root->mContext->mXoopsUser;

		if ( is_object($user) === false ) {
			return '';
		}

		return $user->get('email');
	}

	/**
	 * フォームを作成する
	 * @param string $title
	 * @return bool
	 */
	protected function _createForm($title)
	{
		$handler = $this->_getFormHandler();

		$this->formModel = $handler->create();
		$this->formModel->set('title', $title);
		$this->formModel->set('mail_to_sender', Mailform_Model_Form::MAIL_TO_SENDER_YES);
		$this->formModel->set('mail_to_receiver', Mailform_Model_Form::MAIL_TO_RECEIVER_YES);
		$this->formModel->set('receiver_email', $this->_getCurrentUserEmail());
		$this->formModel->set('header_description', $this->_getDefaultHeaderDescription());
		$this->formModel->set('finish_message', $this->_getDefaultFisnishMessage());
		$this->formModel->set('status', Mailform_Model_Form::STATUS_PRIVATE);

		return $handler->save($this->formModel);
	}

	/**
	 * フォーム説明文を返す
	 * @return string
	 */
	protected function _getDefaultHeaderDescription()
	{
		return t('<p>Please fill out the form below to contact us.</p>');
	}

	/**
	 * フォーム送信完了画面の文言を返す
	 * @return string
	 */
	protected function _getDefaultFisnishMessage()
	{
		$message = '';
		$message .= t("Thank you for the inquiry.")."\n";
		$message .= t("We will be in touch soon.")."\n";
		$message .= t("We sent e-mail to you for the check.")."\n";
		return $message;
	}

	/**
	 * デフォルト入力欄を返す
	 * @return array
	 */
	protected function _getDefaultFields()
	{
		$defaultFields = array(
			// 名前
			array(
				'type'        => 'Name',
				'label'       => t("Your Name"),
				'description' => '',
				'required'    => Mailform_Model_Field::REQUIRED_YES,
			),
			// 宛先
			array(
				'type'        => 'Email',
				'label'       => t("Your Email"),
				'description' => t("Please input your email address exactly."),
				'required'    => Mailform_Model_Field::REQUIRED_YES,
			),
			// 本文
			array(
				'type'        => 'Textarea',
				'label'       => t("Body Text"),
				'description' => '',
				'required'    => Mailform_Model_Field::REQUIRED_YES,
			),
		);

		return $defaultFields;
	}

	/**
	 * 入力欄を作成する
	 * @param integer $formId
	 * @param array $fields
	 * @return bool
	 */
	protected function _createFields($formId, array $fields)
	{
		$pluginManager = $this->_getPluginManager();
		$handler = $this->_getFieldHandler();

		$weight = 1;

		foreach ( $fields as $field ) {
			$plugin = $pluginManager->getPlugin($field['type']);

			/** @var Mailform_Model_Field $model */
			$model = $handler->create();
			$model->setVars($field);
			$model->set('form_id', $formId);
			$model->set('options', $plugin->getDefaultPluginOptions());
			$model->set('weight', $weight);

			if ( $handler->save($model) === false ) {
				return false;
			}

			if ( $handler->autoUpdateName($model) === false ) {
				return false;
			}

			$weight += 1;
		}

		return true;
	}

	/**
	 * エントリーテーブルを作る
	 * @param int $formId
	 * @return bool
	 */
	protected function _createEntryTable($formId)
	{
		$entryHandler = $this->_getEntryHandler();
		return $entryHandler->createTable($formId);
	}

	/**
	 * @return Mailform_Model_FormHandler
	 */
	protected function _getFormHandler()
	{
		$pengin = Pengin::getInstance();
		return $pengin->getModelHandler('Form', 'mailform');
	}

	/**
	 * @return Mailform_Model_FieldHandler
	 */
	protected function _getFieldHandler()
	{
		$pengin = Pengin::getInstance();
		return $pengin->getModelHandler('Field', 'mailform');
	}

	/**
	 * @return Mailform_Model_EntryHandler
	 */
	protected function _getEntryHandler()
	{
		$pengin = Pengin::getInstance();
		return $pengin->getModelHandler('Entry', 'mailform');
	}
	
	/**
	 * @return Mailform_Plugin_Manager
	 */
	protected function _getPluginManager()
	{
		return Mailform_Plugin_Manager::getInstance();
	}
}
