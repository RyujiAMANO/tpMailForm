<?php
class Mailform_Model_FormHandler extends Pengin_Model_AbstractHandler
{
	/**
	 * @param bool $isAdmin
	 * @param int $limit
	 * @param int $start
	 * @return array
	 */
	public function findForList($isAdmin, $limit, $start)
	{
		$criteria = $this->_getCriteriaForList($isAdmin);
		return $this->find($criteria, 'created', 'DESC', $limit, $start);
	}

	/**
	 * @param bool $isAdmin
	 * @return int
	 */
	public function countForList($isAdmin)
	{
		$criteria = $this->_getCriteriaForList($isAdmin);
		return $this->count($criteria);
	}

	/**
	 * @param bool $isAdmin
	 * @return Pengin_Criteria
	 */
	protected function _getCriteriaForList($isAdmin)
	{
		$criteria = new Pengin_Criteria();

		if ( $isAdmin === true ) {
			// 管理者は制限なし
		} else {
			$criteria->add('status', '>=', Mailform_Model_Form::STATUS_OPEN);
		}

		return $criteria;
	}
}
