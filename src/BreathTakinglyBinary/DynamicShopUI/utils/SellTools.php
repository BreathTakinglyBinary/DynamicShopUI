<?php


namespace BreathTakinglyBinary\DynamicShopUI\utils;


use BreathTakinglyBinary\DynamicShopUI\data\DSUConfig;
use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use onebone\economyapi\EconomyAPI;
use pocketmine\item\Item;
use pocketmine\Player;

class SellTools{
	/**
	 * This contains all the methods for handling when a player sells an item to the shop.
	 *
	 * Things of importance to note: Items are intentionally cleared from the player's
	 * inventory before the transaction is completed.  This avoids the potential of players
	 * swapping an item before the transaction completes.
	 */


	/**
	 * @var DynamicShopUI
	 */
	private $plugin;

	/**
	 * @var DSUConfig
	 */
	private $settings;

	/**
	 * @var EconomyAPI
	 */
	private $eco;

	public function __construct(DynamicShopUI $plugin){
		$this->plugin = $plugin;
		$this->settings = $this->plugin->getSettings();
		$this->eco = $plugin->getEconomyAPI();
	}

	private function sendNoSaleMessage(Player $player, string $itemName = null){
		if($itemName !== null or strcmp(strtolower($itemName), "unknown") !== 0){
			$player->sendMessage("§eThe Shop can't buy §5$itemName §eright now.");
		} else {
			$player->sendMessage("§eThe Shop can't buy that item right now.");
		}
	}

	private function sendSoldMessage(Player $player, string $itemName, string $quantity, string $totalSale){
		$player->sendMessage("§bYou sold §a$quantity §bof §f$itemName §bfor §a$totalSale!");
	}

	public function sellHand(Player $player){
		$heldItem = $player->getInventory()->getItemInHand();
		$heldItemIndex = $player->getInventory()->getHeldItemIndex();

		// Clearing the slot before processing the item keeps the player
		// from switching the item out while we're testing the copy.
		$player->getInventory()->clear($heldItemIndex);
		if($heldItem->getId() !== Item::AIR){
			$heldItemName = $heldItem->getName();
			if($this->settings->isShopItem($heldItemName)){
				$shopItem = $this->settings->getItem($heldItemName);
				if(($price = $shopItem->getBuyPrice()) > 0){
					$heldItemQuantity = $heldItem->getCount();
					$totalSale = $price * $heldItemQuantity;
					$this->eco->addMoney($player, $totalSale);
					$this->sendSoldMessage($player, $heldItemName, $heldItemQuantity, $totalSale);
					return;
				}
			} else {
				$player->getInventory()->setItem($heldItemIndex, $heldItem);
				$this->sendNoSaleMessage($player, $heldItemName);
			}
		}

	}

	public function sellAll(Player $player){

		$maxSlots = $player->getInventory()->getDefaultSize();
		for($slot = 0; $slot < $maxSlots; $slot++){
			$inventoryItem = $player->getInventory()->getItem($slot);

			// Clearing the slot before processing the item keeps the player
			// from switching the item out while we're testing the copy.
			$player->getInventory()->clear($slot);

			if($inventoryItem->getId() !== Item::AIR){
				$inventoryItemName = $inventoryItem->getName();
				if($this->settings->isShopItem($inventoryItemName)){
					$shopItem = $this->settings->getItem($inventoryItemName);
					if(($price = $shopItem->getBuyPrice()) > 0){
						$inventoryItemQuantity = $inventoryItem->getCount();
						$totalSale = $price * $inventoryItemQuantity;
						$this->eco->addMoney($player, $totalSale);
						$this->sendSoldMessage($player, $inventoryItemName, $inventoryItemQuantity, $totalSale);
						$sold = true;
					}
				} else {
					$player->getInventory()->setItem($slot,$inventoryItem);
				}
			}
		}
	}

}