<?php

class Mailform_Component_AdminTaskBar
{
	/** @var Mailform_Component_AdminTaskBar */
	protected static $instance = null;

	/** @var NiceAdmin_Core_AdminTaskBar */
	protected $adminTaskBar = null;

	protected $isShowen = false;

	protected $namespace = 'MailformAdmin';

	/**
	 * インスタンスを返す
	 * @static
	 * @return Mailform_Component_AdminTaskBar
	 */
	public static function getInstance()
	{
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * コンストラクタ
	 */
	protected function __construct()
	{
		if ( $this->isAvailable() === false ) {
			return;
		}

		$root = XCube_Root::getSingleton();
		$this->adminTaskBar = $root->mAdminTaskBar;
	}

	/**
	 * AdminTaskBarが利用可能かを返す
	 * @return bool
	 */
	public static function isAvailable()
	{
		$root = XCube_Root::getSingleton();

		if ( isset($root->mAdminTaskBar) === false or is_object($root->mAdminTaskBar) === false ) {
			return false;
		}

		return true;
	}

	/**
	 * タスクバーを表示する
	 * @param Mailform_Model_Form $formModel
	 */
	public function show(Mailform_Model_Form $formModel)
	{
		if ( $this->isShowen === true or $this->isAvailable() === false ) {
			return;
		}

		$formId = $formModel->get('id');

		$pengin = Pengin::getInstance();

		$this->adminTaskBar->addLink($this->namespace,  t('Mailform').'('. $formModel->getStatusAsString() .')', '' , 1);

		$url = $pengin->url('form', null, array('id' => $formId));
		$this->_addSubLink('Form', t("View Form"), $url);

		$url = $pengin->url('form_edit', null, array('id' => $formId));
		$this->_addSubLink('FormEdit', t("Form Preference"), $url);

		$url = $pengin->url('field_edit', null, array('id' => $formId));
		$this->_addSubLink('FieldEdit', t("Screen Preference"), $url);

		$url = $pengin->url('form_status_edit', null, array('id' => $formId));
		$this->_addSubLink('FormStatusEdit', t("Form Open Status Management").'('. $formModel->getStatusAsString() .')', $url);

		$url = $pengin->url('form_csv_export', null, array('id' => $formId));
		$this->_addSubLink('FormCsvExport', t("CSV Export"), $url);

		$this->isShowen = true;
	}

	protected function _addSubLink($uniqueName, $label, $url, $isModal = false)
	{
		$id = '';

		if ( $isModal === true ) {
			$id .= 'tpModal';
		} else {
			$id .= 'tpNoModal';
		}

		$id .= $this->namespace.$uniqueName;

		//　パラメータ1　モジュール名（1文字目大文字）＋Admin
		//　パラメータ2　表示方法＋サブのid。表示方法　tpModal：モーダルで出す。tpNoModal:メインに出す。
		//　パラメータ3　サブメニューに表示する名前
		//　パラメータ4　クリックしたときに表示するurl
		$this->adminTaskBar->addSubLink($this->namespace, $id, $label, $url);
	}
}
