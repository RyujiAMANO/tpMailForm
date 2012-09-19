<div class="mailform">
	<{if $formModel->isPrivate()}>
		<ul class="info">
			<li><{"This form is private status. Private form doesn't mail and save the entry."|t}></li>
		</ul>
	<{/if}>
	<h1><{$formModel->get('title')|escape}></h1>
	<{if $formModel->get('header_description')}>
		<div style="margin-top:10px; margin-bottom:10px;">
			<{$formModel->get('header_description')}>
		</div>
	<{/if}>
	<{if $form->hasError()}>
		<ul style="margin-bottom:10px;" class="error">
			<{foreach from=$form->getErrors() item="error"}>
				<li><{$error|escape}></li>
			<{/foreach}>
		</ul>
	<{/if}>
	<{form form=$form}>
		<table class="outer">
			<{foreach from=$form->getProperties() item="property"}>
				<{if $property->hasError()}>
					<{assign var="penFormError" value="penFormError"}>
				<{/if}>
				<tr>
					<td class="head"><{$property->getLabel()|escape}><{form_require property=$property sign="(required)"|t}>
					</td>
					<td class="<{cycle values="odd,even"}>">
						<div class="<{$penFormError}>">
							<{form_input property=$property}>
							<{if $property->getDescription()}>
								<div class="description"><{$property->getDescription()}></div>
							<{/if}>
						</div>
					</td>
				</tr>
			<{/foreach}>
		</table>
		<div style="text-align: center; padding: 10px;">
			<input type="submit" name="confirm" value="<{"Confirm"|t}>"/>
		</div>
	<{/form}>
</div>
