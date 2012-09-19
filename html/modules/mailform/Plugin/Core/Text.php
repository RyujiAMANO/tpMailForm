<?php

class Mailform_Plugin_Core_Text extends Pengin_Form_Property_Text
                                implements Mailform_Plugin_PluginInterface
{
	/**
	 * プラグインの表示名を返す.
	 * @static
	 * @return string
	 */
	public static function getPluginName()
	{
		return t("Textbox");
	}

	/**
	 * モックHTMLを返す
	 * @return string
	 */
	public static function getMockHTML()
	{
		return '<input type="text" />';
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
		?>
		<table class="outer">
			<tr>
				<td class="head"><?php echo t("Default Value") ?></td>
				<td class="odd">
					<input type="text" name="value" value="<?php echo $params['value'] ?>" />
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
		// Nothing to do.
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
