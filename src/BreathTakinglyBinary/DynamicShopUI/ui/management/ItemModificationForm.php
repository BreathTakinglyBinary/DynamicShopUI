<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\libDynamicForms\CustomForm;
use pocketmine\Player;

class ItemModificationForm extends CustomForm implements FormKeys{

    /** @var DSUItem */
    private $item;

    /** @var DSUCategory[] */
    private $availableCategories = [];

    /** @var string[] */
    private $parents = [];

    public function __construct(DSUItem $DSUItem){
        parent::__construct();
        $this->item = $DSUItem;

        $itemName = $DSUItem->getName();
        $sellPrice = $DSUItem->getSellPrice();
        $buyPrice = $DSUItem->getBuyPrice();
        $imageURL = $DSUItem->getImage();
        $parents = [""];
        $this->parents[] = null;
        foreach($DSUItem->getAllParents() as $parentName => $category){
            $parents[] = $parentName;
            $this->parents[] = $parentName;
        }
        $categories = [""];
        foreach(DynamicShopUI::getInstance()->getDynamicShopManager()->getCategories() as $categoryName => $category){
            if(!in_array($categoryName, $parents)){
                $categories[] = $categoryName;
                $this->availableCategories[] = $category;
            }
        }
        $this->setTitle("§5§lModify Item - §f$itemName");
        $this->addToggle("Can Sell", self::CAN_SELL, $DSUItem->canSell());
        $this->addInput("Sell Price", self::PRICE_SELL, (string) $sellPrice, (string) $sellPrice);
        $this->addToggle("Can Buy", self::CAN_BUY, $DSUItem->canBuy());
        $this->addInput("Buy Price", self::PRICE_BUY, (string) $buyPrice, (string) $buyPrice);
        $this->addInput("Image URL", self::IMG_LOCATION, $imageURL, $DSUItem->getImage());
        $this->addDropdown("Remove Parent", self::PARENTS_REMOVE , $parents);
        $this->addDropdown("Add Parent", self::PARENTS_ADD, $categories);
        
    }

    public function onResponse(Player $player, $data) : void{
        $changes = false;
        $msg = "";
        if(isset($data[self::CAN_SELL]) and $data[self::CAN_SELL] !== $this->item->canSell()){
            $this->item->enableSelling((bool) $data[self::CAN_SELL]);
            $changes = true;
        }
        if(isset($data[self::PRICE_SELL]) and $data[self::PRICE_SELL] !== $this->item->getSellPrice()){
            $this->item->setSellPrice((float) $data[self::PRICE_SELL]);
            $changes = true;
        }
        if(isset($data[self::CAN_BUY]) and $data[self::CAN_BUY] !== $this->item->canBuy()){
            $this->item->enableBuying((bool) $data[self::CAN_BUY]);
            $changes = true;
        }
        if(isset($data[self::PRICE_BUY]) and $data[self::PRICE_BUY] !== $this->item->getBuyPrice()){
            $this->item->setBuyPrice((float) $data[self::PRICE_BUY]);
            $changes = true;
        }
        if(isset($data[self::IMG_LOCATION]) and $data[self::IMG_LOCATION] !== $this->item->getImage()){
            $this->item->setImage($data[self::IMG_LOCATION]);
            $changes = true;
        }
        if(isset($data[self::PARENTS_REMOVE]) and $data[self::PARENTS_REMOVE] > 0){
            $this->item->removeParentByName((string) $data[self::PARENTS_REMOVE]);
            $changes = true;
        }
        if(isset($data[self::PARENTS_ADD])){
            $index = $data[self::PARENTS_ADD] - 1;
            if($index > 0 and isset($this->availableCategories[$index])){
                $this->item->addParent($this->availableCategories[$index]);
                $changes = true;
            }
        }

        if($changes){
            DynamicShopUI::getInstance()->getDynamicShopManager()->updateElement($this->item);
            $msg = "§aItem Updated Successfully!";
        }

        $player->sendForm(new UpdateItemOptionsForm($msg));
    }
}