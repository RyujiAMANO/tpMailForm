<?php

class Mailform_Mail_SmartyMail extends Pengin_Mail
{
	protected $template = '';
	protected $variables = array();
	protected $errors = array();

	/**
	 * テンプレートをセットする
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}

	/**
	 * 変数をセットする
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value)
	{
		$this->variables[$name] = $value;
	}

	/**
	 * 変数を一括セットする
	 * @param array $variables
	 */
	public function setVars(array $variables)
	{
		$this->variables = array_merge($this->variables, $variables);
	}

	/**
	 * 本文の描画結果を返す
	 * @return bool|string
	 */
	public function render()
	{
		$smarty = new XoopsTpl();

		if ( $smarty->template_exists($this->template) === false ) {
			$this->errors[] = t("Template not found: {1}", $this->template);
			return false;
		}

		$smarty->assign($this->variables);
		return $smarty->fetch($this->template);
	}

	/**
	 * メールを送信する
	 * @return bool
	 */
	public function sendMail()
	{
		$content = $this->render();

		if ( $content === false ) {
			return false;
		}

		$sent = @mb_send_mail($this->mailTo, $this->subject, $content, $this->_getHeader(), $this->parameter);

		if ( $sent == false ) {
			$this->errors[] = t("Failed to send mail.");
			return false;
		}

		return true;
	}

	/**
	 * エラーメッセージを返す
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}
}
