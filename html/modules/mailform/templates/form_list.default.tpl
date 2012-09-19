<h1><{$page_title}></h1>

<{if $isAdmin}>
	<div style="text-align: right;">
		<{if $isOperating}>
			<a href="<{url controller="form_list" preview="1"}>"><{"Preview List Mode"|t}></a> |
			<a href="<{url controller="form_create"}>"><{"Create A New Form"|t}></a>
		<{else}>
			<a href="<{url controller="form_list"}>"><{"Back to Admin Mode"|t}></a>
		<{/if}>
	</div>
<{/if}>

<table class="outer">
	<th><{"ID"|t}></th>
	<th><{"Form Title"|t}></th>
	<th><{"Open Status"|t}></th>
	<{if $isOperating == false}>
		<th><{* view my entry column. *}></th>
	<{/if}>
	<{if $isOperating}>
		<th></th>
	<{/if}>

	<{foreach from=$forms item="form"}>
		<{assign var="formId" value=$form->get('id')}>
		<tr class="<{cycle values="odd,even"}>">
			<td><{$formId|escape}></td>
			<td>
				<{if $form->isOpened() or $isOperating}>
					<a href="<{url controller="form" id=$formId}>"><{$form->get('title')|escape}></a>
				<{else}>
					<{$form->get('title')|escape}>
				<{/if}>
			</td>
			<td>
				<{if $isOperating}>
					<a href="<{url controller="form_status_edit" id=$formId}>"><{$form->getStatusAsString()|escape}></a>
				<{else}>
					<{$form->getStatusAsString()|escape}>
				<{/if}>
			</td>
			<{if $isOperating == false}>
				<td>
					<a href="<{url controller="entry_view" id=$formId}>"><{"View my entry"|t}></a>
				</td>
			<{/if}>
			<{if $isOperating}>
				<td>
					<a href="<{url controller="form_edit" id=$formId}>"><{"Form Preference"|t}></a>
					<a href="<{url controller="field_edit" id=$formId}>"><{"Screen Preference"|t}></a>
					<a href="<{url controller="form_csv_export" id=$formId}>"><{"CSV Export"|t}></a>
				</td>
			<{/if}>
		</tr>
	<{/foreach}>
</table>

<{include file="pen:`$dirname`._pager.tpl"}>
