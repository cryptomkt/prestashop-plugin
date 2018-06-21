{if $state == '2'}
        <p>{l s='Your order is complete.' sprintf=$shop_name mod='cryptomarket'}
                <br /><br /> <strong>{l s='Your order will be sent as soon as your payment is confirmed by the Ethereum|Bitcoin|Stellar network.' mod='cryptomarket'}</strong>
                <br /><br />{l s='If you have questions, comments or concerns, please contact our to' mod='cryptomarket'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='Contact us' mod='cryptomarket'}</a>
        </p>
{else}
      	<p class="warning">
                {l s="We noticed a problem with your order. If you think this is an error, feel free to contact our" mod='cryptomarket'}
                <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='Contact us' mod='cryptomarket'}</a>.
        </p>
{/if}
