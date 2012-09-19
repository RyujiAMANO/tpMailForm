<?php

class Mailform_Controller_FormList extends Mailform_Abstract_Controller
{
	/** @var Mailform_Model_FormHandler */
	protected $formHandler = null;
	protected $formModels = array();

	/** @var Pengin_Pager */
	protected $pager = null;
	protected $start = 0;
	protected $limit = 24;
	protected $total = 0;

	protected $isPreview = false;
	protected $isAdmin = false;

	public function _defaultAction()
	{
		$this->_checkAdminTaskBar();
		$this->_setUpIsAdmin();
		$this->_setUpIsPreview();
		$this->_setUpStart();
		$this->_setUpFormHandler();
		$this->_setUpFormModels();
		$this->_setUpTotal();
		$this->_setUpPager();
		$this->_setUpPageTitle();
		$this->_view();
	}

	protected function _checkAdminTaskBar()
	{
		if ( Mailform_Component_AdminTaskBar::isAvailable() === true ) {
			$this->root->location($this->root->cms->url); // AdminTaskBarが使える環境ではこの画面を使わない
		}
	}

	protected function _setUpIsAdmin()
	{
		$this->isAdmin = $this->root->cms->isAdmin();
	}

	protected function _setUpIsPreview()
	{
		if ( $this->get('preview') == 1 and $this->isAdmin === true ) {
			$this->isPreview = true;
		}
	}

	protected function _setUpStart()
	{
		$this->start = intval($this->get('start'));
	}

	protected function _setUpFormHandler()
	{
		$this->formHandler = $this->root->getModelHandler('Form');
	}
	
	protected function _setUpPageTitle()
	{
		$this->pageTitle = t("Form List");
	}

	protected function _setUpFormModels()
	{
		$this->formModels = $this->formHandler->findForList($this->_isOperating(), $this->limit, $this->start);
	}

	protected function _setUpTotal()
	{
		$this->total = $this->formHandler->countForList($this->_isOperating());
	}

	protected function _setUpPager()
	{
		$this->pager = new Pengin_Pager(array(
			'current' => $this->start,
			'perPage' => $this->limit,
			'total'   => $this->total,
		));
	}

	protected function _view()
	{
		$this->output['forms'] = $this->formModels;
		$this->output['isAdmin'] = $this->isAdmin;
		$this->output['pages'] = $this->pager->getPages();
		$this->output['isPreview'] = $this->isPreview;
		$this->output['isOperating'] = $this->_isOperating();

		parent::_view();
	}

	protected function _isOperating()
	{
		if ( $this->isAdmin === false ) {
			return false;
		}

		if ( $this->isPreview === true ) {
			return false;
		}

		return true;
	}
}
