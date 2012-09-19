<?php

class Mailform_Controller_EntryView extends Mailform_Abstract_Controller
{
	protected $id = null;

	/** @var Mailform_Model_Form */
	protected $formModel = null;
	/** @var Mailform_Model_Field[] */
	protected $fieldModels = array();
	/** @var Mailform_Model_Entry[] */
	protected $entryModels = array();

	protected function _defaultAction()
	{
		$this->_setUpId();
		$this->_setUpFormModel();
		$this->_checkPermission();
		$this->_setUpFieldModels();
		$this->_setUpEntryModel();
		$this->_view();
	}

	protected function _checkPermission()
	{
		if ( $this->root->cms->isAdmin() === false and $this->formModel->isOpened() === false ) {
			$this->root->redirect(t("Page not found."), 'form_list');
		}
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
		/** @var Mailform_Model_FormHandler $formHandler */
		$formHandler = $this->root->getModelHandler('Form');
		/** @var Mailform_Model_Form $formModel */
		$formModel = $formHandler->load($this->id);

		if ( is_object($formModel) === false or $formModel->isNew() === true ) {
			$this->root->redirect("no contents here", $this->root->cms->url);
		}

		$this->formModel = $formModel;
	}

	protected function _setUpFieldModels()
	{
		$fieldModels = $this->formModel->getFieldsWithNameAsKey();

		if ( is_array($fieldModels) === false or count($fieldModels) === 0 ) {
			$this->root->redirect("no contents here", $this->root->cms->url);
		}

		$this->fieldModels = $fieldModels;
	}

	protected function _setUpEntryModel()
	{
		$entryHandler = $this->formModel->getEntryHandler();
		$entryModels = $entryHandler->findByCreatorId($this->root->cms->getUserId());
		$this->entryModels = $entryModels;
	}

	protected function _view()
	{
		$this->pageTitle = t("Your Entry for {1}", $this->formModel->get('title'));
		$this->output['formModel']   = $this->formModel;
		$this->output['fieldModels'] = $this->fieldModels;
		$this->output['entryModels'] = $this->entryModels;
		$this->output['hadEntry'] = ( count($this->entryModels) > 0 );
		parent::_view();
	}
}
