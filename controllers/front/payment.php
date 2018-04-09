<?php


class cryptomarketPaymentModuleFrontController extends ModuleFrontController
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

    $cart = $this->context->cart;

    $result = $this->module->execPayment($cart);

    if( $result['success'] ){
      $this->context->smarty->assign(
          array(
              'status' => true,
              'result' => $result['payload'],
              'iframe' => file_get_contents($result['payload']['payment_url']),
              'cart' => $cart
          )
      );
    }
    else{
      $this->context->smarty->assign(
          array(
              'status' => false,
              'message' => $result['message']
          )
      );
    }

    $this->setTemplate('payment_execution.tpl');

    // echo $this->module->execPayment($cart);
  }
}