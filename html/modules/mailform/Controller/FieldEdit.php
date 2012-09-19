<?php

class Mailform_Controller_FieldEdit extends Mailform_Abstract_Controller
{
	protected $useModels = array('Form', 'Field');

	protected $formId = 0;

	/** @var Mailform_Model_FormHandler */
	protected $formHandler = null;
	/** @var Mailform_Model_Form */
	protected $formModel = null;

	/** @var Mailform_Model_FieldHandler */
	protected $fieldHandler = null;
	protected $fieldModels = array();

	/** @var Mailform_Model_EntryHandler */
	protected $entryHandler = null;

	protected $input = array();
	protected $errors = array();

	protected $entryTableCreateTableSyntax = '';

	public function main()
	{
		$this->_checkPermission();
		$this->_fetchFormId();
		$this->_setUpFormModel();
		$this->_setUpFieldMdoels();
		$this->_setUpEntryHandler();
		$this->_setUpInput();
		$this->_setUpPageTitle();
		$this->_checkEntryTableEditable();

		if ( $this->post('save') ) {
			$this->_saveAction();
		}
		
		$this->_adminTaskBar();
		$this->_defaultAction();
	}

	protected function _checkPermission()
	{
		if ( $this->root->cms->isAdmin() === false ) {
			$this->root->redirect("Permission denied.", $this->root->cms->url);
		}
	}

	protected function _fetchFormId()
	{
		$this->formId = $this->get('id');
	}

	protected function _setUpFormModel()
	{
		$this->formModel = $this->formHandler->load($this->formId);

		if ( is_object($this->formModel) === false or $this->formModel->isNew() === true ) {
			$this->root->redirect("Page not found.", $this->root->cms->url);
		}

		if ( $this->formModel->isPrivate() === false ) {
			$this->_redirectToFormList("Opend form is not able to change screen setting.");
		}
	}

	protected function _setUpFieldMdoels()
	{
		$this->fieldModels = $this->fieldHandler->findByFormId($this->formId);
	}

	/**
	 * エントリーハンドラーをセットアップする
	 */
	protected function _setUpEntryHandler()
	{
		$this->entryHandler = $this->formModel->getEntryHandler();
	}

	protected function _setUpInput()
	{
		$this->input = array(
			'title' => $this->formModel->get('title'),
			'header_description' => $this->formModel->get('header_description'),
			'fields' => array(),
		);

		/** @var Mailform_Model_Field $fieldModel */
		foreach ( $this->fieldModels as $fieldModel ) {
			$this->input['fields'][] = array(
				'id'          => $fieldModel->get('id'),
				'label'       => $fieldModel->get('label'),
				'type'        => $fieldModel->get('type'),
				'required'    => $fieldModel->get('required'),
				'description' => $fieldModel->get('description'),
				'options'     => $fieldModel->get('options'),
			);
		}
	}

	/**
	 * ページタイトルをセットアップする
	 */
	protected function _setUpPageTitle()
	{
		$this->pageTitle = t("Screen Preference");
	}

	/**
	 * エントリーテーブルが変更可能かをチェックする
	 */
	protected function _checkEntryTableEditable()
	{
		/*
		 * エントリーテーブルを変更するとき
		 * DROP TABLE して CREATE TABLE しなおすので
		 * データが既に登録されている場合は
		 * 画面設定の変更を許可しない。
		 *
		 * 基本的に、受付開始後は画面設定の変更ができないはずだが、
		 * なんらかのデータ不整合で、万が一画面設定が行えてしまったときに、
		 * 大切なエントリーデータを失わないようにするために、
		 * 冗長的にこのチェックを設けている。
		 */

		if ( $this->entryHandler->isTableChangable() === false ) {
			$this->_redirectToFormList("Can not change screen setting, as entry data already exist.");
		}
	}

	protected function _defaultAction()
	{
		$this->output['input'] = $this->input;
		$this->output['errors'] = $this->errors;
		$this->output['formData'] = json_encode($this->input);
		$this->output['pluginInfo'] = $this->_getPluginInfo();
		$this->_view();
	}

	protected function _saveAction()
	{
		$this->_fetchInput();
		$this->_validate();

		if ( count($this->errors) == 0 ) {
			if ( $this->_save() === true ) {
				$url = $this->url.'/index.php?id='.$this->formId;
				$this->root->redirect("Successfully saved.", $url);
			}
		}
	}

	protected function _fetchInput()
	{
		$input = array();
		$input['title'] = $this->post('title');
		$input['header_description'] = $this->post('header_description');
		$input['fields'] = $this->_fetchInputFields();
		$this->input = $input;
	}

