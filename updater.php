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

/**
 * [update_order_states wc-api update order status]
 */

class Updater extends cryptomarket
{

	public function __construct()
    {
        parent::__construct();

        $this->updateOrderStates();
	}

    public function updateOrderStates()
    { 
        if(true === empty($_POST))
        {
            error_log('[Error] Plugin received empty POST data for an callback_url message.');
            exit;
        } else {
            error_log('[Info] The post data sent from server is present...');
        }

        $payload = (object) $_POST;

        if(true === empty($payload))
        {
            error_log('[Error] Invalid JSON payload: ' . $payload);
            exit;
        } else {
            error_log('[Info] The post data was decoded into JSON...');
        }

        if(false === array_key_exists('id', $payload))
        {
            error_log('[Error] Plugin did not receive an Pay Order ID present in payload: ' . var_export($payload, true));
            exit;
        } else {
            error_log('[Info] Pay Order ID present in payload...');
        }

        $Cryptomarket = new Cryptomarket();

        if(false === array_key_exists('signature', $payload) && false === $Cryptomarket->checkResponseSignature($payload->signature, $payload->id, $payload->status))
        {
            error_log('[Error] Request is not signed:' . var_export($payload, true));
            exit;
        } else {
            error_log('[Info] Signature valid present in payload...');
        }

        if(false === array_key_exists('external_id', $payload))
        {
            error_log('[Error] Plugin did not receive an Order ID present in payload: ' . var_export($payload, true));
            exit;
        } else {
            error_log('[Info] Order ID present in JSON payload...');
        }

        $order_id = $payload->external_id; error_log('[Info] Order ID:' . $order_id);

        switch($payload->status)
        {
            case "-4":
                error_log('[Info] Pago múltiple. Order ID:'.$order_id);
                $status_cryptomarket = Configuration::get('PS_OS_ERROR');

                break;
            case "-3":
                error_log('[Info] Monto pagado no concuerda. Order ID:'.$order_id);
                $status_cryptomarket = Configuration::get('PS_OS_ERROR');

                break;
            case "-2":
                error_log('[Info] Falló conversión. Order ID:'.$order_id);
                $status_cryptomarket = Configuration::get('PS_OS_ERROR');

                break;
            case "-1":
                error_log('[Info] Expiró orden de pago. Order ID:'.$order_id);
                $status_cryptomarket = Configuration::get('PS_OS_ERROR');

                break;
            case "0":
                error_log('[Info] Esperando pago. Order ID:'.$order_id);
                $status_cryptomarket = Configuration::get('PS_OS_PREPARATION');

                break;
            case "1":
                error_log('[Info] Esperando bloque. Order ID:'.$order_id);
                $status_cryptomarket = Configuration::get('PS_OS_PREPARATION');

                break;
            case "2":
                error_log('[Info] Esperando procesamiento. Order ID:'.$order_id);
                $status_cryptomarket = Configuration::get('PS_OS_PREPARATION');

                break;
            case "3":
                error_log('[Info] Pago exitoso. Order ID:'.$order_id);
                $status_cryptomarket = Configuration::get('PS_OS_PAYMENT');
                break;

            default:
                error_log('[Error] No status payment defined:'.$payload->status.'. Order ID:'.$order_id);
                break;
        }

        if(empty(Context::getContext()->link))
        {
            Context::getContext()->link = new Link(); // workaround a prestashop bug so email is sent 
        }

        $order = new Order((int)$order_id);
        $new_history = new OrderHistory();
        $new_history->id_order = (int)$order->id;
        $order_status = (int)$status_cryptomarket;
        $new_history->changeIdOrderState((int)$order_status, $order, true);
        $new_history->addWithemail(true);
        
        exit;
    }
}

$test = new Updater();
exit;