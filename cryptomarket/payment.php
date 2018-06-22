<?php
/*
 * Plugin Name: CryptoCompra by CryptoMarket
 * Plugin URI: https://github.com/cryptomkt/prestashop-plugin
 * Description: Accept multiple cryptocurrencies and turn into local currency as EUR, CLP, BRL and ARS. Welcome to CryptoCompra a new way for payments: simple, free and totally secure.
 * Version: v0.1.1
 * Author: CryptoMarket Dev Team
 * Author URI: http://www.cryptomkt.com/
 * License: The MIT License (MIT)
 *
 */
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/cryptomarket.php');

$cryptomarket = new cryptomarket();
Tools::redirect(Context::getContext()->link->getModuleLink('cryptomarket', 'payment'));

?>