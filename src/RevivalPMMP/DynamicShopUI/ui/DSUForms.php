<?php

namespace RevivalPMMP\DynamicShopUI\ui;

use jojoe77777\FormAPI\FormAPI;
use RevivalPMMP\DynamicShopUI\data\DSUConfig;
use RevivalPMMP\DynamicShopUI\DynamicShopUI;

abstract class DSUForms{

	/**
	 * @var DynamicShopUI
	 */
	protected $plugin;

	/**
	 * @var FormAPI
	 */
	protected $formAPI;

	/**
	 * @var DSUConfig
	 */
	protected $dsuConfig;
	protected $options = [];
	protected $shopName;


	public function __construct(DynamicShopUI $plugin){
		$this->plugin = $plugin;
		$this->shopName = $plugin->getConfig()->get("ShopName");
		$this->formAPI = $plugin->getFormAPI();
		$this->dsuConfig = $plugin->getSettings();
	}
}