<div class="mailform">
	<{if $formModel->isPrivate()}>
		<ul class="info">
			<li><{"This form is private status. Private form doesn't mail and save the entry."|t}></li>
		</ul>
	<{/if}>
	<h1><{$formModel->get('title')|escape}></h1>

	<div style="margin-top: 10px; margin-bottom: 10px;">
		<{if $formModel->get('finish_message')}>
			<p><{$formModel->get('finish_message')|escape|nl2br}></p>
		<{else}>
			<p><{"Thank you for the inquiry."|t}></p>
		<{/if}>
	</div>
</div>
