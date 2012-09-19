<?php

class Mailform_Controller_Form extends Pengin_Controller_AbstractThreeStepForm
{
	protected $id = null;
	/** @var Mailform_Form_Form */
	protected $form = null;
	/** @var Mailform_Model_Form */
	protected $formModel = null;
	/** @var Mailform_Model_Entry */
	protected $entryModel = null;
	protected $fieldModels = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function main()
	{
		$this->_checkPermission();
		parent::main();
	}

	protected function _checkPermission()
	{
		if ( $this->root->cms->isAdmin() === false and $this->formModel->isOpened() === false ) {
			$this->root->redirect(t("Page not found."), 'form_list');
		}
	}
	
	protected function _setUp()
	{
		$this->_setUpId();
		$this->_setUpFormModel();
		$this->_setUpFieldModels();
		$this->_setUpForm();
		$this->_setUpPageTitle();
	}

	protected function _setUpForm()
	{
		$this->form = new Mailform_Form_Form($this->fieldModels);
	}

	protected function _setUpPageTitle()
	{
		$this->pageTitle = $this->formModel->get('title');
	}
	
	protected function _useInputTemplate()
	{
		$this->template = 'pen:mailform.form.default.tpl';
	}
	
	protected function _useConfirmTemplate()
	{
		$this->template = 'pen:mailform.form.confirm.tpl';
	}

	/**
	 * 入力画面アクション
	 */
	protected function _inputAction()
	{
		$this->output['id'] = $this->id;
		$this->output['form'] = $this->form;
		$this->output['formModel'] = $this->formModel;
		$this->_adminTaskBar();
		parent::_inputAction();
	}

	protected function _setUpId()
	{
		$this->id = $this->root->request('id');

		if ( $this->id === null ) {
			$this->root->location('form_list');
		}
	}
	
	protected function _setUpFormModel()
	{
		$formHandler = $this->root->getModelHandler('Form');
		$formModel = $formHandler->load($this->id);

		if ( is_object($formModel) === false or $formModel->isNew() === true ) {
			$this->root->redirect("no contents here", $this->root->cms->url);
		}

		$this->formModel = $formModel;
	}

	protected function _setUpFieldModels()
	{
		/** @var Mailform_Model_FieldHandler $fieldHandler  */
		$fieldHandler = $this->root->getModelHandler('Field');
		$fieldModels = $fieldHandler->findByFormId($this->id);
		
		if ( is_array($fieldModels) === false or count($fieldModels) === 0 ) {
			if ( $this->root->cms->isAdmin() === true ) {
				$this->root->location('field_edit', null, array('id' => $this->id));
			} else {
				$this->root->redirect("no contents here", $this->root->cms->url);
			}
		}

		$this->fieldModels = $fieldModels;
	}

	protected function _updateData()
	{
		if ( $this->formModel->isPrivate() === true ) {
			return; // 未公開のフォームは投稿しても、投稿内容の保存/メール送信を行いません。
		}

		$this->_saveEntry();
		$this->_mailProcess();
	}

	/**
	 * 送信内容をDBに保存する
	 */
	protected function _saveEntry()
	{
		$entryHandler = $this->formModel->getEntryHandler();
		$this->entryModel = $entryHandler->create();
		$this->form->updateEntryModel($this->entryModel);

		if ( $entryHandler->save($this->entryModel) === false ) {
			throw new RuntimeException();
		}
	}

	/**
	 * メールを送信する
	 */
	protected function _mailProcess()
	{
		if ( $this->formModel->hasMailProccess() === false ) {
			return; // メール送信処理がないフォームの場合
		}

		$xoopsConfig = $this->root->cms->getConfig();
		$mailData = new Mailform_Mail_MailData($this->formModel, $this->fieldModels, $this->entryModel, $xoopsConfig);

		// 送信者に控えメールを送信する
		if ( $this->formModel->mailsToSender() === true ) {
			$mailToSender = new Mailform_Mail_MailToSender($mailData);
			$mailToSender->send();
		}

		// 担当者に通知メールを送信する
		if ( $this->formModel->mailsToReceiver() === true ) {
			$mailToReceiver = new Mailform_Mail_MailToReceiver($mailData);
			$mailToReceiver->send();
		}
	}
	
	/**
	 * フォームのトランザクション終了時の処理用メソッド.
	 * 
	 * @access protected
	 * @return void
	 * @note データベースのトランザクションではない
	 */
	protected function _afterTransaction()
	{
		$this->template = 'pen:mailform.form.finish.tpl';
	}

	/**
	 * NiceAdmin タスクバー 連携
	 */
	protected function _adminTaskBar()
	{
		$adminTaskBar = Mailform_Component_AdminTaskBar::getInstance();
		$adminTaskBar->show($this->formModel);
	}
}
