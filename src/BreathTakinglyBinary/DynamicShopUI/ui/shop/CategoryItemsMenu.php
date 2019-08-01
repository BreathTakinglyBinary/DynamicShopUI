<?php

declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\shop;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUElement;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use BreathTakinglyBinary\DynamicShopUI\utils\DynamicShopTransaction;
use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\MainLogger;

class CategoryItemsMenu extends SimpleForm{

    /** @var DSUCategory */
    private $currentCategory;

    /** @var DSUCategory */
    private $previousCategory;

    /** @var DSUElement[] */
    private $elements = [];

    public function __construct(DSUCategory $category, ?DSUCategory $previousCategory = null, $msg = ""){
        parent::__construct();
        $this->currentCategory = $category;
        $this->previousCategory = $previousCategory;
        $this->setTitle(DynamicShopUI::$shopName . "§2 - " . $category->getName());
        $this->setContent($msg);
        foreach($category->getCategories() as $DSUCategory){
            $this->addButton($DSUCategory->getName(), $DSUCategory->getName());
            $this->elements[$DSUCategory->getName()] = $DSUCategory;
        }
        foreach($category->getItems() as $DSUItem){
            $this->addButton($DSUItem->getName(), $DSUItem->getName());
            $this->elements[$DSUItem->getName()] = $DSUItem;
        }
    }

    public function onResponse(Player $player, $data) : void{
        if(!isset($this->elements[$data])){
            MainLogger::getLogger()->warning("ShopCategoryItemsMenu received invalid response of: $data");
            $player->sendMessage("§cSomething is wrong with your selection: $data");
            return;
        }
        $element = $this->elements[$data];
        if($element instanceof DSUCategory){
            $player->sendForm(new CategoryItemsMenu($element, $this->previousCategory));
            return;
        } elseif($element instanceof DSUItem){
            (new DynamicShopTransaction($player, $element))->startTransaction();
        }
    }

}