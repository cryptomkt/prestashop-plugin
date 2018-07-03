<?php
/**
 * NOTICE OF LICENSE.
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

class CryptomarketPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $this->display_column_left = false;
        parent::initContent();

        $ps_version = (bool) version_compare(_PS_VERSION_, '1.7', '>=');

        $cart = $this->context->cart;

        $result = $this->module->execPayment($cart);

        if (!$result['success']) {
            $this->context->smarty->assign(array('status' => false,'message' => $result['message']));
        }

        if ($ps_version) { // if on ps 17
            $this->setTemplate('module:cryptomarket/views/templates/front/payment_execution17.tpl');
        } else { // if on ps 16
            $this->setTemplate('payment_execution.tpl');
        }
    }
}
