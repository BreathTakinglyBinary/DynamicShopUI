<?php

namespace BreathTakinglyBinary\DynamicShopUI\utils;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\FormAPI;
use jojoe77777\FormAPI\SimpleForm;
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
	 * @var FormAPI
	 */
    private $formAPI;

	/**
	 * @var EconomyAPI
	 */
    private $moneyAPI;

	/**
	 * @var DSUItem
	 */
    private $item;


    public function __construct(DynamicShopUI $plugin, Player $player, DSUItem $item){
        $this->plugin = $plugin;
        $this->player = $player;
        $this->item = $item;
		$this->formAPI = $this->plugin->getFormAPI();
        $this->moneyAPI = EconomyAPI::getInstance();
    }

    public function startTransaction(){
		$itemName = $this->item->getName();
		$itemID = $this->item->getID();
		$newItem = Item::get($itemID, $this->item->getMeta());
		$maxStack = $newItem->getMaxStackSize();
		$price = $this->item->getSellPrice();
		if($maxStack > 1){
			$form = new CustomForm([$this, "completeCustomTransaction"]);
			$form->setTitle("Buy $itemName - $price (each)");
			$form->addSlider("How many $itemName would you like to buy?  ($price each)", 1, $maxStack);
		}else{
			$form = new SimpleForm([$this, "completeSimpleTransaction"]);
			$form->setTitle("Buy $itemName for $price");
			$form->addButton("No");
			$form->addButton("Yes");
		}
		$this->player->sendForm($form);
		return;
	}

    public function completeCustomTransaction(Player $player, ?array $data){
        $result = $data;
        if($result === null){
        	return;
		}
        if ($result[0] > 0) {
            $money = $this->moneyAPI->myMoney($player);
            $totalPrice = $result[0] * $this->item->getSellPrice();
            if($money >= $totalPrice){
            	$this->moneyAPI->reduceMoney($player, $totalPrice);
				$newItem = ItemFactory::get($this->item->getID(), $this->item->getMeta(), $result[0]);
				if($player->getInventory()->canAddItem($newItem)){
					$player->getInventory()->addItem($newItem);
					$itemName = $newItem->getName();
					$player->sendMessage("You purchaced $result[0] x $itemName for $totalPrice.");
				} else {
					$this->moneyAPI->addMoney($player, $totalPrice);
					$player->sendMessage("You can't buy this item.  Your inventory is full!");
				}

			} else {
				$player->sendMessage("You don't have enough money");
			}
		}
        return;
    }

    public function completeSimpleTransaction(Player $player, $data){
		if($data === null){
			return;
		} elseif($data == 1){
			$money = $this->moneyAPI->myMoney($player);
			$price = $this->item->getSellPrice();
			if($money >= $price) {
				$this->moneyAPI->reduceMoney($player, $price);
				$newItem = ItemFactory::get($this->item->getID(), $this->item->getMeta(), 1);
				if($player->getInventory()->canAddItem($newItem)){
					$player->getInventory()->addItem($newItem);
					$itemName =$newItem->getName();
					$player->sendMessage("You bought $itemName for $price!");
				} else {
					$this->moneyAPI->addMoney($player, $price);
					$player->sendMessage("You can't buy this item.  Your inventory is full!");
				}

			} else {
				$player->sendMessage("You don't have enough money!");
			}
		} else {
			return;
		}
	}
}