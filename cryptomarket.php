<?php
// use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if( !defined('_PS_VERSION_'))
exit;

//composer autoload
if(file_exists('vendor/autoload.php')){
	require_once('vendor/autoload.php');
}

class cryptomarket extends PaymentModule{

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
		$this->bootstrap = true;
    $this->controllers = array('payment', 'validation');
    $this->sslport         = $sslport;
    $this->verifypeer      = $verifypeer;
    $this->verifyhost      = $verifyhost;
		$this->currencies = true;  

		parent::__construct();

		$this->displayName = $this->l('CryptoMarket Pago');
		$this->description = $this->l('Integrate cryptocurrencies into Prestashop and welcome to the new way for payments. Simple, Free and totally Secure.');
		$this->confirmUninstall = $this->l('Would you like uninstall this plugin?');

		// $this->templateFile = 'module::main/views/templates/hook/main.tpl';
	}

	public function install() {
      if(!function_exists('curl_version')) {
        $this->_errors[] = $this->l('Sorry, this module requires the cURL PHP extension but it is not enabled on your server.  Please ask your web hosting provider for assistance.');
        return false;
      }

      if (!parent::install() || !$this->registerHook('paymentOptions')) {
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

  public function hookPaymentOptions($params)
  {

      $payment_options = [
          $this->linkToCryptoMkt(),
      ];
              
      return $payment_options;
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