	protected function _fetchInputFields()
	{
		$defaultField = array(
			'id'       => 0,
			'label'    => '',
			'type'     => '',
			'required' => 0,
			'options'  => array(),
		);

		$fields = $this->post('fields');

		if ( is_array($fields) === false ) {
			return array();
		}

		if ( count($fields) === 0 ) {
			return array();
		}

		foreach ( $fields as $key => $field ) {
			$fields[$key] = array_merge($defaultField, $field);
		}

		return $fields;
	}

	protected function _validate()
	{
		$this->_validateTitle();
		$this->_validateEmailFieldAndNameField();
		$this->_validateFields();
	}

	/**
	 * フォームタイトルのバリデーション
	 */
	protected function _validateTitle()
	{
		if ( $this->input['title'] == '' ) {
			$this->errors[] = t("Please enter {1}.", t("Form Title"));
		}
	}

	/**
	 * 名前フィールドのバリデーション
	 */
	protected function _validateEmailFieldAndNameField()
	{
		$nameFieldCount  = $this->_countNameField();
		$emailFieldCount = $this->_countEmailField();

		// 「送信者に控えメールを送る」設定がONの場合、
		// メールアドレスフィールドと送信者名フィールドが必要なので、
		// このふたつのフィールドがあるかチェックする
		if ( $this->formModel->mailsToSender() === true ) {
			if ( $nameFieldCount < 1 ) {
				$this->errors[] = t("Please place one Sender Name field.");
			}

			if ( $emailFieldCount < 1 ) {
				$this->errors[] = t("Please place one Email field.");
			}

			if ( $nameFieldCount < 1 or $emailFieldCount < 1 ) {
				$this->errors[] = t("If you want to remove Email or Sender Name field, pleace turn off 'Mail to Sender' setting at Form Preference.");
			}
		}

		// 送信者名フィールドが多すぎる場合
		if ( $nameFieldCount > 1 ) {
			$this->errors[] = t("Sender Name field must be one. {1} fields were placed.", $nameFieldCount);
		}

		// メールアドレスフィールドが多すぎる場合
		if ( $emailFieldCount > 1 ) {
			$this->errors[] = t("Email field must be one. {1} fields were placed.", $emailFieldCount);
		}
	}

	/**
	 * @return int
	 */
	protected function _countNameField()
	{
		$count = 0;

		foreach ( $this->input['fields'] as $field ) {
			if ( $field['type'] === 'Name' ) {
				$count += 1;
			}
		}

		return $count;
	}

	/**
	 * @return int
	 */
	protected function _countEmailField()
	{
		$count = 0;

		foreach ( $this->input['fields'] as $field ) {
			if ( $field['type'] === 'Email' ) {
				$count += 1;
			}
		}

		return $count;
	}

	protected function _validateFields()
	{
		if ( count($this->input['fields']) === 0 ) {
			$this->errors[] = t("Please create at least one field.");
			return;
		}

		$pluginManager = Mailform_Plugin_Manager::getInstance();

		$row = 1;

		foreach ( $this->input['fields'] as $field ) {

			// 空行は飛ばす
			if ( $field['label'] == '' and $field['type'] == '' and $field['description'] == '' ) {
				continue;
			}

			// 各行の呼び名を決める
			if ( $field['label'] == '' ) {
				$name = t("Row {1}", $row);
			} else {
				$name = $field['label'];
			}

			// 入力欄名のバリエーション
			if ( $field['label'] == '' ) {
				$this->errors[] = $name .': '. t("Please enter {1}.", t("Label"));
			}

			// 入力欄有無のバリエーション
			if ( $field['type'] == '' ) {
				$this->errors[] = $name .': '. t("Please place a field.");
			} else {
				$plugin = $pluginManager->getPlugin($field['type']);
				
				if ( $plugin == false ) {
					$this->errors[] = $name.': '.t("Unexpected field.");
				} else {
					// プラグイン固有のバリエーション
					$errors = $plugin->validatePluginOptions($field['options']);

					foreach ( $errors as $error ) {
						$this->errors[] = $name.': '.$error;
					}
				}
			}

			// 必須フラグのチェック: メールと宛名は絶対必須でないといけない
			if ( $field['type'] === 'Email' or $field['type'] === 'Name' ) {
				if ( $field['required'] == 0 ) {
					$this->errors[] = $name .': '.t("Requiring setting must be on.");
				}
			}

			$row += 1;
		}
	}

	protected function _save()
	{
		/** @var XoopsMySQLDatabase $db */
		$db = $this->root->cms->database();

		try {
			$db->query('BEGIN');
			$this->_saveFormData();
			$this->_deleteFields();
			$this->_saveFields();
			$this->_notifyUpdateToSiteNavi();
			$this->_changeEntryTable();
			$db->query('COMMIT');
		} catch ( Exception $e ) {
			$db->query('ROLLBACK');
			$this->_restoreEntryTable();
			$this->errors[] = $e->getMessage();
			return false;
		}

		return true;
	}

