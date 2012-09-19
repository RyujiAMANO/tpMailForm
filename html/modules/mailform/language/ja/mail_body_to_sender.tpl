<{$sender_name}> 様

<{$site_name}>の<{$form_title}>より以下の内容でお問い合わせいただきました。

--------------------------------------------------
<{foreach from=$fields item="field"}>
<{$field.label}>: <{$field.value}>
<{/foreach}>
--------------------------------------------------

お心当たりのない場合は、破棄をお願いします。

<{$site_name}>
