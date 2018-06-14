<?php 
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/cryptomarket.php');

//composer autoload
$loader = require __DIR__ . '/vendor/autoload.php';
$loader->add('Cryptomkt\\Exchange\\Client', __DIR__);
$loader->add('Cryptomkt\\Exchange\\Configuration as CMConfiguration', __DIR__);

/**
 * [update_order_states wc-api update order status]
 */

class Updater extends cryptomarket{

	function __construct(){
        parent::__construct();

        $this->updateOrderStates();
	}

    public function updateOrderStates() {
        if (true === empty($_POST)) {
            error_log('[Error] Plugin received empty POST data for an callback_url message.');
            exit;
        } else {
            error_log('[Info] The post data sent from server is present...');
        }

        $payload = (object) $_POST;

        if (true === empty($payload)) {
            error_log('[Error] Invalid JSON payload: ' . $post_body);
            exit;
        } else {
            error_log('[Info] The post data was decoded into JSON...');
        }

        if (false === array_key_exists('id', $payload)) {
            error_log('[Error] Plugin did not receive an Order ID present in payload: ' . var_export($payload, true));
            exit;
        } else {
            error_log('[Info] Order ID present in payload...');
        }

        if (false === array_key_exists('external_id', $payload)) {
            error_log('[Error] Plugin did not receive an Order ID present in payload: ' . var_export($payload, true));
            exit;
        } else {
            error_log('[Info] Order ID present in JSON payload...');
        }

        $cart_id = $payload->external_id; error_log('[Info] Cart ID:' . $cart_id);

        if ( $order = (int)Order::getOrderByCartId($cart_id)) {
            error_log('[Error] The Plugin was called but could not retrieve the cart details for cart_id: "' . $cart_id . '". If you use an alternative order numbering system, please see class-wc-gateway-cryptomarket.php to apply a search filter.');
            throw new \Exception('The Plugin was called but could not retrieve the cart details for cart_id ' . $cart_id . '. Cannot continue!');
        } else {
            error_log('[Info] Order details retrieved successfully...');
        }

        switch ($payload->status) {
            case "-4":
                error_log('[Info] Pago múltiple. Cart ID:'.$cart_id);
                $status_cryptomarket = Configuration::get('PS_OS_PREPARATION');

                exit('Pago Multiple');
                break;
            case "-3":
                error_log('[Info] Monto pagado no concuerda. Cart ID:'.$cart_id);
                $status_cryptomarket = Configuration::get('PS_OS_PREPARATION');

                exit('Monto pagado no concuerda');
                break;
            case "-2":
                error_log('[Info] Falló conversión. Cart ID:'.$cart_id);
                $status_cryptomarket = Configuration::get('PS_OS_PREPARATION');

                exit('Falló conversión');
                break;
            case "-1":
                error_log('[Info] Expiró orden de pago. Cart ID:'.$cart_id);
                $status_cryptomarket = Configuration::get('PS_OS_PREPARATION');

                exit('Expiró orden de pago');
                break;
            case "0":
                error_log('[Info] Esperando pago. Cart ID:'.$cart_id);
                $status_cryptomarket = Configuration::get('PS_OS_PAYMENT');

                break;
            case "1":
                error_log('[Info] Esperando bloque. Cart ID:'.$cart_id);
                $status_cryptomarket = Configuration::get('PS_OS_PAYMENT');

                break;
            case "2":
                error_log('[Info] Esperando procesamiento. Cart ID:'.$cart_id);
                $status_cryptomarket = Configuration::get('PS_OS_PAYMENT');

                break;
            case "3":
                error_log('[Info] Pago exitoso. Cart ID:'.$cart_id);
                $status_cryptomarket = Configuration::get('PS_OS_PAYMENT');
                break;

            default:
                error_log('[Error] No status payment defined:'.$payload->status.'. Cart ID:'.$cart_id);
                break;
        }

        if ($order == 0){
            $cryptomarket = new cryptomarket();
            $cart = new Cart($cart_id);

            $key = 'test';
            $cryptomarket->validateOrder($cart_id, $status_cryptomarket, $cart->getTotalCart($cart_id), $cryptomarket->displayName, null, array(), null, false, $key);
            $cryptomarket->writeDetails($cryptomarket->currentOrder, $cart_id, $status_cryptomarket);
        }
        else{
            if (empty(Context::getContext()->link)){
                Context::getContext()->link = new Link(); // workaround a prestashop bug so email is sent 
            }

            $order = new Order((int)Order::getOrderByCartId($cart_id));
            $new_history = new OrderHistory();
            $new_history->id_order = (int)$order->id;
            $order_status = (int)$status_cryptomarket;
            $new_history->changeIdOrderState((int)$order_status, $order, true);
            $new_history->addWithemail(true);
        }
        
        exit;
    }

}

$test = new Updater();

exit;



//execute
// update_order_states();
?>