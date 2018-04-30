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

        $client = $this->getCryptoMarketClient();

        $cart_id = $payload->external_id; error_log('[Info] Cart ID:' . $cart_id);
        // $order = wc_get_order($order_id);
        // $order_states = $this->get_option('order_states');

        if ((int)Order::getOrderByCartId($cart_id)) {
            error_log('[Error] The Plugin was called but could not retrieve the order details for order_id: "' . $order_id . '". If you use an alternative order numbering system, please see class-wc-gateway-cryptomarket.php to apply a search filter.');
            throw new \Exception('The Plugin was called but could not retrieve the order details for order_id ' . $order_id . '. Cannot continue!');
        } else {
            error_log('[Info] Order details retrieved successfully...');
        }

        $current_status = $order->get_status();
        if (false === isset($current_status) && true === empty($current_status)) {
            error_log('[Error] The Plugin was calledbut could not obtain the current status from the order.');
            throw new \Exception('The Plugin was called but could not obtain the current status from the order. Cannot continue!');
        } else {
            error_log('[Info] The current order status for this order is ' . $current_status);
        }

        switch ($payload->status) {
            case "-4":
                error_log('[Info] Pago múltiple. Orden ID:'.$order_id);
                $order->update_status($order_states['invalid']);

                exit('Pago Multiple');
                break;
            case "-3":
                error_log('[Info] Monto pagado no concuerda. Orden ID:'.$order_id);
                $order->update_status($order_states['invalid']);

                exit('Monto pagado no concuerda');
                break;
            case "-2":
                error_log('[Info] Falló conversión. Orden ID:'.$order_id);
                $order->update_status($order_states['invalid']);

                exit('Falló conversión');
                break;
            case "-1":
                error_log('[Info] Expiró orden de pago. Orden ID:'.$order_id);
                $order->update_status($order_states['invalid']);

                exit('Expiró orden de pago');
                break;
            case "0":
                error_log('[Info] Esperando pago. Orden ID:'.$order_id);
                $order->update_status($order_states['waiting_pay']);

                break;
            case "1":
                error_log('[Info] Esperando bloque. Orden ID:'.$order_id);
                $order->update_status($order_states['waiting_block']);

                break;
            case "2":
                error_log('[Info] Esperando procesamiento. Orden ID:'.$order_id);
                $order->update_status($order_states['waiting_processing']);

                break;
            case "3":
                error_log('[Info] Pago exitoso. Orden ID:'.$order_id);
                $order->update_status($order_states['complete']);
                break;

            default:
                error_log('[Error] No status payment defined:'.$payload->status.'. Order ID:'.$order_id);
                break;
        }
        exit;
    }

}

$test = new Updater();

exit;



//execute
// update_order_states();
?>