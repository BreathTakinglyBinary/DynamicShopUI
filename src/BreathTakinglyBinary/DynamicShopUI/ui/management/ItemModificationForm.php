<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\libDynamicForms\CustomForm;
use pocketmine\Player;

class ItemModificationForm extends CustomForm implements FormKeys{

    /** @var DSUItem */
    private $DSUItem;

    public function __construct(DSUItem $DSUItem){
        parent::__construct();
        $this->DSUItem = $DSUItem;

        $itemName = $DSUItem->getName();
        $sellPrice = $DSUItem->getSellPrice();
        $buyPrice = $DSUItem->getBuyPrice();
        $imageURL = $DSUItem->getImage();
        $parents = [""];
        foreach($DSUItem->getAllParents() as $parent => $data){
            array_push($parents, $parent);
        }
        $categories = [];
        foreach(DynamicShopUI::getInstance()->getDynamicShopManager()->getCategories() as $category => $data){
            if(!in_array($category, $parents)){
                $categories[] = $category;
            }
        }
        $this->setTitle("§5§lModify Item - §f$itemName");
        $this->addToggle("Can Sell", self::CAN_SELL, $DSUItem->canSell());
        $this->addInput("Sell Price", self::PRICE_SELL, (string) $sellPrice);
        $this->addToggle("Can Buy", self::CAN_BUY, $DSUItem->canBuy());
        $this->addInput("Buy Price", self::PRICE_BUY, (string) $buyPrice);
        $this->addInput("Image URL", self::IMG_LOCATION, $imageURL);
        $this->addDropdown("Remove Parent", self::PARENTS_REMOVE , $parents);
        $this->addDropdown("Add Parent", self::PARENTS_ADD, $categories);
        
    }

    public function onResponse(Player $player, $data) : void{
        $changes = false;
        $msg = "";
        if(isset($data[self::CAN_SELL])){
            $this->DSUItem->enableSelling((bool) $data[self::CAN_SELL]);
            $changes = true;
        }
        if(isset($data[self::PRICE_SELL])){
            $this->DSUItem->setSellPrice($data[self::PRICE_SELL]);
            $changes = true;
        }
        if(isset($data[self::CAN_BUY])){
            $this->DSUItem->enableBuying($data[self::CAN_BUY]);
            $changes = true;
        }
        if(isset($data[self::PRICE_BUY])){
            $this->DSUItem->setBuyPrice($data[self::PRICE_BUY]);
            $changes = true;
        }
        if(isset($data[self::IMG_LOCATION])){
            $this->DSUItem->setImage($data[self::IMG_LOCATION]);
            $changes = true;
        }
        if(isset($data[self::PARENTS_REMOVE])){
            $this->DSUItem->removeParent($data[self::PARENTS_REMOVE]);
            $changes = true;
        }
        if(isset($data[self::PARENTS_ADD])){
            $this->DSUItem->addParent($data[self::PARENTS_ADD]);
            $changes = true;
        }

        if($changes){
            $msg = "§aItem Updated Successfully!";
        }
        $player->sendForm(new UpdateItemOptionsForm($msg));
    }
}