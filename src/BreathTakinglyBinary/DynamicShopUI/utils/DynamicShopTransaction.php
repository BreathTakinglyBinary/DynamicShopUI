<?php

namespace BreathTakinglyBinary\DynamicShopUI\utils;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use BreathTakinglyBinary\DynamicShopUI\ui\shop\ChooseItemQuantityForm;
use BreathTakinglyBinary\DynamicShopUI\ui\shop\ConfirmPurchaseForm;
use onebone\economyapi\EconomyAPI;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;

class DynamicShopTransaction{

    /**
     * @var $plugin DynamicShopUI
     */
    private $plugin;

    /**
     * @var $player Player
     */
    private $player;

    /**
     * @var EconomyAPI
     */
    private $moneyAPI;

    /**
     * @var DSUItem
     */
    private $item;


    public function __construct(Player $player, DSUItem $item){
        $this->plugin = DynamicShopUI::getInstance();
        $this->player = $player;
        $this->item = $item;
        $this->moneyAPI = EconomyAPI::getInstance();
    }

    /**
     * @return DSUItem
     */
    public function getItem() : DSUItem{
        return $this->item;
    }

    public function startTransaction(){
        $itemName = $this->item->getName();
        $itemID = $this->item->getID();
        $newItem = Item::get($itemID, $this->item->getMeta());
        $maxStack = $newItem->getMaxStackSize();
        $price = $this->item->getSellPrice();
        if($maxStack > 1){
            $form = new ChooseItemQuantityForm($this, $itemName, $price, $maxStack);
        }else{
            $form = new ConfirmPurchaseForm($this, $itemName, $price);
        }
        $this->player->sendForm($form);

        return;
    }

    public function completeTransaction(Player $player, int $qty, float $totalPrice){
        $money = $this->moneyAPI->myMoney($player);
        if($money >= $totalPrice){
            $this->moneyAPI->reduceMoney($player, $totalPrice);
            $newItem = ItemFactory::get($this->item->getID(), $this->item->getMeta(), $qty);
            if($player->getInventory()->canAddItem($newItem)){
                $player->getInventory()->addItem($newItem);
                $itemName = $newItem->getName();
                $player->sendMessage("You bought $itemName for $totalPrice!");
            }else{
                $this->moneyAPI->addMoney($player, $totalPrice);
                $player->sendMessage("You can't buy this item.  Your inventory is full!");
            }

        }else{
            $player->sendMessage("You don't have enough money!");
        }
    }
}