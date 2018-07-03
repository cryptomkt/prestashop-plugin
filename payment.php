<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    CryptoMarket Dev Team
 *  @copyright 2010-2015 CryptoMarket Inc
 *  @license   LICENSE.txt
 */

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/cryptomarket.php');

$cryptomarket = new cryptomarket();
Tools::redirect(Context::getContext()->link->getModuleLink('cryptomarket', 'payment'));
