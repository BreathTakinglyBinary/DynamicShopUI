<?php


namespace RevivalPMMP\DynamicShopUI\elements;


use pocketmine\Server;
use RevivalPMMP\DynamicShopUI\data\DataKeys;
use RevivalPMMP\DynamicShopUI\data\DSUConfig;

class DSUCategory extends DSUElement{

	private $children = [];

	public function __construct(DSUConfig $settings, string $name, string $image = ""){
		parent::__construct($settings, $name, $image);
		$this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY] = [];
		$this->children[DataKeys::SHOP_DATA_ITEMS_KEY] = [];
	}

	public function addChild(DSUElement &$newChild): bool{
		$selfName = $this->name;
		$childName = $newChild->getName();
		Server::getInstance()->getLogger()->info("$selfName adding child $childName");
		if($newChild->getName() === $this->getName()){
			// Categories cannot have children of themselves.
			return false;
		}
		if($newChild instanceof DSUCategory){
			if(!isset($this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY][$newChild->getName()])){
				$this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY][$newChild->getName()] = $newChild;
				return true;
			}
			return false;
		} elseif($newChild instanceof DSUItem){
			if(!isset($this->children[DataKeys::SHOP_DATA_ITEMS_KEY][$newChild->getName()])){
				$this->children[DataKeys::SHOP_DATA_ITEMS_KEY][$newChild->getName()] = $newChild;
				return true;
			}else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function removeChild(DSUElement &$child): bool {
		if($child instanceof DSUCategory){
			if(isset($this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY][$child->getName()])){
				unset($this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY][$child->getName()]);
				return true;
			}
			return false;
		} elseif($child instanceof DSUItem){
			if(!isset($this->children[DataKeys::SHOP_DATA_ITEMS_KEY][$child->getName()])){
				$this->children[DataKeys::SHOP_DATA_ITEMS_KEY][$child->getName()] = $child;
				return true;
			}else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getCategories(): array{
		return $this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY];
	}

	public function getItems(): array{
		return $this->children[DataKeys::SHOP_DATA_ITEMS_KEY];
	}
}