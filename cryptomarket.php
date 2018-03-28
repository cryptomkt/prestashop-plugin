<?php
// use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if( !defined('_PS_VERSION_'))
exit;

//composer autoload
if(file_exists('vendor/autoload.php')){
	require_once('vendor/autoload.php');
  // use Cryptomkt\Exchange\Client;
  // include 'vendor/cryptomkt/cryptomkt/src/Client.php';
  // include 'vendor/cryptomkt/cryptomkt/src/Configuration.php';
}

class cryptomarket extends PaymentModule{
  private $_html = '';

	/**
	 * [__construct define details of module]
	 */
	public function __construct()
	{
		$this->name = 'cryptomarket';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'CryptoMarket Development Team';
		$this->className = 'cryptomarket';
		// $this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6.x.x', 'max' => _PS_VERSION_);
		$this->bootstrap = false;
    $this->controllers = array('payment', 'validation');
    $this->sslport         = 443;
    $this->verifypeer      = 1;
    $this->verifyhost      = 2;
		$this->currencies = true;  

		parent::__construct();

		$this->displayName = $this->l('CryptoMarket');
		$this->description = $this->l('Integrate cryptocurrencies into Prestashop and welcome to the new way for payments. Simple, Free and totally Secure.');
		$this->confirmUninstall = $this->l('Would you like uninstall this plugin?');

		// $this->templateFile = 'module::main/views/templates/hook/main.tpl';
	}

