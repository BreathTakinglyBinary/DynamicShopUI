<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\Player;

class ManagementMainForm extends SimpleForm implements FormKeys{

    public function __construct(string $msg = "", bool $allowConfigChanges = false){
        parent::__construct();
        $this->setTitle(DynamicShopUI::$shopName . " - Manage");
        $this->setContent($msg);
        $this->addButton("Update Items", self::ITEM);
        $this->addButton("Update Categories", self::CATEGORY);
        if($allowConfigChanges){
            $this->addButton("Update DynamicShopUI Settings", self::SETTINGS);
        }
    }

    public function onResponse(Player $player, $data) : void{
        switch($data){
            case self::ITEM:
                $player->sendForm(new UpdateItemOptionsForm());
                break;
            case self::CATEGORY:
                $player->sendForm(new UpdateCategoriesOptionsForm());
                break;
            case self::SETTINGS:
                $player->sendMessage("Update Settings Feature is not available yet.");
                break;
            default:
                DynamicShopUI::getInstance()->getLogger()->error("ManagementMainForm received unknown input \"$data\" from player " . $player->getName() . ".");
                $player->sendMessage("Selection not recognized. Please try again.");
        }
    }

}