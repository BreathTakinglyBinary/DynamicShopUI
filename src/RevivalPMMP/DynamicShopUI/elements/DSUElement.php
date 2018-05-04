<?php

namespace RevivalPMMP\DynamicShopUI\elements;


use RevivalPMMP\DynamicShopUI\data\DSUConfig;

abstract class DSUElement{

	protected $name;
	protected $parents = [];
	protected $image;
	/**
	 * @var DSUConfig
	 */
	private $settings;
	public function __construct(DSUConfig $settings, string $name, string $image = ""){
		$this->settings = $settings;
		$this->name = $name;
		$this->image = $image;
}

	public function getName(): string{
		return $this->name;
	}

	public function getSettings(): DSUConfig{
		return $this->settings;
	}

	public function getParent(string $parentName): ?DSUCategory{
		if($this->isParent($parentName)){
			return $this->parents[$parentName];
		}
	}

	public function getAllParents(): array{
		return $this->parents;
	}

	public function isParent(string $parentName): bool{
		if(isset($this->parents[$parentName])){
			return true;
		}
		return false;
	}

	public function addParent(DSUCategory $newParent): bool {
		// An element can't be it's own parent.
		if($newParent->getName() === $this->name){
			return false;
		}
		if(!isset($this->parents[$newParent->getName()])){
			$this->parents[$newParent->getName()] = $newParent;
			return true;
		} else {
			return false;
		}
	}

	public function removeParent(string $parent): bool{
		if(isset($this->parents[$parent])){
			unset($this->parents[$parent]);
			return true;
		} else {
			return false;
		}
	}

	public function getImage(): string{
		return $this->image;
	}

	public function setImage(string $url) {
		$this->image = $url;
	}

}