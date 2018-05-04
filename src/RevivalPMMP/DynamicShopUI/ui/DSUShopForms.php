<?php

namespace RevivalPMMP\DynamicShopUI\ui;

use pocketmine\Player;
use RevivalPMMP\DynamicShopUI\data\DataKeys;
use RevivalPMMP\DynamicShopUI\elements\DSUCategory;
use RevivalPMMP\DynamicShopUI\elements\DSUElement;
use RevivalPMMP\DynamicShopUI\elements\DSUItem;
use RevivalPMMP\DynamicShopUI\utils\DynamicShopTransaction;

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
			$form = $this->formAPI->createSimpleForm([$this, "shopFormHandler"]);
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
			$form->sendToPlayer($player);
		} else {
			$player->sendMessage("No main categories found.");
		}
	}

	public function shopFormHandler(Player $player, array $data){
		$result = $data[0];

		if($result === null){
			return;
		}
		if($result >= count($this->options[$player->getUniqueId()->toString()][self::CHOICES_KEY])) {
			// Do the back thing
			$previous = $this->options[$player->getUniqueId()->toString()]["previous"];
			if($this->plugin->getSettings()->isCategory($previous)){
				$selection = $this->plugin->getSettings()->getCategory($previous);
			} else {
				$this->shopMainForm($player);
				return;
			}
		} else {
			$selection = $this->options[$player->getUniqueId()->toString()][self::CHOICES_KEY][$result];
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
			$form = $this->formAPI->createSimpleForm([$this, "shopFormHandler"]);
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
			$form->sendToPlayer($player);

		} else {
			$player->sendMessage("§r Sorry, that menu can't be created right now.");
			$this->plugin->getLogger()->error("buildMenu called with no Menu Items.");
		}
	}

}