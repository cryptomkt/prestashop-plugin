{*
* MIT License
*
* Copyright (c) 2018 CryptoMarket Inc
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*
*}
<script type="text/javascript">
	var pos_select = 0;
</script>

{if version_compare($smarty.const._PS_VERSION_,'1.6','>=')}
	<script type="text/javascript" src="{$js_dir|escape:'htmlall'}jquery/plugins/tabpane/jquery.tabpane.js"></script>
	<link type="text/css" rel="stylesheet" href="{$css_dir|escape:'htmlall'}jquery/plugins/tabpane/jquery.tabpane.css" />
{else}
	<script type="text/javascript" src="{$js_dir|escape:'htmlall'}tabpane.js"></script>
	<link type="text/css" rel="stylesheet" href="{$css_dir|escape:'htmlall'}tabpane.css" />
{/if}

<div style="padding: 20px 50px 50px;">
	<img src="{$this_path|escape:'htmlall'}/views/img/logotipo-bld.png" /><br />
	<b>{l s='This module allows you to accept payments by CryptoMarket' mod='cryptomarket'}</b><br /><br />
	<p>
		{l s='If the client chooses this payment mode, your CriptoMarket account will be automatically credited.' mod='cryptomarket'}<br />
		{l s='You need to configure your CryptoMarket account before using this module.' mod='cryptomarket'}</p>
</div>

<form method="post" action="{$request_uri|escape:'utf8'}">
	<input type="hidden" name="tabs" id="tabs" value="0" />
	<div class="tab-pane" id="tab-pane-1" style="width:100%;">
		<div class="tab-page" id="step1">
			<h4 class="tab">{l s='Settings' mod='cryptomarket'}</h2>
				<h2>{l s='Settings' mod='cryptomarket'}</h2>
				<div style="clear:both;margin-bottom:30px;">

					<h3 style="clear:both;margin-left:5px;margin-top:10px">{l s='Payment Receiver Email' mod='cryptomarket'}</h3>

					<input type="text" style="width:400px;" name="cryptomkt_payment_receiver" value="{$cryptomkt_payment_receiver|escape:'htmlall'}" />

					<h3 style="clear:both;margin-left:5px;margin-top:10px">API Key</h3>

					<input type="text" style="width:400px;" name="cryptomkt_apikey" value="{$cryptomkt_apikey|escape:'htmlall'}" />

					<h3 style="clear:both;margin-left:5px">API Secret</h3>

					<input type="text" style="width:400px;" name="cryptomkt_apisecret" value="{$cryptomkt_apisecret|escape:'htmlall'}" />

					<p class="center"><input class="button" type="submit" name="submitcryptomarket" value="{l s='Save settings' mod='cryptomarket'}" /></p>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<script type="text/javascript">
			function loadTab(id){}
			setupAllTabs();
		</script>
	</form>
