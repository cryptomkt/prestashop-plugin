<?php
include(dirname(__FILE__).'/cryptomarket.php');

$cryptomarket = new cryptomarket();
Tools::redirect(Context::getContext()->link->getModuleLink('cryptomarket', 'payment'));

?>