<?php

namespace BreathTakinglyBinary\DynamicShopUI;

use BreathTakinglyBinary\DynamicShopUI\commands\DSUMainCommand;
use BreathTakinglyBinary\DynamicShopUI\commands\DSUSellCommand;
use BreathTakinglyBinary\DynamicShopUI\commands\DSUShopCommand;
use BreathTakinglyBinary\DynamicShopUI\ui\DSUManagementForms;
use BreathTakinglyBinary\DynamicShopUI\ui\DSUShopForms;
use BreathTakinglyBinary\DynamicShopUI\utils\SellTools;
use jojoe77777\FormAPI\FormAPI;
use onebone\economyapi\EconomyAPI;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;


class DynamicShopUI extends PluginBase{

    /** @var  DynamicShopUI */
    private static $instance;

    /** @var DynamicShopManager */
    private $dynamicShopManager;

    /** @var FormAPI */
    private $formAPI;

    /** @var EconomyAPI */
    private $moneyAPI;

    /** @var DSUManagementForms */
    private $dsuManagementForms;

    /** @var DSUShopForms */
    private $dsuShopForms;

    /** @var SellTools */
    private $sellTools;

    public function onEnable(){
        TextFormat::GOLD;
        self::$instance = $this;
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

        $this->dynamicShopManager = new DynamicShopManager($this);

        $this->dynamicShopManager->loadShopData();

        $this->dsuManagementForms = new DSUManagementForms($this);
        $this->dsuShopForms = new DSUShopForms($this);
        $this->sellTools = new SellTools($this);

        $this->registerCommands();
        $this->getLogger()->info(TextFormat::GREEN . "enabled.");
    }

    public static function getInstance() : DynamicShopUI{
        return self::$instance;
    }

    private function registerCommands(){
        $this->getServer()->getCommandMap()->registerAll("dynamicshopui", [
            new DSUMainCommand($this),
            new DSUShopCommand($this),
            new DSUSellCommand($this),
        ]);
    }

    /**
     * @return DynamicShopManager
     */
    public function getDynamicShopManager() : DynamicShopManager{
        return $this->dynamicShopManager;
    }

    public function getFormAPI() : FormAPI{
        return $this->formAPI;
    }

    public function getEconomyAPI() : EconomyAPI{
        return $this->moneyAPI;
    }

    public function getDSUManagementForms(){
        return $this->dsuManagementForms;
    }

    public function getDSUShopForms(){
        return $this->dsuShopForms;
    }

    public static function sendNoShopMessage(Player $player){
        $player->sendMessage("Not Available. Check FormAPI.");
    }

    public function getSellTools() : SellTools{
        return $this->sellTools;
    }

    public function onDisable(){
        $this->dynamicShopManager->saveShopData();
        $this->getLogger()->info(TextFormat::RED . "disabled.");
    }
}
