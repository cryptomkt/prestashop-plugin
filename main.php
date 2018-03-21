<?php
if( !defined('_PS_VERSION_'))
exit;

class Main extends Module{

	/**
	 * [__construct define details of module]
	 */
	public function __construct()
	{
		$this->name = 'main';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'CryptoMarket Development Team support@cryptomkt.com';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => , '1.5.x.x', 'max' => _PS_VERSION_);
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('CryptoMarket Pago');
		$this->description = $this->l('Integrate cryptocurrencies into Prestashop and welcome to the new way for payments. Simple, Free and totally Secure.');
		$this->confirmUninstall = $this->l('Would you like uninstall this plugin?');

		$this->templateFile = 'module::main/views/templates/hook/main.tpl';
	}

	public function install()
	{
		return (parent::install()
			&& $this->registerHook('displayHeader')
			&& $this->registerHook('displayCarrierExtraContent')
			);

		$this->emptyTemplatesCache();

		return (bool) $return;
	}

	public function hookDisplayHeader($params)
	{
		$this->context->controller->registerStylesheet('modules-main','modules/'.$this->name.'/views/css/style.css',['media' => 'all', 'priority' => 150]);	
		$this->context->controller->registerJavascript('modules-main','modules/'.$this->name.'/views/js/main.js',['position' => 'bottom', 'priority' => 150]);	
	}

	public function uninstall()
	{
		$this->clearCache('*');

		if( !parent::uninstall() || !$this->unregisterHook('displayHome'))
			return false;

		return true;
	}

	public function hookDisplayHome()
	{
		return $this->display(__FILE__, 'views/templates/hook/main.tpl');
	}
}
?>