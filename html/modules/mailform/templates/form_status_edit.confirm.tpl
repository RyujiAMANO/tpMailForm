<h1><{$page_title}></h1>

<p><{$formStatus->confirmMessage}></p>

<{if $formStatus->warningMessages}>
<ul class="warning">
	<{foreach from=$formStatus->warningMessages item="message"}>
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
		<td class="head"><{"Open Status"|t}></td>
		<td class="<{cycle values="odd,even"}>"><{$formModel->getStatusAsString()|escape}> =&gt; <{$formStatus->nextStatusLabel}></td>
	</tr>
</table>

<div style="text-align: center; padding: 10px;">
	<form action="" method="post">
		<input type="hidden" name="nextStatus" value="<{$formStatus->nextStatus}>" />
		<input type="submit" name="cancel" value="<{"Cancel"|t}>" />
		<input type="submit" name="save" value="<{$formStatus->submitButtonLabel}>" />
	</form>
</div>