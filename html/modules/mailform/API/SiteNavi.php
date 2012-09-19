<?php

class Mailform_API_SiteNavi implements SiteNavi_API_SiteNaviInterface
{
	const CONTENT_ID_FORMAT = '/mailform/%u/';

	/**
	 * コンテンツ種別名を返す
	 * @return string 種別名 [a-z0-9_]+
	 */
	public function getName()
	{
		return 'mailform';
	}

	/**
	 * コンテンツ種別タイトルを返す
	 * @return string
	 */
	public function getTitle()
	{
		return "メールフォーム";
	}

	/**
	 * データを作成する
	 * @param array $data データ
	 *              string $data['title'] ページタイトル
	 *              string $data['type'] コンテンツ種別名
	 * @return array $dataに以下の要素を追加したものを返す
	 *               string $data['url'] ページのURL
	 *               string $data['content_id'] コンテンツID文字列 /{種別名}/{連番ID}/など
	 *               データの作成に失敗した場合は false を返す
	 */
	public function create(array $data)
	{
		$pengin = Pengin::getInstance();
		$pengin->translator->useTranslation('mailform', $pengin->cms->langcode, 'translation');

		$creator = new Mailform_Component_FormPrototypeCreator();

		if ( $creator->create($data['title']) === false ) {
			return false;
		}

		$formId = $creator->getFormId();

		if ( $formId === false ) {
			return false;
		}

		$data['url'] = XOOPS_MODULE_URL.'/mailform/index.php?id='.$formId;
		$data['content_id'] = sprintf(self::CONTENT_ID_FORMAT, $formId);

		return $data;
	}

	/**
	 * データを削除する
	 * @param array $contentIds コンテンツID
	 * @return bool
	 */
	public function delete(array $contentIds)
	{
		$ids = array();

		foreach ( $contentIds as $contentId ) {
			if ( preg_match('#^/mailform/(?P<id>[0-9]+)/$#', $contentId, $matches) == 0 ) {
				continue;
			}

			$ids[] = $matches['id'];
		}

		$pengin  = Pengin::getInstance();

		$fieldHandler = $pengin->getModelHandler('Field', 'mailform');
		$criteria = new Pengin_Criteria();
		$criteria->add('form_id', 'IN', $ids);

		if ( $fieldHandler->deleteAll($criteria) == false ) {
			return false;
		}

		$formHandler = $pengin->getModelHandler('Form', 'mailform');
		$criteria = new Pengin_Criteria();
		$criteria->add('id', 'IN', $ids);

		if ( $formHandler->deleteAll($criteria) == false ) {
			return false;
		}

		return true;
	}

	/**
	 * URLからコンテンツIDを返す
	 * @param string $url
	 * @param array $parameters Query String
	 * @return string コンテンツID
	 */
	public function getContentId($url, array $parameters)
	{
		if ( array_key_exists('id', $parameters) === false ) {
			return false;
		}

		$contentId = sprintf(self::CONTENT_ID_FORMAT, $parameters['id']);
		return $contentId;
	}
}
