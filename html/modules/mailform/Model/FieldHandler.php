<?php
class Mailform_Model_FieldHandler extends Pengin_Model_AbstractHandler
{
	const DEFAULT_NAME_FORMAT = 'field_%u';

	public function findByFormId($formId)
	{
		$criteria = new Pengin_Criteria();
		$criteria->add('form_id', $formId);
		return $this->find($criteria, 'weight', 'ASC', null, null, true);
	}

	/**
	 * @param int $formId
	 * @return array
	 */
	public function findByFormIdWithNameAsKey($formId)
	{
		$_models = $this->findByFormId($formId);

		$models = array();

		if ( is_array($_models) === false ) {
			return $models;
		}

		/** @var Mailform_Model_Field $model */
		foreach ( $_models as $model ) {
			$name = $model->get('name');
			$models[$name] = $model;
		}

		return $models;
	}

	public function deleteAllByIds(array $ids)
	{
		if ( is_array($ids) === false or count($ids) === 0 ) {
			return true;
		}

		$criteria = new Pengin_Criteria();
		$criteria->add('id', 'IN', $ids);
		return $this->deleteAll($criteria);
	}

	public function autoUpdateName(Mailform_Model_Field $model)
	{
		$id = $model->get('id');

		if ( $model->get('name') != '' or $id == 0 ) {
			return true;
		}

		$name = sprintf(self::DEFAULT_NAME_FORMAT, $id);
		$model->set('name', $name);
		return $this->save($model);
	}

	/**
	 * フォームにメールフィールドと名前フィールドが存在するかを返す
	 * @param int $formId
	 * @return bool
	 */
	public function existsEmailAndNameForForm($formId)
	{
		$criteria = new Pengin_Criteria();
		$criteria->add('form_id', $formId);
		$criteria->add('type', 'Email');
		$emailFieldCount = $this->count($criteria);

		if ( $emailFieldCount < 1 ) {
			return false;
		}

		$criteria = new Pengin_Criteria();
		$criteria->add('form_id', $formId);
		$criteria->add('type', 'Name');
		$nameFieldCount = $this->count($criteria);

		if ( $nameFieldCount < 1 ) {
			return false;
		}

		return true;
	}
}
