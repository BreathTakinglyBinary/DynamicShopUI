<?php

namespace BreathTakinglyBinary\DynamicShopUI\data;

use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;

class DSUConfig{

	const SHOP_DATA_FILE_NAME = "shopData.yml";

	private $categories = [];
	private $items = [];
	private $plugin;
	private $settings;
	private $shopData;


	public function __construct(DynamicShopUI $plugin){
		$this->plugin = $plugin;
		$plugin->saveResource(DSUConfig::SHOP_DATA_FILE_NAME);
		$plugin->saveDefaultConfig();
		$this->settings = $plugin->getConfig();
		$this->reload();
	}

	private function populateCategories() {
		foreach($this->shopData[DataKeys::SHOP_DATA_CATEGORIES_KEY] as $category => $data){
			$img = $data[DataKeys::SHOP_DATA_IMAGE_KEY];
			$this->categories[$category] = new DSUCategory($this, $category, $img);
		}
	}
	private function populateItems() {
		foreach($this->shopData[DataKeys::SHOP_DATA_ITEMS_KEY] as $item => $data) {
			$this->plugin->getLogger()->info("DSU - Loading Item $item");
			$id = $data{DataKeys::SHOP_DATA_ITEM_ID_KEY};
			$meta = $data{DataKeys::SHOP_DATA_ITEM_META_KEY};
			$buyPrice = $data{DataKeys::SHOP_DATA_ITEM_BUY_PRICE_KEY};
			$sellPrice = $data{DataKeys::SHOP_DATA_ITEM_SELL_PRICE_KEY};
			$imageURL = $data[DataKeys::SHOP_DATA_IMAGE_KEY];
			$this->items[$item] = new DSUItem($this, $item, $id, $meta, $buyPrice, $sellPrice, $imageURL);
		}
	}

	private function mapParents() {

		foreach($this->shopData[DataKeys::SHOP_DATA_CATEGORIES_KEY] as $category => $data){
			if(isset($this->categories[$category])){
				/**
				 * @var DSUCategory $dsuCategory;
				 */
				$dsuCategory = $this->categories[$category];
				foreach($data[DataKeys::SHOP_DATA_PARENTS_KEY] as $parent){
					if(isset($this->categories[$parent])) {
						/**
						 * @var DSUCategory $categoryParent;
						 */
						$categoryParent = $this->categories[$parent];
						$dsuCategory->addParent($categoryParent);
					}
				}
			}
		}

		foreach($this->shopData[DataKeys::SHOP_DATA_ITEMS_KEY] as $item => $data){
			if(isset($this->items[$item])){
				/**
				 * @var DSUItem $dsuCategory;
				 */
				$dsuItem = $this->items[$item];
				foreach($data[DataKeys::SHOP_DATA_PARENTS_KEY] as $parent){
					if(isset($this->categories[$parent])) {
						/**
						 * @var DSUCategory $categoryParent;
						 */
						$categoryParent = $this->categories[$parent];
						$dsuItem->addParent($categoryParent);
					}
				}
			}
		}
	}

	private function mapChildren() {

		/**
		 * @var DSUCategory $category;
		 */
		foreach($this->categories as $category){
			$categoryParents = $category->getAllParents();
			if(!$categoryParents === null or count($categoryParents) > 0){
				/**
				 * @var DSUCategory $parent;
				 */
				foreach($categoryParents as $parent){
					$parent->addChild($category);
				}
			}
		}

		/**
		 * @var DSUItem $item;
		 */
		foreach($this->items as $item){
			$itemParents = $item->getAllParents();
			if(!$itemParents === null or count($itemParents) > 0) {
				foreach($itemParents as $parent){
					$parent->addChild($item);
				}
			}
		}
	}

	public function reload(bool $saveFirst = false){
		if($saveFirst){
			$this->saveShopData();
		}
		$directory = $this->plugin->getDataFolder();
		$this->shopData = yaml_parse_file($directory . self::SHOP_DATA_FILE_NAME);
		if(!isset($this->shopData[DataKeys::SHOP_DATA_CATEGORIES_KEY])){
			$this->shopData[DataKeys::SHOP_DATA_CATEGORIES_KEY] = [];
		}
		if(!isset($this->shopData[DataKeys::SHOP_DATA_ITEMS_KEY])){
			$this->shopData[DataKeys::SHOP_DATA_ITEMS_KEY] = [];
		}

		$this->populateCategories();
		$this->populateItems();
		$this->mapParents();
		$this->mapChildren();
	}

	/**
	 * @return DSUCategory[]
	 */
	public function getAllCategories(){
		return $this->categories;
	}

