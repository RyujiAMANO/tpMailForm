<?php

class Mailform_Controller_FormStatusEdit extends Mailform_Abstract_Controller
{
	protected $id = 0;

	/** @var Mailform_Model_FormHandler */
	protected $formHandler = null;
	/** @var Mailform_Model_Form */
	protected $formModel = null;
	/** @var Mailform_Component_FormStatusModel */
	protected $formStatus = null;

	protected $errors = array();

	public function main()
	{
		$this->_checkPermission();
		$this->_setUpId();
		$this->_setUpFormHandler();
		$this->_setUpFormModel();
		$this->_setUpFormStatus();
		$this->_setUpPageTitle();
		$this->_dispatch();
		$this->_adminTaskBar();
	}

	protected function _checkPermission()
	{
		if ( $this->root->cms->isAdmin() === false ) {
			$this->root->redirect("Permission denied.", $this->root->cms->url);
		}
	}

	protected function _setUpId()
	{
		$this->id = $this->get('id');
	}

	protected function _setUpFormHandler()
	{
		$this->formHandler = $this->root->getModelHandler('Form');
	}

	protected function _setUpFormModel()
	{
		$this->formModel = $this->formHandler->load($this->id);

		if ( is_object($this->formModel) === false or $this->formModel->isNew() === true ) {
			$this->root->redirect("Page not found.", $this->root->cms->url);
		}
	}

	protected function _setUpFormStatus()
	{
		$this->formStatus = new Mailform_Component_FormStatusModel($this->formModel);
	}

	protected function _setUpPageTitle()
	{
		$this->pageTitle = t("Form Open Status Management");
	}

	protected function _dispatch()
	{
		try {
			if ( $this->post('confirm') ) {
				$this->_confirmAction();
				return;
			} elseif ( $this->post('save') ) {
				$this->_saveAction();
				return;
			}
		} catch ( RuntimeException $e ) {
			// Do nothing.
		}

		$this->_defaultAction();
	}

	protected function _confirmAction()
	{
		$this->_validate();
		$this->_confirmView();
	}

	protected function _saveAction()
	{
		$this->_validate();
		$this->formModel->set('status', $this->formStatus->nextStatus);

		if ( $this->formHandler->save($this->formModel) == false ) {
			$this->errors[] = t("Failed to update form stauts.");
			throw new RuntimeException();
		}

		$this->root->redirect("Successfully updated form status.", 'form', null, array('id' => $this->formModel->get('id')));
	}

	protected function _defaultAction()
	{
		$this->_inputView();
	}

	protected function _validate()
	{
		if ( $this->formStatus->hasNext() === false ) {
			throw new RuntimeException();
		}

		if ( $this->post('nextStatus') != $this->formStatus->nextStatus ) {
			$this->errors[] = t("Unexpected screen transition.");
			throw new RuntimeException();
		}
	}

	protected function _inputView()
	{
		$this->template = 'pen:mailform.form_status_edit.input.tpl';
		$this->output['errors'] = $this->errors;
		$this->_view();
	}

	protected function _confirmView()
	{
		$this->template = 'pen:mailform.form_status_edit.confirm.tpl';
		$this->_view();
	}
	
	protected function _view()
	{
		$this->output['formModel'] = $this->formModel;
		$this->output['formStatus'] = $this->formStatus;
		parent::_view();
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
