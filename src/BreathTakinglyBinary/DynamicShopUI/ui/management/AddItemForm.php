<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\libDynamicForms\CustomForm;
use pocketmine\item\Item;
use pocketmine\Player;

class AddItemForm extends CustomForm implements FormKeys{

    private $categories = [];
    private $categoryKeys = [];

    public function __construct(){
        parent::__construct();

        foreach(DynamicShopUI::getInstance()->getDynamicShopManager()->getCategories() as $DSUCategory){
            $this->categories[$DSUCategory->getName()] = $DSUCategory;
            $this->categoryKeys[] = $DSUCategory->getName();
        }
        $this->setTitle("Item - Add");
        $this->addInput("Item ID", self::ITEM_ID, "236", "-1");
        $this->addInput("Meta", self::ITEM_META,"10", "0");
        $this->addDropdown("Parent", self::PARENTS, $this->categoryKeys);
        $this->addToggle("Sell to players? (No / Yes)", self::CAN_SELL, true);
        $this->addInput("Sell Price", self::PRICE_SELL, "100", "100");
        $this->addInput("Image URL",self::IMG_LOCATION, "http://yoursite.com/imagefile.png");
        $this->addToggle("Buy from players? (No / Yes)",self::CAN_BUY, false);
        $this->addInput("Buy Price (each)", self::PRICE_BUY, "10");
    }

    public function onResponse(Player $player, $data) : void{

        if(!is_numeric($data[self::ITEM_ID]) or (int) $data[self::ITEM_ID] < 1){
            if((int) $data[self::ITEM_ID] === 0){
                $msg = "Can't add AIR to the shop.";
            } else {
                $msg = "§cInvalid Item ID";
            }

            $player->sendForm(new ManagementMainForm($msg, $player->hasPermission("dsu.configuration")));
            return;
        }

        $itemId = (int) $data[self::ITEM_ID];
        $itemMeta = 0;
        if(is_numeric($data[self::ITEM_META]) and $data[self::ITEM_ID] > 0){
            $itemMeta = $data[self::ITEM_ID];
        }
        $item = Item::get($itemId, $itemMeta);
        if((DynamicShopUI::getInstance()->getDynamicShopManager()->getItemById($itemId, $itemMeta)) instanceof DSUItem){
            $player->sendForm(new ManagementMainForm($item->getName() . " was already registered.", $player->hasPermission("dsu.configuration")));
            return;
        }

        $canSell = false;
        if(isset($data[self::CAN_SELL])){
            $canSell =  (bool) $data[self::CAN_SELL];
        }

        $sellPrice = 0.0;
        if(isset($data[self::PRICE_SELL]) and is_numeric($data[self::PRICE_SELL])){
            $sellPrice = $data[self::PRICE_SELL];
        }

        $imgUrl = "";
        if(isset($data[self::IMG_LOCATION])){
            $imgUrl = $data[self::IMG_LOCATION];
        }

        $canBuy = false;
        if(isset($data[self::CAN_BUY])){
            $canSell = $data[self::CAN_BUY];
        }

        $buyPrice = 0.0;
        if(isset($data[self::PRICE_BUY])){
            $buyPrice = $data[self::PRICE_BUY];
        }


        $newItem = new DSUItem($item->getName(), $itemId, $itemMeta, $buyPrice, $sellPrice, $imgUrl, $canBuy, $canSell);

        DynamicShopUI::getInstance()->getDynamicShopManager()->addItem($newItem);

    }

}