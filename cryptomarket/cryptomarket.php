<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

//composer autoload
$loader = require __DIR__ . '/vendor/autoload.php';
$loader->addPsr4('Cryptomkt\\Exchange\\Client\\', __DIR__);
$loader->addPsr4('Cryptomkt\\Exchange\\Configuration\\', __DIR__);

class cryptomarket extends PaymentModule {
    private $_html = '';

    /**
     * [__construct define details of module]
     */
    public function __construct() {
        $this->name = 'cryptomarket';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'CryptoMarket Development Team';
        $this->className = 'cryptomarket';
        $this->ps_versions_compliancy = array('min' => '1.5.x.x', 'max' => _PS_VERSION_);
        $this->bootstrap = false;
        $this->controllers = array('payment', 'validation');
        $this->sslport = 443;
        $this->verifypeer = 1;
        $this->verifyhost = 2;
        $this->currencies = true;

        parent::__construct();

        $this->displayName = $this->l('CryptoMarket');
        $this->description = $this->l('Integrate cryptocurrencies into Prestashop and welcome to the new way for payments. Simple, Free and totally Secure.');
        $this->confirmUninstall = $this->l('Would you like uninstall this plugin?');
    }

    public function install() {
        if (!function_exists('curl_version')) {
            $this->_errors[] = $this->l('Sorry, this module requires the cURL PHP extension but it is not enabled on your server.  Please ask your web hosting provider for assistance.');
            return false;
        }

        if (!parent::install() || !$this->registerHook('invoice') || !$this->registerHook('paymentReturn') || !$this->registerHook('payment') || !$this->registerHook('paymentOptions')) {
            return false;
        }

        return true;
    }

    public function getContent() {
        $this->_postProcess();
        $this->_setSettingHeader();
        $this->_setConfigurationForm();
        return $this->_html;
    }

    private function _setSettingHeader() {
        $this->_html .= '<div style="padding: 20px 50px 50px;">
                      <img src="../modules/cryptomarket/img/logotipo-bld.png" />
                       <br><b>' . $this->l('This module allows you to accept payments by CryptoMarket.') . '</b><br /><br />
                       ' . $this->l('If the client chooses this payment mode, your CriptoMarket account will be automatically credited.') . '<br />
                       ' . $this->l('You need to configure your CryptoMarket account before using this module.') . '</div>';
    }

    private function _postProcess() {
        global $currentIndex, $cookie;
        if (Tools::isSubmit('submitcryptomarket')) {
            $template_available = array('A', 'B', 'C');
            $this->_errors = array();
            if (Tools::getValue('apikey') == NULL) {
                $this->_errors[] = $this->l('Missing API Key');
            }

            if (count($this->_errors) > 0) {
                $error_msg = '';

                foreach ($this->_errors AS $error) {
                    $error_msg .= $error . '<br />';
                }

                $this->_html = $this->displayError($error_msg);
            } else {
                Configuration::updateValue('payment_receiver', trim(Tools::getValue('payment_receiver')));
                Configuration::updateValue('apikey', trim(Tools::getValue('apikey')));
                Configuration::updateValue('apisecret', trim(Tools::getValue('apisecret')));
                $this->_html = $this->displayConfirmation($this->l('Settings updated'));
            }
        }
    }

    private function _setConfigurationForm() {
        $this->_html .= '<form method="post" action="' . htmlentities($_SERVER['REQUEST_URI']) . '">
                       <script type="text/javascript">
                       var pos_select = ' . (($tab = (int) Tools::getValue('tabs')) ? $tab : '0') . ';
                       </script>';
        if (_PS_VERSION_ <= '1.5') {
            $this->_html .= '<script type="text/javascript" src="' . _PS_BASE_URL_ . _PS_JS_DIR_ . 'tabpane.js"></script>
                         <link type="text/css" rel="stylesheet" href="' . _PS_BASE_URL_ . _PS_CSS_DIR_ . 'tabpane.css" />';
        } else {
            $this->_html .= '<script type="text/javascript" src="' . _PS_BASE_URL_ . _PS_JS_DIR_ . 'jquery/plugins/tabpane/jquery.tabpane.js"></script>
                         <link type="text/css" rel="stylesheet" href="' . _PS_BASE_URL_ . _PS_JS_DIR_ . 'jquery/plugins/tabpane/jquery.tabpane.css" />';
        }
        $this->_html .= '<input type="hidden" name="tabs" id="tabs" value="0" />
                       <div class="tab-pane" id="tab-pane-1" style="width:100%;">
                       <div class="tab-page" id="step1">
                       <h4 class="tab">' . $this->l('Settings') . '</h2>
                       ' . $this->_getSettingsTabHtml() . '
                       </div>
                       </div>
                       <div class="clear"></div>
                       <script type="text/javascript">
                       function loadTab(id){}
                       setupAllTabs();
                       </script>
                       </form>';
    }