	protected function _saveFormData()
	{
		$formData = $this->input;
		unset($formData['fields']);
		$this->formModel->setVars($formData);

		$saved = $this->formHandler->save($this->formModel);

		if ( $saved === false ) {
			throw new Exception(t("Failed to save form data."));
		}
	}

	/**
	 * 取り除かれたフィールドを削除する
	 */
	protected function _deleteFields()
	{
		$fieldsData = $this->input['fields'];

		$newIds = array();
		$oldIds = array();

		foreach ( $fieldsData as $fieldData ) {
			$newIds[] = $fieldData['id'];
		}

		/** @var Mailform_Model_Field $fieldModel */
		foreach ( $this->fieldModels as $fieldModel ) {
			$oldIds[] = $fieldModel->get('id');
		}

		$deleteIds = array_diff($oldIds, $newIds);

		$deleted = $this->fieldHandler->deleteAllByIds($deleteIds);

		if ( $deleted === false ) {
			throw new Exception(t("Failed to delete old fields."));
		}
	}

	protected function _saveFields()
	{
		$fieldsData = $this->input['fields'];
		$weight = 1;

		foreach ( $fieldsData as $fieldData ) {

			if ( $fieldData['label'] == '' and $fieldData['type'] == '' and $fieldData['description'] == '' ) {
				continue;
			}

			$id = $fieldData['id'];
			unset($fieldData['id']);
			
			if ( $id == 0 ) {
				// 新規作成
				/** @var Mailform_Model_Field $fieldModel */
				$fieldModel = $this->fieldHandler->create();
				$fieldModel->set('form_id', $this->formId);
			} else {
				// 更新
				if ( isset($this->fieldModels[$id]) === false ) {
					throw new Exception(t("Field data not found: id: {1}", $id));
				}
				
				$fieldModel = $this->fieldModels[$id];
			}
			
			$fieldModel->setVars($fieldData);
			$fieldModel->setVar('weight', $weight);

			$saved = $this->fieldHandler->save($fieldModel);

			if ( $saved === false ) {
				throw new Exception(t("Failed to save field data: id: {1}", $id));
			}

			$updated = $this->fieldHandler->autoUpdateName($fieldModel);
			
			if ( $updated === false ) {
				throw new Exception(t("Failed to update field name: id: {1}", $fieldModel->get('id')));
			}
			
			$weight += 1;
		}
	}

	protected function _getPluginInfo()
	{
		$pluginManager = Mailform_Plugin_Manager::getInstance();
		$pluginInfo = $pluginManager->getPluginInfo();
		$pluginInfo = json_encode($pluginInfo);
		return $pluginInfo;
	}

	protected function _notifyUpdateToSiteNavi()
	{
		// TODO >> この部分は モジュールメディエイター を作って移植する
		// refs #7403
		$pengin = Pengin::getInstance();
		$pengin->path(TP_MODULE_PATH.'/site_navi');
		
		if ( class_exists('SiteNavi_API_Page') === false ) {
			return true;
		}

		$contentId = sprintf('/mailform/%u/', $this->formModel->get('id'));
		$title     = $this->formModel->get('title');

		$page = new SiteNavi_API_Page();
		$isSuccess = $page->updateTitle($contentId, $title);
		return $isSuccess;
	}

	/**
	 * エントリーテーブルの構造を変更する
	 */
	protected function _changeEntryTable()
	{
		// テーブル構造のバックアップ
		$this->entryTableCreateTableSyntax = $this->entryHandler->getCreateTableSyntax($this->formId);

		// テーブルをDROPする
		if ( $this->entryHandler->dropTableIfExists($this->formId) === false ) {
			throw new RuntimeException(t("Failed to drop entry table."));
		}

		// テーブルをCREATEする
		if ( $this->entryHandler->createTable($this->formId) === false ) {
			throw new RuntimeException(t("Failed to create entry table."));
		}
	}

	/**
	 * エントリーテーブルのリストア
	 */
	protected function _restoreEntryTable()
	{
		if ( $this->entryTableCreateTableSyntax === '' ) {
			return;
		}

		$this->entryHandler->dropTableIfExists($this->formId);

		// CREATE TABLEが「暗黙のトランザクションコミット」を発生させるので、別コネクションでやる
		$connection = Mailform_Component_DatabaseFactory::getConnection();
		$connection->query($this->entryTableCreateTableSyntax);
	}
	
	/**
	 * NiceAdmin タスクバー 連携
	 */
	protected function _adminTaskBar()
	{
		$adminTaskBar = Mailform_Component_AdminTaskBar::getInstance();
		$adminTaskBar->show($this->formModel);
	}

	protected function _redirectToFormList($message = '')
	{
		if ( Mailform_Component_AdminTaskBar::isAvailable() === true ) {
			$this->root->redirect($message, 'form', null, array('id' => $this->formId));
		}

		$this->root->redirect($message, 'form_list');
	}
}
