<?php

class Mailform_Form_FormCreate extends Pengin_Form
{
	public function setUpForm()
	{
		$this->title(t("Create A New Form"));
	}

	/**
	 * プロパティをセットアップするテンプレートメソッド
	 *
	 * @access public
	 * @return void
	 */
	public function setUpProperties()
	{
		$this->add('title', 'Text')
			->required()
			->label("Form Title");
	}
}