	/**
	 * Pass the name of a category to test into this function and
	 * it will return true if the category is available.
	 * @param string $categoryName
	 * @return bool
	 */
	public function isCategory(string $categoryName): bool{
		if(isset($this->categories[$categoryName])){
			return true;
		}
		return false;
	}

	public function getCategory(string $categoryName): ?DSUCategory{
		if($this->isCategory($categoryName)){
			return $this->categories[$categoryName];
		}
		return null;
	}

	/**
	 * @param string      $name
	 * @param string|null $parent
	 * @param string      $img
	 */
	public function setCategory(string $name, string $img = "", string $parent = null){
		if(!isset($this->categories[$name])){
			$this->categories[$name] = new DSUCategory($this, $name, $img);
			$this->plugin->getLogger()->info("New Category $name Detected");
			if($parent !== null){
				if(isset($this->categories[$parent])){
					$this->categories[$name]->addParent($this->categories[$parent]);
				}
			}
			return;
		}elseif($this->categories[$name]->isParent($parent)){
			// This category already has the passed parent so removing parent instead of adding.
			$this->plugin->getLogger()->info("setCategory removing parent $parent from $name");
			$this->categories[$name]->removeParent($parent);
		}else{
			$this->plugin->getLogger()->info("setCategory adding parent $parent to $name.");
			if(isset($this->categories[$parent])){
				$this->categories[$name]->addParent($this->categories[$parent]);
			}
		}

		$this->categories[$name]->setImage($img);

		$this->reload(true);
	}

	public function removeCategory(string $name){
		if(isset($this->categories[$name])){
			unset($this->categories[$name]);
			$this->reload(true);
		}
	}

	public function getItem(string $itemName): ?DSUItem {
		if(isset($this->items[$itemName])){
			return $this->items[$itemName];
		}
		return null;
	}

	public function isShopItem($itemName): bool{
		if(isset($this->items[$itemName])){
			return true;
		}
		return false;
	}

	public function getAllItems() {
		return $this->items;
	}

	public function setItem(string $name, int $id, int $meta, float $sellPrice = 100, float $buyPrice = 0, string $parent = null, string $image = ""){
		$item = new DSUItem($this, $name, $id, $meta, $buyPrice, $sellPrice, $image);
		if($this->isCategory($parent)){
			$item->addParent($this->getCategory($parent));
		} else {
			$this->plugin->getLogger()->warning("setItem: $parent is not a valid parent for $name");
		}
		$this->items[$name] = $item;

		$this->reload(true);
	}

	public function removeItem(string $itemName){
		if(isset($this->items[$itemName])){
			unset($this->items[$itemName]);
			$this->reload(true);
			return true;
		}
		$this->plugin->getLogger()->warning("removeItem: No item named $itemName found.");
		return false;
	}

	public function saveShopData() {
		$shopData = [];

		foreach($this->categories as $category){
			/**
			 * @var DSUCategory $category;
			 */
			$parents = [];
			foreach($category->getAllParents() as $parent){
					/**
					 * @var DSUCategory $parent ;
					 */
					array_push($parents, $parent->getName());
			}
			$shopData[DataKeys::SHOP_DATA_CATEGORIES_KEY][$category->getName()][DataKeys::SHOP_DATA_PARENTS_KEY] = $parents;
			$shopData[DataKeys::SHOP_DATA_CATEGORIES_KEY][$category->getName()][DataKeys::SHOP_DATA_IMAGE_KEY] = $category->getImage();
		}

		foreach($this->items as $item){
			/**
			 * @var DSUItem $item;
			 */
			$shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$item->getName()][DataKeys::SHOP_DATA_ITEM_ID_KEY] = $item->getID();
			$shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$item->getName()][DataKeys::SHOP_DATA_ITEM_META_KEY] = $item->getMeta();
			$shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$item->getName()][DataKeys::SHOP_DATA_ITEM_BUY_PRICE_KEY] = $item->getBuyPrice();
			$shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$item->getName()][DataKeys::SHOP_DATA_ITEM_SELL_PRICE_KEY] = $item->getSellPrice();
			$shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$item->getName()][DataKeys::SHOP_DATA_IMAGE_KEY] = $item->getImage();

			$parents = [];
			foreach($item->getAllParents() as $parent) {
				/**
				 * @var DSUCategory $parent ;
				 */
				array_push($parents, $parent->getName());
			}
			$shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$item->getName()][DataKeys::SHOP_DATA_PARENTS_KEY] = $parents;
		}

		yaml_emit_file($this->plugin->getDataFolder() . self::SHOP_DATA_FILE_NAME , $shopData);
	}
}