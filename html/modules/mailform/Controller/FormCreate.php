<?php

class Mailform_Controller_FormCreate extends Pengin_Controller_AbstractThreeStepSimpleForm
{
	protected $useModels = array('Form'); // 使用するモデルを定義

	public function main()
	{
		$this->_checkPermission();
		$this->_checkAdminTaskBar();
		$this->_setUpPageTitle();
		parent::main();
	}

	protected function _checkAdminTaskBar()
	{
		if ( Mailform_Component_AdminTaskBar::isAvailable() === true ) {
			$this->root->location($this->root->cms->url); // AdminTaskBarが使える環境ではこの画面を使わない
		}
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
	 * ページタイトルをセットアップする
	 */
	protected function _setUpPageTitle()
	{
		$this->pageTitle = $this->form->getTitle();
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
	 * @return Mailform_Form_Form
	 */
	protected function _getForm()
	{
		return new Mailform_Form_FormCreate(); // フォーム
	}

	/**
	 * データを更新する.
	 *
	 * @access protected
	 * @abstract
	 * @return void
	 */
	protected function _updateData()
	{
		$input = $this->form->getInput();
		$creator = new Mailform_Component_FormPrototypeCreator();

		if ( $creator->create($input['title']) === false ) {
			throw new Exception(t("Failed to save."));
		}

		$this->model = $this->modelHandler->load($creator->getFormId());
	}

	protected function _getReturnUri()
	{
		return $this->root->url('form_list');
	}
}
