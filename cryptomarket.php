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

if (!defined('_PS_VERSION_')) {
    exit;
}

//composer autoload
$loader = require dirname(__FILE__).'/vendor/autoload.php';
$loader->add('Cryptomkt\\Exchange\\Client\\', dirname(__FILE__));
$loader->add('Cryptomkt\\Exchange\\Configuration\\', dirname(__FILE__));
$loader->add('Cryptomkt\\Exchange\\Authentication\\', dirname(__FILE__));

class Cryptomarket extends PaymentModule
{
    private $htmloutput = '';

    /**
     * [__construct define details of module].
     */
    public function __construct()
    {
        $this->name = 'cryptomarket';
        $this->tab = 'payments_gateways';
        $this->version = '0.1.1';
        $this->author = 'CryptoMarket Dev Team';
        $this->className = 'cryptomarket';
        $this->ps_versions_compliancy = array('min' => '1.5.x.x', 'max' => _PS_VERSION_);
        $this->bootstrap = false;
        $this->controllers = array('payment', 'validation');
        $this->sslport = 443;
        $this->verifypeer = 1;
        $this->verifyhost = 2;
        $this->currencies = true;

        parent::__construct();

        $this->displayName = $this->l('CryptoCompra by CryptoMarket');
        $this->description = $this->l('Accept multiple cryptocurrencies and turn into local currency as EUR, CLP, BRL and ARS. Welcome to CryptoCompra a new way for payments: simple, free and totally secure.');
        $this->confirmUninstall = $this->l('Would you like uninstall this plugin?');
    }

    public function install()
    {
        if (!function_exists('curl_version')) {
            $this->_errors[] = $this->l('Sorry, this module requires the cURL PHP extension but it is not enabled on your server.');

            return false;
        }

        if (!parent::install() || !$this->registerHook('invoice') || !$this->registerHook('paymentReturn') || !$this->registerHook('payment') || !$this->registerHook('paymentOptions') || !$this->addOrderState($this->l('Procesando CryptoCompra'))) {
            return false;
        }

        return true;
    }

    /**
     * getContent Return a valid html form.
     *
     * @return string html formatted
     */
    public function getContent()
    {
        $this->_postProcess();
        $this->_setSettingHeader();
        $this->_setConfigurationForm();

        return $this->htmloutput;
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !$this->unregisterHook('displayHome')) {
            return false;
        }

