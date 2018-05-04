<?php

namespace RevivalPMMP\DynamicShopUI\elements;


use RevivalPMMP\DynamicShopUI\data\DSUConfig;

class DSUItem extends DSUElement{

	private $id = 0;
	private $meta = 0;
	private $buyPrice = 0.0;
	private $sellPrice = 0.0;
	private $buy = false;
	private $sell = false;

	public function __construct(DSUConfig $settings, string $name, int $id = 0, int $meta = 0, float $buyPrice = 0, float $sellPrice = 0, string $image = "", bool $canBuy = false, bool $canSell = false){
		parent::__construct($settings, $name, $image);
		$this->id = $id;
		$this->meta = $meta;
		$this->buyPrice = $buyPrice;
		$this->sellPrice = $sellPrice;
		$this->buy = $canBuy;
		$this->sell = $canSell;
	}

	public function getID(): int{
		return $this->id;
	}

	public function getMeta(): int{
		return $this->meta;
	}

	public function getBuyPrice(): float{
		return $this->buyPrice;
	}

	public function setBuyPrice(float $price){
		$this->buyPrice = $price;
	}
	public function getSellPrice(): float{
		return $this->sellPrice;
	}

	public function setSellPrice(float $price){
		$this->sellPrice = $price;
	}

	public function enableBuying(bool $enable = false){
		$this->buy = $enable;
	}

	public function canBuy() : bool{
		 return $this->buy;
	}

	public function enableSelling(bool $enable = false){
		$this->sell = $enable;
	}

	public function canSell(): bool{
		return $this->sell;
	}

}