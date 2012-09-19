<?php

class Mailform_Form_FormEdit extends Pengin_Form
{
	/** @var Mailform_Model_Form */
	protected $formModel = null;

	/**
	 * @param Mailform_Model_Form $formModel
	 */
	public function __construct(Mailform_Model_Form $formModel)
	{
		$this->formModel = $formModel;
		parent::__construct();
	}

	public function setUpForm()
	{
		$this->title(t("Form Preference"));
	}

	/**
	 * プロパティをセットアップするテンプレートメソッド
	 * 
	 * @access public
	 * @return void
	 */
	public function setUpProperties()
	{
		$this->add('mail_to_sender', 'RadioYesNo')
			->required()
			->label("Mail to Sender");

		$this->add('mail_to_receiver', 'RadioYesNo')
			->required()
			->label("Mail to Receiver");

		$this->add('receiver_email', 'Textarea')
			->required()
			->label("Receiver Email")
			->description("You can specify emails multiply by email separated with comma.")
			->attr('cols', 50)
			->attr('rows', 3);

		$this->add('finish_message', 'Textarea')
			->required()
			->label("Finish Message")
			->attr('cols', 50)
			->attr('rows', 6);
	}

	public function validateReceiverEmail(Pengin_Form_Property $property)
	{
		$value  = $property->getValue();
		$emails = $this->_emailTextToArray($value);

		if ( mb_strlen($value) > 0 and count($emails) == 0 ) {
			$this->addError(t("Please enter {1}.", t("Receiver Email")));
		} 

		foreach ( $emails as $email ) {
			if ( Pengin_Validator::email($email) === false ) {
				$this->addError(t("{1} is Invalid Email format.", $email));
			}
		}
	}

	/**
	 * 「送信者に控えメールを送信する」のバリデーション
	 * @param Pengin_Form_Property $property
	 */
	public function validateMailToSender(Pengin_Form_Property $property)
	{
		$mailToSender = $property->getValue();

		// メール送信を利用する設定なのに、メールフィールドと名前フィールドがない場合
		if ( $mailToSender == 1 and $this->formModel->hasEmailFieldAndNameField() === false ) {
			$this->addError(t("Please place Email and Sender Name field at Screen Preference in order to turn on 'Mail to Sender'."));
		}
	}

	/**
	 * 入力値をモデルに反映する.
	 *
	 * @access public
	 * @param Mailform_Model_Form $model
	 * @return Pengin_Form
	 */
	public function updateModel(Mailform_Model_Form $model)
	{
		/** @var $property Pengin_Form_Property */
		foreach ( $this->properties as $property ) {
			$name  = $property->getName();
			$value = $property->exportValue();
			$model->setVar($name, $value);
		}

		$emails = $model->getReceiverEmails();
		$emails = $model->emailArrayToText($emails);
		$model->set('receiver_email', $emails);

		return $this;
	}

	/**
	 * @param string $email
	 * @return array
	 */
	protected function _emailTextToArray($email)
	{
		return Mailform_Model_Form::emailTextToArray($email);
	}
}
