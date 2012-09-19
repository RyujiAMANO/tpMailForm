<?php

class Mailform_Plugin_Core_Name extends Pengin_Form_Property_Text
                                implements Mailform_Plugin_PluginInterface
{
	const USER_NAME = 2;
	const USER_NICK_NAME = 1;

	/**
	 * プラグインの表示名を返す.
	 * @static
	 * @return string
	 */
	public static function getPluginName()
	{
		return t("Sender Name");
	}

	/**
	 * モックHTMLを返す
	 * @return string
	 */
	public static function getMockHTML()
	{
		return '<input type="text" value="Sender Name" />';
	}

	/**
	 * entryテーブル用のカラム定義を返す
	 * @return string
	 */
	public static function getColumnDefinition()
	{
		return "varchar(255) NOT NULL DEFAULT ''";
	}

	/**
	 * オプションのデフォルト値を返す
	 * @return array
	 */
	public function getDefaultPluginOptions()
	{
		return array(
			'value' => '',
		);
	}

	/**
	 * オプション設定画面のHTMLを出力する
	 * @param array $params パラメータ
	 * @return void
	 */
	public function editPluginOptions(array $params)
	{
		$useUserName     = '';
		$useUserNickName = '';
		$notUseUserName  = '';

		switch ( @$params['use_user_info'] ) { // 下位互換: Noticeエラー防止のために @ を使っている
			case self::USER_NAME:
				$useUserName = 'checked="checked" ';
				break;
			case self::USER_NICK_NAME:
				$useUserNickName = 'checked="checked" ';
				break;
			default:
				$notUseUserName = 'checked="checked" ';
		}


		?>
		<table class="outer">
			<tr>
				<td class="head"><?php echo t("Default Value") ?></td>
				<td class="odd">
					<input type="text" name="value" value="<?php echo $params['value'] ?>" />
				</td>
			</tr>
			<tr>
				<td class="head"><?php echo t("Set User Name") ?></td>
				<td class="odd">
					<input type="radio" name="use_user_info" value="2"<?php echo $useUserName ?> /><?php echo t("Use user name") ?>&nbsp;
					<input type="radio" name="use_user_info" value="1"<?php echo $useUserNickName ?> /><?php echo t("Use user nick name") ?>&nbsp;
					<input type="radio" name="use_user_info" value="0"<?php echo $notUseUserName ?> /><?php echo t("Not use user info") ?>
					<div><small><?php echo t("If set this option, user information will be used as default value.") ?></small></div>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * オプションをバリデーションする
	 * @param array $params パラメータ
	 * @return array エラー文言の配列。エラーがない場合は空の配列を返す。
	 */
	public function validatePluginOptions(array $params)
	{
		return array();
	}

	/**
	 * オプションを反映する。
	 * @param array $options オプション
	 * @return void
	 * @note Mailform_Form_Form::setUpProperties()で使う
	 */
	public function applyPluginOptions(array $options)
	{
		switch ( @$options['use_user_info'] ) { // 下位互換: Noticeエラー防止のために @ を使っている
			case self::USER_NAME:
				$columnName = 'name';
				break;
			case self::USER_NICK_NAME:
				$columnName = 'uname';
				break;
			default:
				return; // 処理を中断する
		}

		// もしユーザなら、ユーザ情報のメールアドレスをデフォルト値にする
		$root = XCube_Root::getSingleton();

		if ( is_object($root->mContext->mXoopsUser) === true ) {
			$this->value($root->mContext->mXoopsUser->get($columnName));
		}
	}

	/**
	 * 入力値をエントリーモデルに反映する
	 * @param Mailform_Model_Entry $entryModel
	 */
	public function updateEntryModel(Mailform_Model_Entry $entryModel)
	{
		$entryModel->set($this->name, $this->value);
	}

	/**
	 * 選択肢を配列にして返す
	 * @param string $optionText
	 * @return array
	 */
	public static function optionTextToArray($optionText)
	{
		return array(); // 選択肢はないので空配列を返す
	}

	/**
	 * 値を文字列に変換する
	 * @param int $value
	 * @param array $options getDefaultPluginOptions()で定義した配列を受け取る
	 * @return string
	 */
	public static function valueToString($value, array $options = array())
	{
		return $value;
	}

	/**
	 * 値を配列に変換する
	 * @static
	 * @param mixed $value
	 * @param array $options getDefaultPluginOptions()で定義した配列を受け取る
	 * @return array
	 */
	public static function valueToArray($value, array $options = array())
	{
		return array($value);
	}
}
