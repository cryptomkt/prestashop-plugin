{extends file='page.tpl'}

{block name='page_title'}
    {l s='Payment status: Order summary' mod='cryptomarket'}
{/block}

{block name='page_content'}
<div class="container">
    <section class="page_content">
        {if isset($status) && !$status }
                <h2 class="warning">{$message}</h2>
        {/if}
    </section>
</div>
{/block}

