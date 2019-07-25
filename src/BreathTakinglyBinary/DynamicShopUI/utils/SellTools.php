<?php


namespace BreathTakinglyBinary\DynamicShopUI\utils;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopManager;
use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
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


    /** @var DynamicShopUI */
    private $plugin;

    /** @var DynamicShopManager */
    private $manager;

    /** @var EconomyAPI */
    private $eco;

    public function __construct(DynamicShopUI $plugin){
        $this->plugin = $plugin;
        $this->manager = $this->plugin->getDynamicShopManager();
        $this->eco = $plugin->getEconomyAPI();
    }

    private function sendNoSaleMessage(Player $player, string $itemName = null){
        if($itemName !== null or strcmp(strtolower($itemName), "unknown") !== 0){
            $player->sendMessage("§eThe Shop can't buy §5$itemName §eright now.");
        }else{
            $player->sendMessage("§eThe Shop can't buy that item right now.");
        }
    }

    private function sendSoldMessage(Player $player, string $itemName, string $quantity, string $totalSale){
        $player->sendMessage("§bYou sold §a$quantity §bof §f$itemName §bfor §a$totalSale!");
    }

    public function sellHand(Player $player){
        $this->sellItem($player, $player->getInventory()->getHeldItemIndex());
    }

    public function sellAll(Player $player){
        $maxSlots = $player->getInventory()->getDefaultSize();
        for($slot = 0; $slot < $maxSlots; $slot++){
            $this->sellItem($player, $slot);
        }
    }

    private function sellItem(Player $player, int $index){
        $playerItem = $player->getInventory()->getItem($index);

        // Clearing the slot before processing the item keeps the player
        // from switching the item out while we're testing the copy.
        $player->getInventory()->clear($index);
        if($playerItem->getId() !== Item::AIR){
            if(($shopItem = $this->manager->getItemByItem($playerItem)) instanceof DSUItem){
                if($shopItem->canBuy() and ($buyPrice = $shopItem->getBuyPrice()) > 0){
                    $itemQuantity = $playerItem->getCount();
                    $totalSale = $buyPrice * $itemQuantity;
                    $this->eco->addMoney($player, $totalSale);
                    $this->sendSoldMessage($player, $playerItem->getName(), $itemQuantity, $totalSale);
                    return;
                }
            }
            $player->getInventory()->setItem($index, $playerItem);
            $this->sendNoSaleMessage($player, $playerItem->getName());
        }
    }

}