    private function _getSettingsTabHtml() {
        global $cookie;

        $html = '<h2>' . $this->l('Settings') . '</h2>
               <div style="clear:both;margin-bottom:30px;">

               <h3 style="clear:both;margin-left:5px;margin-top:10px">' . $this->l('Payment Receiver Email') . '</h3>

               <input type="text" style="width:400px;" name="payment_receiver" value="' . htmlentities(Tools::getValue('payment_receiver', Configuration::get('payment_receiver')), ENT_COMPAT, 'UTF-8') . '" />

               <h3 style="clear:both;margin-left:5px;margin-top:10px">' . $this->l('API Key') . '</h3>

               <input type="text" style="width:400px;" name="apikey" value="' . htmlentities(Tools::getValue('apikey', Configuration::get('apikey')), ENT_COMPAT, 'UTF-8') . '" />

               <h3 style="clear:both;margin-left:5px">' . $this->l('API Secret') . '</h3>

               <input type="text" style="width:400px;" name="apisecret" value="' . htmlentities(Tools::getValue('apisecret', Configuration::get('apisecret')), ENT_COMPAT, 'UTF-8') . '" />

               <p class="center"><input class="button" type="submit" name="submitcryptomarket" value="' . $this->l('Save settings') . '" /></p>
               </div>
               ';
        return $html;
    }

    public function uninstall() {
        if (!parent::uninstall() || !$this->unregisterHook('displayHome')) {
            return false;
        }

        return true;
    }

    public function hookPayment($params) {
        if (!$this->active) {
            return;
        }

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
        ));

        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentReturn($params) {
        global $smarty;

        $order = ($params['objOrder']);
        $state = $order->current_state;
        $smarty->assign(array(
            'state' => $state,
            'this_path' => $this->_path,
            'this_path_ssl' => Configuration::get('PS_FO_PROTOCOL') . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . "modules/{$this->name}/"));
        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function hookPaymentOptions($params) {
        if (!$this->active) {
            return;
        } else {
        }

        $payment_options = [
            $this->linkToCryptoMkt(),
        ];

        return $payment_options;
    }

    public function getCryptoMarketClient(){
        $configuration = Cryptomkt\Exchange\Configuration::apiKey(Configuration::get('apikey'), Configuration::get('apisecret'));
        return Cryptomkt\Exchange\Client::create($configuration);
    }

    public function execPayment($cart) {
        global $smarty;

        $client = $this->getCryptoMarketClient();
        $currency = Currency::getCurrencyInstance((int) $cart->id_currency);

        $callback_url  = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/updater.php';

        if (_PS_VERSION_ <= '1.5') {
            $redirect_url = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'order-confirmation.php?id_cart=' . $cart->id . '&id_module=' . $this->id . '&id_order=' . $this->currentOrder;
        } else {
            $redirect_url = Context::getContext()->link->getModuleLink('cryptomarket', 'validation');
        }

        try {
            $result = $client->getTicker(array('market' => 'ETH' . $currency->iso_code));
            if($result->status === 'error'){
                return array('success' => false, 'message' => $this->l('Currency does not supported: ' . $currency->iso_code));
            }
        } catch (Exception $e) {
            return array('success' => false, 'message' => $this->l('Currency does not supported: ' . $currency->iso_code));
        }

        //Min value validation
        $min_value = (float) $result->data[0]->bid * 0.001;
        $total_order = (float) $cart->getOrderTotal();

        if ($total_order > $min_value) {
            try {
                $payment = array(
                    'payment_receiver' => Configuration::get('payment_receiver'),
                    'to_receive_currency' => $currency->iso_code,
                    'to_receive' => $total_order,
                    'external_id' => $cart->id,
                    'callback_url' => $callback_url,
                    'error_url' => $this->context->link->getPagelink('order&step=3'),
                    'success_url' => $redirect_url,
                    'refund_email' => $this->context->customer->email,
                );                

                $payload = $client->createPayOrder($payment);

                if($payload->status === 'error'){
                    return array('success' => false, 'message' => $payload->message);
       	        }
                else{
                    \ob_clean();
               	    header('Location:  ' . $payload->data->payment_url);
                    exit;
                }
            } catch (Exception $e) {
                return array('success' => false, 'message' => $e->getMessage());
            }
        } else {
            return array('success' => false, 'message' => $this->l('Total order must be greater than ') . $currency->iso_code . ' ' . $min_value);
        }
    }


    public function linkToCryptoMkt() {
        $cryptomarket_option = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentGateway = $this->fetch('module:cryptomarket/views/templates/front/payment_execution.tpl');
        $cryptomarket_option->setCallToActionText($this->l('CryptoMarket'))
            ->setForm($paymentGateway)
            ->setAction(Configuration::get('PS_FO_PROTOCOL') . __PS_BASE_URI__ . "modules/{$this->name}/payment.php");
        return $cryptomarket_option;
    }
}
?>