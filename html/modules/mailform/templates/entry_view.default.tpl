<div class="mailform">
	<h1><{$page_title}></h1>
	<{if $hadEntry}>
		<p><{"You have sent following entries for {1}."|t:$formModel->get('title')|escape}></p>
	<{else}>
		<p><{"You have no entries for {1} yet."|t:$formModel->get('title')|escape}></p>
	<{/if}>
	<{foreach from=$entryModels item="entry"}>
		<table class="outer" style="margin: 20px 0;">
			<tr>
				<th colspan="2"><{"Entry Contents"|t}> #<{$entry->get('id')}></th>
			</tr>
			<{foreach from=$fieldModels item="field"}>
				<tr>
					<td class="head" style="width: 30%;"><{$field->get('label')|escape}></td>
					<td class="<{cycle values="odd,even"}>"><{$field->valueToString($entry)|escape|nl2br}></td>
				</tr>
			<{/foreach}>
			<tr>
				<td class="head"><{"Entry Date"|t}></td>
				<td class="<{cycle values="odd,even"}>"><{"Y-m-d H:i:s"|date:$entry->get('created')}></td>
			</tr>
		</table>
	<{/foreach}>
</div>
