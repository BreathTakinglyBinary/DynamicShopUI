<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\shop;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUElement;
use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\MainLogger;

class ShopMainForm extends SimpleForm{

    /** @var DSUElement[] */
    private $elements;

    /** @var DynamicShopUI */
    private $plugin;

    public function __construct(string $msg = ""){
        parent::__construct();
        $this->plugin = DynamicShopUI::getInstance();
        $this->setTitle(DynamicShopUI::$shopName . "§2 - Main");
        $this->setContent($msg);
        $this->elements = $this->plugin->getDynamicShopManager()->getTopLevelElements();
        foreach($this->elements as $element){
            $name = $element->getName();
            $img = $element->getImage();
            if($img !== null and $img !== ""){
                $this->addButton($name, $name, 1, $img);
            }else{
                $this->addButton($name, $name);
            }
        }

    }

    public function onResponse(Player $player, $data) : void{
        if(!isset($this->elements[$data])){
            MainLogger::getLogger()->error("ShopMainForm->onResponse() called with invalid option" .  $data);
            return;
        }

        $element = $this->elements[$data];
        if($element instanceof DSUCategory){
            $player->sendForm(new CategoryItemsMenu($element));
            return;
        }

    }

}