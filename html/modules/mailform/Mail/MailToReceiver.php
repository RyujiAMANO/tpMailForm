<?php

/**
 * 担当者へのメール送信を担当するクラス
 */
class Mailform_Mail_MailToReceiver
{
	/** @var Mailform_Mail_MailData */
	protected $mailData = null;

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

		$pengin = Pengin::getInstance();
		$dir = $pengin->context->path;
		$langcode = $pengin->cms->langcode;
		$template = sprintf('%s/language/%s/mail_body_to_receiver.tpl', $dir, $langcode);

		foreach ( $data['receiver_emails'] as $receiverEmail ) {
			$mail = new Mailform_Mail_SmartyMail();
			$mail->setTemplate($template);
			$mail->setVars($data);
			$mail->setMailTo($receiverEmail);
			$mail->setSubject($data['form_title']);
			$mail->setMailFrom($data['site_name'], $data['receiver_main_email']);
			$mail->sendMail();
		}

		return true;
	}
}
