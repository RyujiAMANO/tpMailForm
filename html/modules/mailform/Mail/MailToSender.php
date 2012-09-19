<?php

/**
 * 送信者のメール送信を担当するクラス
 */
class Mailform_Mail_MailToSender
{
	/** @var Mailform_Mail_MailData */
	protected $mailData = null;

	protected $sent = false;

	/**
	 * @param Mailform_Mail_MailData $mailData
	 */
	public function __construct(Mailform_Mail_MailData $mailData)
	{
		$this->mailData = $mailData;
	}

	/**
	 * @return bool
	 */
	public function send()
	{
		$data = $this->mailData->getData();

		if ( $data['sender_email'] === false ) {
			return true;
		}

		$pengin = Pengin::getInstance();
		$dir = $pengin->context->path;
		$langcode = $pengin->cms->langcode;
		$template = sprintf('%s/language/%s/mail_body_to_sender.tpl', $dir, $langcode);

		$mail = new Mailform_Mail_SmartyMail();
		$mail->setTemplate($template);
		$mail->setVars($data);
		$mail->setMailTo($data['sender_email']);
		$mail->setSubject($data['form_title']);
		$mail->setMailFrom($data['site_name'], $data['receiver_main_email']);

		$result = $mail->sendMail();

		if ( $result === true ) {
			$this->sent = true;
		}

		return $result;
	}

	public function sent()
	{
		return $this->sent;
	}
}
