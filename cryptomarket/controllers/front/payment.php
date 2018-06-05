<?php

class cryptomarketPaymentModuleFrontController extends ModuleFrontController {
    public $ssl = true;
    public $display_column_left = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent() {
        $this->display_column_left = false;
        parent::initContent();

        $ps_version = (bool)version_compare(_PS_VERSION_, '1.7', '>=');

        $cart = $this->context->cart;

        $result = $this->module->execPayment($cart);

        if (!$result['success']) {
            $this->context->smarty->assign(
                array(
                    'status' => false,
                    'message' => $result['message']
                )
            );
	}
        if ($ps_version) { // if on ps 17
                $this->setTemplate('module:cryptomarket/views/templates/front/payment_execution17.tpl');
        }
         else { // if on ps 16
                $this->setTemplate('payment_execution.tpl');
        }
    }
}
