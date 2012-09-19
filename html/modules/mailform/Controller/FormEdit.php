<?php

class Mailform_Controller_FormEdit extends Pengin_Controller_AbstractThreeStepSimpleForm
{
	protected $useModels = array('Form'); // 使用するモデルを定義

	/** @var Mailform_Model_FormHandler */
	protected $formHandler = null;

	/** @var Mailform_Model_Form */
	protected $model = null;

	/**
	 * main function.
	 */
	public function main()
	{
		$this->_setUpPageTitle();
		$this->_checkPermission();
		parent::main();
		$this->_adminTaskBar();
	}

	/**
	 * 権限チェック
	 */
	protected function _checkPermission()
	{
		if ( $this->root->cms->isAdmin() === false ) {
			$this->root->redirect("Permission denied.", $this->root->cms->url);
		}
	}

	/**
	 * モデルセットをセットアップする.
	 * 
	 * @access protected
	 * @return void
	 */
	protected function _setUpModel()
	{
		parent::_setUpModel();

		if ( $this->model->isNew() === true ) {
			$this->root->redirect("Page not found.", $this->root->cms->url);
		} 
	}

	/**
	 * モデルハンドラーを返す.
	 * @return Mailform_Model_FormHandler
	 */
	protected function _getModelHandler()
	{
		return $this->formHandler; // モデルハンドラー
	}

	/**
	 * フォームオブジェクトを返す.
	 * @return Mailform_Form_FormEdit
	 */
	protected function _getForm()
	{
		return new Mailform_Form_FormEdit($this->model); // フォーム
	}

	/**
	 * 戻り先URIを返す.
	 * @return string 戻り先URI
	 */
	protected function _getReturnUri()
	{
		return $this->url.'/index.php?id='.$this->id;
	}
	
	/**
	 * NiceAdmin タスクバー 連携
	 */
	protected function _adminTaskBar()
	{
		$adminTaskBar = Mailform_Component_AdminTaskBar::getInstance();
		$adminTaskBar->show($this->model);
	}

	protected function _setUpPageTitle()
	{
		$this->pageTitle = $this->form->getTitle();
	}
}
