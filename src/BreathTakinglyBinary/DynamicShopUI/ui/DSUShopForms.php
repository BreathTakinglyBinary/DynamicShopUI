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

class DSUShopForms extends DSUForms {

	const CHOICES_KEY = "choices";

	public function shopMainForm(Player $player){
		/**
		 * @var DSUCategory[] $categories;
		 */
		$categories = [];
		foreach($this->dsuConfig->getAllCategories() as $category){
			$parents = $category->getAllParents();
			if(count($parents) == 0){
				array_push($categories, $category);
			}
		}

		if(!empty($categories)){
			$form = new SimpleForm([$this, "shopFormHandler"]);
			$form->setTitle($this->shopName . "§2 - Main");
			foreach($categories as $menuItem){
				$name = $menuItem->getName();
				$img = $menuItem->getImage();
				if($img !== null and $img !== ""){
					$form->addButton($name, 1, $img);
				} else {
					$form->addButton($name);
				}
			}
			$this->options[$player->getUniqueId()->toString()][self::CHOICES_KEY] = $categories;
			$this->options[$player->getUniqueId()->toString()]["previous"] = "main";
			$player->sendForm($form);
		} else {
			$player->sendMessage("The shop is currently closed.");
			DynamicShopUI::getInstance()->getLogger()->alert(__METHOD__ . " called with no items available in the shop.");
		}
	}

	public function shopFormHandler(Player $player, $data){
		if($data === null){
			return;
		}
		var_dump($data);
		if($data >= count($this->options[$player->getUniqueId()->toString()][self::CHOICES_KEY])) {
			// Do the back thing
			$previous = $this->options[$player->getUniqueId()->toString()]["previous"];
			if($this->plugin->getSettings()->isCategory($previous)){
				$selection = $this->plugin->getSettings()->getCategory($previous);
			} else {
			    var_dump("Sending Main Form");
				$this->shopMainForm($player);
				return;
			}
		} else {
			$selection = $this->options[$player->getUniqueId()->toString()][self::CHOICES_KEY][$data];
		}

		if($selection instanceof DSUCategory){
			$menuItems = [];
			$categories = $selection->getCategories();
			$items = $selection->getItems();
			if($categories !== null and count($categories) > 0){
				foreach($categories as $category){
					array_push($menuItems, $category);
				}
			}
			if($items !== null and count($items) > 0){
				foreach($items as $item){
					array_push($menuItems, $item);
				}
			}
			if(!empty($menuItems)){
				$this->options[$player->getUniqueId()->toString()][self::CHOICES_KEY] = $menuItems;
				$this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY] = $selection->getName();
				$this->buildMenu($player, $menuItems);
				return;
			}

		} elseif($selection instanceof DSUItem){
			$transaction = new DynamicShopTransaction($this->plugin, $player, $selection);
			$transaction->startTransaction();
			return;
		} else {
			$player->sendMessage("Selection not recognized.");
		}
		$this->shopMainForm($player);
	}

	/**
	 * @param Player 		$player
	 * @param DSUElement[]  $menuItems
	 */
	public function buildMenu(Player $player, $menuItems){

		if(!empty($menuItems)){
			$form = new SimpleForm([$this, "shopFormHandler"]);
			$menuName = $this->shopName . "§2 - " . $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY];
			$form->setTitle($menuName);
			$choices = [];
			foreach($menuItems as $option){
				if($option instanceof DSUCategory){
					array_push($choices, $option);
					$name = $option->getName() . " (Category)";

				} elseif( $option instanceof DSUItem) {
					array_push($choices, $option);
					$name = $option->getName() .  " " . $option->getSellPrice();
				}
				$img = $option->getImage();
				if($img !== null and $img !== ""){
					$form->addButton($name, 1, $img);
				} else {
					$form->addButton($name);
				}
			}
			$form->addButton("Back");
			$player->sendForm($form);

		} else {
			$player->sendMessage("§r Sorry, that menu can't be created right now.");
			$this->plugin->getLogger()->error("buildMenu called with no Menu Items.");
		}
	}

}