<?php

namespace BreathTakinglyBinary\DynamicShopUI\ui;

use BreathTakinglyBinary\DynamicShopUI\DynamicShopManager;
use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use jojoe77777\FormAPI\FormAPI;

abstract class DSUForms{

    /** @var DynamicShopUI */
    protected $plugin;

    /** @var FormAPI */
    protected $formAPI;

    /** @var DynamicShopManager */
    protected $manager;

    /** @var array */
    protected $options = [];

    /** @var bool|mixed  */
    protected $shopName;


    /**
     * DSUForms constructor.
     *
     * @param DynamicShopUI $plugin
     */
    public function __construct(DynamicShopUI $plugin){
        $this->plugin = $plugin;
        $this->shopName = $plugin->getConfig()->get("ShopName");
        $this->manager = $plugin->getDynamicShopManager();
        $this->formAPI = $plugin->getFormAPI();
    }
}