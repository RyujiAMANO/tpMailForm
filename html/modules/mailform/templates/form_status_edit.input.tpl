<h1><{$page_title}></h1>

<{if $errors}>
<ul class="error">
	<{foreach from=$errors item="message"}>
	<li><{$message}></li>
	<{/foreach}>
</ul>
<{/if}>

<{if $formStatus->informationMessages}>
<ul class="info">
	<{foreach from=$formStatus->informationMessages item="message"}>
		<li><{$message}></li>
	<{/foreach}>
</ul>
<{/if}>

<table class="outer">
	<tr>
		<td class="head"><{"Form Title"|t}></td>
		<td class="<{cycle values="odd,even"}>"><{$formModel->get('title')|escape}></td>
	</tr>
	<tr>
		<td class="head"><{"Current Open Status"|t}></td>
		<td class="<{cycle values="odd,even"}>"><{$formModel->getStatusAsString()|escape}></td>
	</tr>
</table>

<{if $formStatus->hasNext()}>
	<div style="text-align: center; padding: 10px;">
		<form action="" method="post">
			<input type="hidden" name="nextStatus" value="<{$formStatus->nextStatus}>" />
			<input type="submit" name="confirm" value="<{$formStatus->submitButtonLabel}>" />
		</form>
	</div>
<{/if}>