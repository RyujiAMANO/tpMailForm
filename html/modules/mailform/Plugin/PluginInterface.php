<?php

interface Mailform_Plugin_PluginInterface
{
	/**
	 * プラグインの表示名を返す.
	 * @static
	 * @return string
	 *
	 * 用途: ここで設定したプラグイン表示名は、画面設定ページのパレットに現れます
	 */
	public static function getPluginName();

	/**
	 * モックHTMLを返す
	 * @static
	 * @return string
	 *
	 * 用途: ここで設定したHTMLは画面設定ページのパレットに現れます
	 */
	public static function getMockHTML();

	/**
	 * entryテーブル用のカラム定義を返す
	 * @static
	 * @return string
	 *
	 * 用途: entryテーブルをCREATEするときに参照されます
	 */
	public static function getColumnDefinition();

	/**
	 * オプションのデフォルト値を返す
	 * @return array
	 *
	 * 用途:
	 */
	public function getDefaultPluginOptions();

	/**
	 * オプション設定画面のHTMLを出力する
	 * @param array $params パラメータ
	 * @return void
	 *
	 * 用途:
	 */
	public function editPluginOptions(array $params);

	/**
	 * オプションをバリデーションする
	 * @param array $params パラメータ
	 * @return array エラー文言の配列。エラーがない場合は空の配列を返す。
	 *
	 * 用途:
	 */
	public function validatePluginOptions(array $params);

	/**
	 * オプションを反映する
	 * @param array $options オプション
	 * @return void
	 *
	 * 用途: Mailform_Form_Confirm::setUpProperties()で使う
	 */
	public function applyPluginOptions(array $options);

	/**
	 * 入力値をエントリーモデルに反映する
	 * @param Mailform_Model_Entry $entryModel
	 *
	 * 用途:
	 */
	public function updateEntryModel(Mailform_Model_Entry $entryModel);

	/**
	 * 選択肢を配列にして返す
	 * @static
	 * @param string $optionText
	 * @return array 選択肢がない場合は array() を返す
	 *
	 * 用途:
	 */
	public static function optionTextToArray($optionText);

	/**
	 * 値を文字列に変換する
	 * @static
	 * @param mixed $value
	 * @param array $options getDefaultPluginOptions()で定義した配列を受け取る
	 * @return string
	 *
	 * 用途: メールの文面で使われる
	 */
	public static function valueToString($value, array $options = array());

	/**
	 * 値を配列に変換する
	 * @static
	 * @param mixed $value
	 * @param array $options getDefaultPluginOptions()で定義した配列を受け取る
	 * @return array
	 *
	 * 用途: CSV出力で使われる
	 *
	 * ------------------------------------------------------------
	 * 戻り値の仕様
	 * ------------------------------------------------------------
	 *
	 * 非選択型の場合: Email, Text, Textarea など
	 *
	 * - 値を含む長さ1の配列を返す。
	 * - 添字はどのような値でも構わない。
	 *
	 * 例:
	 *
	 *   array('alice@example.com') // Email
	 *
	 * ------------------------------------------------------------
	 *
	 * 選択型の場合: Checkbox, Radio, Selectなど
	 *
	 * - 選択肢に対し 0 or 1 のフラグを付ける形で全ての選択肢を返す。
	 *
	 *   0: 選択されていないもの
	 *   1: 選択されたもの
	 *
	 * - 添字は、選択肢の添字と同じ値にする。
	 *
	 * 例:
	 *
	 *   array(1 => 'うどん', 2 => 'カレー', 4 => 'ビビンバ', 8 => '牛丼');
	 *   という選択肢に対して、 「カレー」と「ビビンバ」が選択された状態は、
	 *   array(1 => 0, 2 => 1, 4 => 1, 8 => 0);
	 *   のように表現する。
	 *
	 * ------------------------------------------------------------
	 */
	public static function valueToArray($value, array $options = array());
}
