<?php

namespace BreathTakinglyBinary\DynamicShopUI\ui;

use BreathTakinglyBinary\DynamicShopUI\data\DSUConfig;
use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use jojoe77777\FormAPI\FormAPI;

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