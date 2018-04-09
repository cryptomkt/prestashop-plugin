{capture name=path}{l s='Ethereum payment.' mod='cryptomarket'}{/capture}

<h2>{l s='Order summary' mod='cryptomarket'}</h2>

{assign var='current_step' value='payment'}

{include file="$tpl_dir./order-steps.tpl"}

{if ! $status }
	<h2 class="warning">{$message}</h2>
{/if}

