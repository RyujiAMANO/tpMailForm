<?php

class Mailform_Plugin_Core_Radio extends Pengin_Form_Property_Radio
                                 implements Mailform_Plugin_PluginInterface
{
	/**
	 * プラグインの表示名を返す.
	 * @static
	 * @return string
	 */
	public static function getPluginName()
	{
		return t("Radio");
	}

	/**
	 * モックHTMLを返す
	 * @return string
	 */
	public static function getMockHTML()
	{
		return '<span style="white-space:nowrap"><input type="radio" checked="checked" /> A <input type="radio" /> B <input type="radio" /> C</span>';
	}

	/**
	 * entryテーブル用のカラム定義を返す
	 * @return string
	 */
	public static function getColumnDefinition()
	{
		return "int(11) unsigned NOT NULL DEFAULT '0'";
	}

	/**
	 * 選択肢を翻訳して返す.
	 * @return array 選択肢
	 */
	public function getOptionsLocal()
	{
		$options = array();

		// 選択肢はユーザが決めるので、翻訳しない
		foreach ( $this->options as $key => $value ) {
			$options[$key] = $value;
		}

		return $options;
	}

	/**
	 * オプションのデフォルト値を返す
	 * @return array
	 */
	public function getDefaultPluginOptions()
	{
		return array(
			'value' => '',
			'options' => '',
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
				<td class="head"><?php echo t("Options") ?></td>
				<td class="odd">
					<textarea name="options" cols="40" rows="10"><?php echo $params['options'] ?></textarea>
					<div><?php echo t("You can specify options multiply by option separated with line break.") ?></div>
				</td>
			</tr>
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
	 * @param array $params [options => string, value => string] パラメータ
	 * @return array エラー文言の配列。エラーがない場合は空の配列を返す。
	 *
	 * editPluginOptions()のフォームで入力された値に対してのバリデーションを行います。
	 */
	public function validatePluginOptions(array $params)
	{
		$errors = array();

		$options = $this->optionTextToArray($params['options']);
		$value   = $params['value'];

		// 必須チェック
		if ( count($options) === 0 ) {
			$errors[] = t("Please enter {1}.", t("Options"));
		}

		if ( trim($value) !== '' ) {
			// オプションに無い項目がデフォルト値になっているかチェックする
			if ( in_array($value, $options) === false ) {
				$errors[] = t("These default value is not in the options: {1}", $value);
			}
		}

		return $errors;
	}

	/**
	 * オプションを反映する。
	 * @param array $options オプション
	 * @return void
	 * @note Mailform_Form_Form::setUpProperties()で使う
	 */
	public function applyPluginOptions(array $options)
	{
		$selections = $options['options']; // 選択肢
		$selections = $this->optionTextToArray($selections);
		$this->options($selections);

		$value = $options['value']; // デフォルト値

		if ( in_array($value, $selections) === true ) {
			$key = array_search($value, $selections);
			$this->value($key);
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
		return Mailform_Plugin_Helper::textToArray($optionText);
	}

	/**
	 * 値を文字列に変換する
	 * @param int $value
	 * @param array $options getDefaultPluginOptions()で定義した配列を受け取る
	 * @return string
	 */
	public static function valueToString($value, array $options = array())
	{
		$selections = $options['options']; // 選択肢
		$selections = self::optionTextToArray($selections);

		if ( array_key_exists($value, $selections) === true ) {
			$value = $selections[$value];
		}

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
		$selections = $options['options']; // 選択肢
		$selections = self::optionTextToArray($selections);
		$values = array();

		foreach ( $selections as $selectionKey => $selectionLabel ) {
			if ( $value == $selectionKey ) {
				$values[$selectionKey] = 1;
			} else {
				$values[$selectionKey] = 0;
			}
		}

		return $values;
	}

	/**
	 * HTMLの設定情報を返す.
	 *
	 * @access public
	 * @return array key-value形式の設定情報
	 */
	public function getHtmlParameters()
	{
		$values = array(
			'type'      => 'radio',
			'name'      => $this->name,
			'checked'   => $this->value,
			'options'   => $this->getOptionsLocal(),
			'separator' => '<br />',
		);

		$values = array_merge($values, $this->attributes);

		return $values;
	}
}
