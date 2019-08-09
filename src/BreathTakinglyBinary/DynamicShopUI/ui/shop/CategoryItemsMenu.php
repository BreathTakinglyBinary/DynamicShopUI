<?php

declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\shop;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUElement;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\DynamicShopUI\utils\DynamicShopTransaction;
use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\MainLogger;

class CategoryItemsMenu extends SimpleForm implements FormKeys{

    /** @var DSUCategory */
    private $currentCategory;

    /** @var DSUElement[] */
    private $elements = [];

    /** @var DSUCategory[] */
    private $history = [];

    public function __construct(DSUCategory $category, $categoryHistory = [], $msg = ""){
        parent::__construct();
        $this->currentCategory = $category;
        $this->history = $categoryHistory;
        $this->setTitle(DynamicShopUI::$shopName . "§2 - " . $category->getName());
        $this->setContent($msg);
        foreach($category->getCategories() as $DSUCategory){
            $this->addButton("§l§5" . $DSUCategory->getName(), $DSUCategory->getName());
            $this->elements[$DSUCategory->getName()] = $DSUCategory;
        }
        foreach($category->getItems() as $DSUItem){
            if($DSUItem->canSell()){

                $this->addButton($DSUItem->getName() . "§r§8 - (§7 " . $DSUItem->getSellPrice() . " §r§8)", $DSUItem->getName());
                $this->elements[$DSUItem->getName()] = $DSUItem;
            }
        }
        $this->addButton("Back", self::BACK);
    }

    public function onResponse(Player $player, $data) : void{
        $msg = "";
        if($data === self::BACK){
            $indexes = count($this->history);
            if($indexes > 0){
                $category = array_pop($this->history);
                if($category instanceof DSUCategory){
                    $player->sendForm(new CategoryItemsMenu($category, $this->history));
                    return;
                } else{
                    DynamicShopUI::getInstance()->getLogger()->error("Found unknown category in category history of CategoryItemsMenu");
                }
            }
            $player->sendForm(new ShopMainForm());
            return;
        }
        if(!isset($this->elements[$data])){
            MainLogger::getLogger()->warning("ShopCategoryItemsMenu received invalid response of: $data");
            $player->sendMessage("§cSomething is wrong with your selection: $data");
            return;
        }
        $element = $this->elements[$data];
        if($element instanceof DSUCategory){
            $this->history[] = $this->currentCategory;
            $player->sendForm(new CategoryItemsMenu($element, $this->history));
            return;
        } elseif($element instanceof DSUItem){
            (new DynamicShopTransaction($player, $element))->startTransaction();
        }
    }

}