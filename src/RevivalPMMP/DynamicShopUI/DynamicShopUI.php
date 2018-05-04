<?php

namespace RevivalPMMP\DynamicShopUI;

use jojoe77777\FormAPI\FormAPI;
use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use RevivalPMMP\DynamicShopUI\commands\DSUShopCommand;
use RevivalPMMP\DynamicShopUI\commands\DSUMainCommand;
use RevivalPMMP\DynamicShopUI\commands\DSUSellCommand;
use RevivalPMMP\DynamicShopUI\data\DSUConfig;
use RevivalPMMP\DynamicShopUI\elements\DSUItem;
use RevivalPMMP\DynamicShopUI\ui\DSUManagementForms;
use RevivalPMMP\DynamicShopUI\ui\DSUShopForms;
use RevivalPMMP\DynamicShopUI\utils\DynamicShopTransaction;
use RevivalPMMP\DynamicShopUI\utils\SellTools;


class DynamicShopUI extends PluginBase {

    /**
     * @var $cfg DSUConfig
     */
    private $cfg;

    /**
     * @var $formAPI FormAPI
     */
    private $formAPI;

    /**
     * @var $moneyAPI EconomyAPI
     */
    private $moneyAPI;

	/**
	 * @var DSUManagementForms
	 */
    private $dsuManagementForms;

	/**
	 * @var DSUShopForms
	 */
	private $dsuShopForms;

	/**
	 * @var SellTools
	 */
	private $sellTools;

    public function onLoad(){

	}

	public function onEnable(){
		// Double Checking that FormAPI is available.
        if(($this->formAPI = $this->getServer()->getPluginManager()->getPlugin("FormAPI")) === null
		    or $this->formAPI->isDisabled()){
        	$this->getLogger()->warning("FormAPI is not available.  Disabling DynamicShopUI");
        	$this->setEnabled(false);
		}

		// Double Checking that EconomyAPI is available.
		if(($this->moneyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")) === null
			or $this->moneyAPI->isDisabled()){
			$this->getLogger()->warning("EconomyAPI is not available.  Disabling DynamicShopUI");
			$this->setEnabled(false);
		}

		$this->cfg = new DSUConfig($this);

		$this->dsuManagementForms = new DSUManagementForms($this);
		$this->dsuShopForms = new DSUShopForms($this);
		$this->sellTools = new SellTools($this);

		$this->registerCommands();
        $this->getLogger()->info(TextFormat::GREEN . "enabled.");
    }

    private function registerCommands() {
    	$this->getServer()->getCommandMap()->registerAll("dynamicshopui", [
			new DSUMainCommand($this),
    		new DSUShopCommand($this),
			new DSUSellCommand($this),
    		]);
	}

	public function getFormAPI(): FormAPI {
    	return $this->formAPI;
	}

	public function getEconomyAPI(): EconomyAPI{
    	return $this->moneyAPI;
	}

	public function getDSUManagementForms() {
    	return $this->dsuManagementForms;
	}

	public function getDSUShopForms() {
    	return $this->dsuShopForms;
	}

	public static function sendNoShopMessage(Player $player) {
        $player->sendMessage("Not Available. Check FormAPI.");
    }

	/**
	 * @return DSUConfig
	 */
	public function getSettings() {
		return $this->cfg;
	}

	public function getSellTools(): SellTools{
		return $this->sellTools;
	}

	public function onDisable(){
		$this->cfg->saveShopData();
		$this->getLogger()->info(TextFormat::RED . "disabled.");
	}
}