	public function install() {
    if(!function_exists('curl_version')) {
      $this->_errors[] = $this->l('Sorry, this module requires the cURL PHP extension but it is not enabled on your server.  Please ask your web hosting provider for assistance.');
      return false;
    }

    // if (!parent::install() || !$this->registerHook('paymentOptions')) {
    //   return false;
    // }

    if (!parent::install() || !$this->registerHook('invoice') || !$this->registerHook('paymentReturn') || !$this->registerHook('payment') || !$this->registerHook('paymentOptions')) {
        return false;
      }

    $db = Db::getInstance();
    $query = "CREATE TABLE `"._DB_PREFIX_."cryptomarket` (
              `api_key` varchar(255) NOT NULL,
              `api_secret` varchar(255) NOT NULL) ENGINE="._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
    $db->Execute($query);

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
                      <h2>'.$this->l('CryptoMarket').'</h2>
                      <img src="../modules/bitpay/bitcoin.png" style="float:left; margin-right:15px;" />
                       <b>'.$this->l('This module allows you to accept payments by CryptoMarket.').'</b><br /><br />
                       '.$this->l('If the client chooses this payment mode, your CriptoMarket account will be automatically credited.').'<br />
                       '.$this->l('You need to configure your CryptoMarket account before using this module.').'</div>';
  }

  private function _postProcess() {
    global $currentIndex, $cookie;
    if (Tools::isSubmit('submitcryptomarket')) {
      $template_available = array('A', 'B', 'C');
      $this->_errors      = array();
      if (Tools::getValue('apikey') == NULL)
        $this->_errors[]  = $this->l('Missing API Key');
      if (count($this->_errors) > 0) {
        $error_msg = '';
        
        foreach ($this->_errors AS $error)
          $error_msg .= $error.'<br />';
        
        $this->_html = $this->displayError($error_msg);
      } else {
        Configuration::updateValue('apikey', trim(Tools::getValue('apikey')));
        Configuration::updateValue('apisecret', trim(Tools::getValue('apisecret')));
        $this->_html = $this->displayConfirmation($this->l('Settings updated'));
      }
    }
  }

	private function _setConfigurationForm() {
      $this->_html .= '<form method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'">
                       <script type="text/javascript">
                       var pos_select = '.(($tab = (int)Tools::getValue('tabs')) ? $tab : '0').';
                       </script>';
      if (_PS_VERSION_ <= '1.5') {
        $this->_html .= '<script type="text/javascript" src="'._PS_BASE_URL_._PS_JS_DIR_.'tabpane.js"></script>
                         <link type="text/css" rel="stylesheet" href="'._PS_BASE_URL_._PS_CSS_DIR_.'tabpane.css" />';
      } else {
        $this->_html .= '<script type="text/javascript" src="'._PS_BASE_URL_._PS_JS_DIR_.'jquery/plugins/tabpane/jquery.tabpane.js"></script>
                         <link type="text/css" rel="stylesheet" href="'._PS_BASE_URL_._PS_JS_DIR_.'jquery/plugins/tabpane/jquery.tabpane.css" />';
      }
      $this->_html .= '<input type="hidden" name="tabs" id="tabs" value="0" />
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

	private function _getSettingsTabHtml() {
      global $cookie;
      
      $html = '<h2>'.$this->l('Settings').'</h2>
               <div style="clear:both;margin-bottom:30px;">
               <h3 style="clear:both;margin-left:5px;margin-top:10px">'.$this->l('API Key').'</h3>
               <input type="text" style="width:400px;" name="apikey" value="'.htmlentities(Tools::getValue('apikey', Configuration::get('apikey')), ENT_COMPAT, 'UTF-8').'" />
               </div>
               <h3 style="clear:both;margin-left:5px">'.$this->l('API Secret').'</h3>               
               <input type="text" style="width:400px;" name="apisecret" value="'.htmlentities(Tools::getValue('apisecret', Configuration::get('apisecret')), ENT_COMPAT, 'UTF-8').'" />
               </div>
               <p class="center"><input class="button" type="submit" name="submitcryptomarket" value="'.$this->l('Save settings').'" /></p>';
      return $html;
    }


	public function uninstall()
	{
		// $this->clearCache('*');

		if( !parent::uninstall() || !$this->unregisterHook('displayHome'))
			return false;

		return true;
	}

  public function hookInvoice($params) {
    global $smarty;
          
    $id_order = $params['id_order'];
    $bitcoinpaymentdetails = $this->readBitcoinpaymentdetails($id_order);
    if($bitcoinpaymentdetails['invoice_id'] === 0)
    {
        return;
    }
    
    $smarty->assign(array(
                          'bitpayurl'    =>  $this->bitpayurl,
                          'invoice_id'    => $bitcoinpaymentdetails['invoice_id'],
                          'status'        => $bitcoinpaymentdetails['status'],
                          'id_order'      => $id_order,
                          'this_page'     => $_SERVER['REQUEST_URI'],
                          'this_path'     => $this->_path,
                          'this_path_ssl' => Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/"
                         ));
    return $this->display(__FILE__, 'invoice_block.tpl');
  }

  public function hookPayment($params)
  {
    if (!$this->active)
      return;

    $this->smarty->assign(array(
      'this_path' => $this->_path,
      'this_path_bw' => $this->_path,
      'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
    ));

    return $this->display(__FILE__, 'payment.tpl');
  }

  public function hookPaymentReturn($params) {
    global $smarty;
    
    $order = ($params['objOrder']);
    $state = $order->current_state;
    $smarty->assign(array(
                          'state'         => $state,
                          'this_path'     => $this->_path,
                          'this_path_ssl' => Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/"));
    return $this->display(__FILE__, 'payment_return.tpl');
  }

  public function hookPaymentOptions($params)
  {
      if (!$this->active) {
            return;
      }
      else{
      }

      $payment_options = [
          $this->linkToCryptoMkt(),
      ];
              
      return $payment_options;
  }

  public function execPayment($cart) { 
    // $configuration = Configuration::apiKey(Configuration::get('api_key'), Configuration::get('api_secret'));
    // $client = Client::create($configuration);

    // var_dump($client);

      // Create invoice
      // $currency                    = Currency::getCurrencyInstance((int)$cart->id_currency);
      // $options                     = $_POST;
      // $options['transactionSpeed'] = Configuration::get('bitpay_TXSPEED');
      // $options['currency']         = $currency->iso_code;
      // $total                       = $cart->getOrderTotal(true);
      
      // $options['notificationURL']  = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/ipn.php';
      // if (_PS_VERSION_ <= '1.5')
      //   $options['redirectURL']    = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$this->id.'&id_order='.$this->currentOrder;
      // else
      //   $options['redirectURL']    = Context::getContext()->link->getModuleLink('bitpay', 'validation');
      // $options['posData']          = '{"cart_id": "' . $cart->id . '"';
      // $options['posData']         .= ', "hash": "' . crypt($cart->id, Configuration::get('bitpay_APIKEY')) . '"';
      // $this->key                   = $this->context->customer->secure_key;
      
      // $options['posData']         .= ', "key": "' . $this->key . '"}';
      // $options['orderID']          = $cart->id;
      // $options['price']            = $total;
      // $options['fullNotifications'] = true;
      // $postOptions                 = array('orderID', 'itemDesc', 'itemCode', 
      //                                      'notificationEmail', 'notificationURL', 'redirectURL', 
      //                                      'posData', 'price', 'currency', 'physical', 'fullNotifications',
      //                                      'transactionSpeed', 'buyerName', 'buyerAddress1', 
      //                                      'buyerAddress2', 'buyerCity', 'buyerState', 'buyerZip', 
      //                                      'buyerEmail', 'buyerPhone');
      
      // foreach($postOptions as $o) {
      //   if (array_key_exists($o, $options))
      //     $post[$o] = $options[$o];
      // }
      // if(function_exists('json_encode'))
      //   $post = json_encode($post);
      // else
      //   $post = rmJSONencode($post);
      // // Call BitPay
      // $curl = curl_init($this->apiurl.'/api/invoice/');
      // $length = 0;
      // if ($post) {
      //   curl_setopt($curl, CURLOPT_POST, 1);
      //   curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
      //   $length = strlen($post);
      // }
      // $uname  = base64_encode(Configuration::get('bitpay_APIKEY'));
      // $header = array(
      //                 'Content-Type: application/json',
      //                 'Content-Length: ' . $length,
      //                 'Authorization: Basic ' . $uname,
      //                 'X-BitPay-Plugin-Info: PrestaShop'.$this->version,
      //                );
      // curl_setopt($curl, CURLINFO_HEADER_OUT, true);
      // curl_setopt($curl, CURLOPT_PORT, $this->sslport);
      // curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
      // curl_setopt($curl, CURLOPT_TIMEOUT, 10);
      // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
      // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->verifypeer); // verify certificate (1)
      // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->verifyhost); // check existence of CN and verify that it matches hostname (2)
      // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      // curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
      // curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
      
      // $responseString = curl_exec($curl);
      // if(!$responseString) {
      //   $response = curl_error($curl);
      //   die(Tools::displayError("Error: no data returned from API server!"));
      
      // } else {
      //   if(function_exists('json_decode'))
      //     $response = json_decode($responseString, true);
      //   else
      //     $response = rmJSONdecode($responseString);
      // }
      // curl_close($curl);
      // if(isset($response['error'])) {
      //   bplog($response['error']);
      //   die(Tools::displayError("Error occurred! (" . $response['error']['type'] . " - " . $response['error']['message'] . ")"));
      // } else if(!$response['url']) {
      //   die(Tools::displayError("Error: Response did not include invoice url!"));
      // } else {
      //   \ob_clean();  
      //   header('Location:  ' . $response['url']);
      //   exit;
      // }
 
    }
  
  public function linkToCryptoMkt()
  {
      $cryptomarket_option = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
      $cryptomarket_option->setCallToActionText($this->l('CryptoMarket'))
                    ->setAction(Configuration::get('PS_FO_PROTOCOL').__PS_BASE_URI__."modules/{$this->name}/payment.php");
      return $cryptomarket_option;
  }
}
?>