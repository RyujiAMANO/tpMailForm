<?php

class Mailform_Controller_FormCsvExport extends Mailform_Abstract_Controller
{
	/** @var Mailform_Model_Form */
	protected $formModel = null;
	protected $fieldModels = array();

	protected function _defaultAction()
	{
		$this->_checkPermsission();
		$this->_setUpFormModel();
		$this->_setUpFieldModels();
		$this->_exportCsv();
		die;
	}

	protected function _checkPermsission()
	{
		if ( $this->root->cms->isAdmin() === false ) {
			$this->root->redirect("Permission denied.");
		}
	}
	
	protected function _setUpFormModel()
	{
		$handler = $this->_getFormHandler();
		$this->formModel = $handler->load($this->_getFormId());

		if ( is_object($this->formModel) === false or $this->formModel->isNew() === true ) {
			$this->root->redirect("Page not found.");
		}
	}

	protected function _setUpFieldModels()
	{
		$this->fieldModels = $this->formModel->getFieldsWithNameAsKey();
	}

	protected function _exportCsv()
	{
		// ヘッダ行の準備
		$headers = array();
		$headers['id'] = t("Entry ID");
		$headers['creator'] = t("Sender Username");
		$headers['created'] = t("Sent Date Time");

		foreach ( $this->fieldModels as $fieldModel ) {
			/** @var Mailform_Model_Field $fieldModel */
			$headers = array_merge($headers, $fieldModel->getCsvHeader());
		}

		$csv = new Mailform_Component_AssocCsvExport($headers);
		$csv->fileName = sprintf('form_%u_%s.csv', $this->formModel->get('id'), date('Ymd_His'));
		$csv->clearObFilter();
		$csv->sendHeader(); // HTTP ヘッダ
		$csv->addHeaderRow(); // ヘッダ行出力

		$entryHandler = $this->formModel->getEntryHandler();
		$entryIterator = $entryHandler->findModelsAsIterator();

		// データ行
		foreach ( $entryIterator as $entryModel ) {

			/** @var Mailform_Model_Entry $entryModel */
			$values = $entryModel->getVars();
			$values['creator'] = $entryModel->getCreatorName();
			$values['created'] = date('Y-m-d H:i:s', $values['created']);

			$row = array();

			foreach ( $values as $name => $value ) {
				if ( isset($this->fieldModels[$name]) === false ) {
					// 入力欄が存在しない場合（fieldテーブルとentryテーブルカラムとのデータ不整合の可能性あり）
					$row[$name] = $value;
				} else {
					/** @var Mailform_Model_Field $fieldModel */
					$fieldModel = $this->fieldModels[$name];
					$row = array_merge($row, $fieldModel->getCsvValues($value));
				}
			}

			$csv->addRowAssoc($row);
		}
	}

	/**
	 * @return Mailform_Model_FormHandler
	 */
	protected function _getFormHandler()
	{
		return $this->root->getModelHandler('Form');
	}

	/**
	 * @return int
	 */
	protected function _getFormId()
	{
		return intval($this->get('id'));
	}
}