        return true;
    }

    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
        ));

        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentReturn($params)
    {
        $order = ($params['objOrder']);
        $state = $order->current_state;
        $this->smarty->assign(array(
            'state' => $state,
            'this_path' => $this->_path,
            'this_path_ssl' => Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/", ));

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return false;
        }

        return array(0 => $this->linkToCryptoMkt());
    }

    /**
     * getCryptoMarketClient Return a Cryptomarket PHP Client.
     *
     * @return Cryptomkt\Exchange\Client Object
     */
    public function getCryptoMarketClient()
    {
        $configuration = Cryptomkt\Exchange\Configuration::apiKey(Configuration::get('apikey'), Configuration::get('apisecret'));

        return Cryptomkt\Exchange\Client::create($configuration);
    }

    /**
     * execPayment Main function.
     *
     * @param PSCart $cart Default cart
     *
     * @return array Smarty success return
     */
    public function execPayment($cart)
    {
        $client = $this->getCryptoMarketClient();
        $currency = Currency::getCurrencyInstance((int) $cart->id_currency);

        $callback_url = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/updater.php';

        try {
            $result = $client->getTicker(array('market' => 'ETH'.$currency->iso_code));
            if ('error' === $result->status) {
                return array('success' => false, 'message' => $this->l('Currency does not supported: '.$currency->iso_code));
            }
        } catch (Exception $e) {
            return array('success' => false, 'message' => $this->l('Currency does not supported: '.$currency->iso_code));
        }

        //Min value validation
        $min_value = (float) $result->data[0]->bid * 0.001;
        $total_order = (float) $cart->getOrderTotal(true, Cart::BOTH);

        if ($total_order > $min_value) {
            try {
                //create order
                $customer = new Customer($cart->id_customer);
                $order_state = $this->getOrderState('Procesando CryptoCompra');
                $this->validateOrder($cart->id, $order_state['id_order_state'], $total_order, $this->displayName, null, null, (int) $currency->id, false, $customer->secure_key);

                if (_PS_VERSION_ <= '1.5') {
                    $success_url = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$this->id.'&id_order='.$this->currentOrder;
                } else {
                    $success_url = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int) $cart->id.'&id_module='.(int) $this->id.'&id_order='.$this->currentOrder.'&key='.$customer->secure_key;
                }

                $payment = array(
                    'payment_receiver' => Configuration::get('payment_receiver'),
                    'to_receive_currency' => $currency->iso_code,
                    'to_receive' => $total_order,
                    'external_id' => $this->currentOrder,
                    'callback_url' => $callback_url,
                    'error_url' => $this->context->link->getPagelink('order'),
                    'success_url' => $success_url,
                    'refund_email' => $this->context->customer->email,
                    'language' => Tools::strtolower(Configuration::get('PS_LOCALE_LANGUAGE')),
                );

                $payload = $client->createPayOrder($payment);

                if ('error' === $payload->status) {
                    return array('success' => false, 'message' => $payload->message);
                }

                \ob_clean();
                Tools::redirect($payload->data->payment_url);
                exit;
            } catch (Exception $e) {
                return array('success' => false, 'message' => $e->getMessage());
            }
        } else {
            return array('success' => false, 'message' => $this->l('Total order must be greater than ').$currency->iso_code.' '.$min_value);
        }
    }

    public function linkToCryptoMkt()
    {
        $cryptomarket_option = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentGateway = $this->fetch('module:cryptomarket/views/templates/front/payment_execution.tpl');
        $cryptomarket_option->setCallToActionText($this->l('CryptoMarket'))
            ->setForm($paymentGateway)
            ->setAction(Configuration::get('PS_FO_PROTOCOL').__PS_BASE_URI__."modules/{$this->name}/payment.php");

        return $cryptomarket_option;
    }

    /**
     * addOrderState Add a order state.
     *
     * @param string $name Order name
     */
    public function addOrderState($name)
    {
        $state_exist = false;
        $state_exist = $this->getOrderState($name);

        // If the state does not exist, we create it.
        if (false !== $state_exist) {
            // create new order state
            $order_state = new OrderState();
            $order_state->color = '#00ffff';
            $order_state->send_email = true;
            $order_state->module_name = $this->name;
            $order_state->template = 'preparation';
            $order_state->name = array();
            $languages = Language::getLanguages(false);

            foreach ($languages as $language) {
                $order_state->name[$language['id_lang']] = $name;
            }

            // Update object
            $order_state->add();
        }

        return true;
    }

    /**
     * getOrderState get a object order state.
     *
     * @param string $name Order name
     *
     * @return object State order object
     */
    public function getOrderState($name)
    {
        $state = false;
        $states = OrderState::getOrderStates((int) $this->context->language->id);

        // check if order state exist
        foreach ($states as $state) {
            if (in_array($name, $state, true)) {
                $state = $state;
                break;
            }
        }

        return $state;
    }

    public function checkResponseSignature($hash, $id, $status)
    {
        return Cryptomkt\Exchange\Authentication\ApiKeyAuthentication::checkHash($hash, $id.$status, Configuration::get('apisecret'));
    }

    private function _setSettingHeader()
    {
        $this->htmloutput .= '<div style="padding: 20px 50px 50px;">
                      <img src="../modules/cryptomarket/views/img/logotipo-bld.png" />
                       <br><b>'.$this->l('This module allows you to accept payments by CryptoMarket.').'</b><br /><br />
                       '.$this->l('If the client chooses this payment mode, your CriptoMarket account will be automatically credited.').'<br />
                       '.$this->l('You need to configure your CryptoMarket account before using this module.').'</div>';
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('submitcryptomarket')) {
            $this->_errors = array();
            if (null === Tools::getValue('apikey')) {
                $this->_errors[] = $this->l('Missing API Key');
            }

            if (count($this->_errors) > 0) {
                $error_msg = '';

                foreach ($this->_errors as $error) {
                    $error_msg .= $error.'<br />';
                }

                $this->htmloutput = $this->displayError($error_msg);
            } else {
                Configuration::updateValue('payment_receiver', trim(Tools::getValue('payment_receiver')));
                Configuration::updateValue('apikey', trim(Tools::getValue('apikey')));
                Configuration::updateValue('apisecret', trim(Tools::getValue('apisecret')));
                $this->htmloutput = $this->displayConfirmation($this->l('Settings updated'));
            }
        }
    }

    private function _setConfigurationForm()
    {
        $this->htmloutput .= '<form method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'">
                       <script type="text/javascript">
                       var pos_select = '.(($tab = (int) Tools::getValue('tabs')) ? $tab : '0').';
                       </script>';
        if (_PS_VERSION_ <= '1.5') {
            $this->htmloutput .= '<script type="text/javascript" src="'._PS_BASE_URL_._PS_JS_DIR_.'tabpane.js"></script>
                         <link type="text/css" rel="stylesheet" href="'._PS_BASE_URL_._PS_CSS_DIR_.'tabpane.css" />';
        } else {
            $this->htmloutput .= '<script type="text/javascript" src="'._PS_BASE_URL_._PS_JS_DIR_.'jquery/plugins/tabpane/jquery.tabpane.js"></script>
                         <link type="text/css" rel="stylesheet" href="'._PS_BASE_URL_._PS_JS_DIR_.'jquery/plugins/tabpane/jquery.tabpane.css" />';
        }
        $this->htmloutput .= '<input type="hidden" name="tabs" id="tabs" value="0" />
                       <div class="tab-pane" id="tab-pane-1" style="width:100%;">
                       <div class="tab-page" id="step1">
                       <h4 class="tab">'.$this->l('Settings').'</h2>
                       '.$this->_getSettingsTabHtml().'
                       </div>
                       </div>
                       <div class="clear"></div>
                       <script type="text/javascript">
                       function loadTab(id){}
                       setupAllTabs();
                       </script>
                       </form>';
    }

    private function _getSettingsTabHtml()
    {
        $html = '<h2>'.$this->l('Settings').'</h2>
               <div style="clear:both;margin-bottom:30px;">

               <h3 style="clear:both;margin-left:5px;margin-top:10px">'.$this->l('Payment Receiver Email').'</h3>

               <input type="text" style="width:400px;" name="payment_receiver" value="'.htmlentities(Tools::getValue('payment_receiver', Configuration::get('payment_receiver')), ENT_COMPAT, 'UTF-8').'" />

               <h3 style="clear:both;margin-left:5px;margin-top:10px">'.$this->l('API Key').'</h3>

               <input type="text" style="width:400px;" name="apikey" value="'.htmlentities(Tools::getValue('apikey', Configuration::get('apikey')), ENT_COMPAT, 'UTF-8').'" />

               <h3 style="clear:both;margin-left:5px">'.$this->l('API Secret').'</h3>

               <input type="text" style="width:400px;" name="apisecret" value="'.htmlentities(Tools::getValue('apisecret', Configuration::get('apisecret')), ENT_COMPAT, 'UTF-8').'" />

               <p class="center"><input class="button" type="submit" name="submitcryptomarket" value="'.$this->l('Save settings').'" /></p>
               </div>
               ';

        return $html;
    }
}
