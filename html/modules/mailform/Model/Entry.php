<?php

class Mailform_Model_Entry extends Pengin_Model_AbstractModel
{
	public function getCreatorName()
	{
		$userId = $this->get('creator_id');
		$pengin = Pengin::getInstance();
		return $pengin->cms->getUserName($userId);
	}
}
