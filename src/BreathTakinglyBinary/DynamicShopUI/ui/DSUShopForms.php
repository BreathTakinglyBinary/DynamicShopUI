<?php

namespace BreathTakinglyBinary\DynamicShopUI\ui;

use BreathTakinglyBinary\DynamicShopUI\data\DataKeys;
use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUElement;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use BreathTakinglyBinary\DynamicShopUI\utils\DynamicShopTransaction;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;

class DSUShopForms extends DSUForms{

    const CHOICES_KEY = "choices";

    public function shopMainForm(Player $player, string $msg = ""){
        if(!empty(($elements = $this->manager->getTopLevelElements()))){
            $form = new SimpleForm([$this, "shopFormHandler"]);
            $form->setTitle($this->shopName . "§2 - Main");
            if($msg !== ""){
                $form->setContent($msg);
            }
            foreach($elements as $menuItem){
                $name = $menuItem->getName();
                $img = $menuItem->getImage();
                if($img !== null and $img !== ""){
                    $form->addButton($name, 1, $img);
                }else{
                    $form->addButton($name);
                }
            }
            $this->options[$player->getUniqueId()->toString()][self::CHOICES_KEY] = $elements;
            $this->options[$player->getUniqueId()->toString()]["previous"] = "main";
            $player->sendForm($form);
        }else{
            $player->sendMessage("The shop is currently closed.");
            DynamicShopUI::getInstance()->getLogger()->alert(__METHOD__ . " called with no items available in the shop.");
        }
    }

    public function shopFormHandler(Player $player, $data){
        if($data === null){
            return;
        }
        if($data >= count($this->options[$player->getUniqueId()->toString()][self::CHOICES_KEY])){
            $this->showPreviousMenu($player);
            return;
        }else{
            $selection = $this->options[$player->getUniqueId()->toString()][self::CHOICES_KEY][$data];
        }
        if($selection instanceof DSUCategory){
            $menuItems = $this->getCategoryMenuItems($selection);
            if(!empty($menuItems)){
                $this->options[$player->getUniqueId()->toString()][self::CHOICES_KEY] = $menuItems;
                $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY] = $selection->getName();
                $this->buildMenu($player, $menuItems);
                return;
            }
            $this->showPreviousMenu($player, "Category " . $selection->getName() . " is empty.");
            return;

        }elseif($selection instanceof DSUItem){
            $transaction = new DynamicShopTransaction($this->plugin, $player, $selection);
            $transaction->startTransaction();

            return;
        }else{
            $player->sendMessage("Selection not recognized.");
            return;
        }
    }

    private function getCategoryMenuItems(DSUCategory $parentCategory){
        $menuItems = [];
        $categories = $this->manager->getCategoriesByParent($parentCategory->getName());
        if(is_array($categories)){
            foreach($categories as $category){
                $menuItems[] = $category;
            };
        }

        $items = $this->manager->getItemsByParent($parentCategory->getName());
        if(is_array($items)){
            foreach($items as $item){
                if($item->canSell()){
                    $menuItems[] = $item;
                }
            }
        }
        return $menuItems;
    }

    /**
     * @param Player $player
     * @param array  $menuItems
     * @param string $msg
     *
     * @throws \InvalidArgumentException
     */
    public function buildMenu(Player $player, array $menuItems, string $msg = ""){
        if(!empty($menuItems)){
            $form = new SimpleForm([$this, "shopFormHandler"]);
            $menuName = $this->shopName . "§2 - " . $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY];
            $form->setTitle($menuName);
            if($msg !== ""){
                $form->setContent($msg);
            }
            $choices = [];
            foreach($menuItems as $option){
                if($option instanceof DSUCategory){
                    array_push($choices, $option);
                    $name = $option->getName() . " (Category)";

                }elseif($option instanceof DSUItem){
                    array_push($choices, $option);
                    $name = $option->getName() . " " . $option->getSellPrice();
                } else {
                    throw new \InvalidArgumentException(__METHOD__ . "requires that argument 2 only contain DSUElement objects, " . get_class($option) . " found");
                }

                $img = $option->getImage();
                if($img !== null and $img !== ""){
                    $form->addButton($name, 1, $img);
                }else{
                    $form->addButton($name);
                }
            }
            $form->addButton("Back");
            $player->sendForm($form);

        }else{
            $player->sendMessage("§r Sorry, that menu can't be created right now.");
            $this->plugin->getLogger()->error("buildMenu called with no Menu Items.");
        }
    }

    private function showPreviousMenu(Player $player, $msg = ""){
        $previous = $this->options[$player->getUniqueId()->toString()]["previous"];
        if(!($selection = $this->manager->getCategoryByName($previous)) instanceof DSUCategory){
            $this->shopMainForm($player, $msg);
            return;
        } else {
            $menuItems = $this->getCategoryMenuItems($selection);
            $this->buildMenu($player, $menuItems, $msg);
        }
    }

}