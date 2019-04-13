<?php

namespace BreathTakinglyBinary\DynamicShopUI\elements;


use BreathTakinglyBinary\DynamicShopUI\data\DSUConfig;

class DSUItem extends DSUElement{

    /** @var int */
    private $id = 0;

    /** @var int */
    private $meta = 0;

    /** @var float */
    private $buyPrice = 0.0;

    /** @var float */
    private $sellPrice = 0.0;

    /** @var bool */
    private $buy = false;

    /** @var bool */
    private $sell = false;

    /**
     * DSUItem constructor.
     *
     * @param DSUConfig $settings
     * @param string    $name
     * @param int       $id
     * @param int       $meta
     * @param float     $buyPrice
     * @param float     $sellPrice
     * @param string    $image
     * @param bool      $canBuy
     * @param bool      $canSell
     */
    public function __construct(DSUConfig $settings, string $name, int $id = 0, int $meta = 0, float $buyPrice = 0, float $sellPrice = 0, string $image = "", bool $canBuy = false, bool $canSell = false){
        parent::__construct($settings, $name, $image);
        $this->id = $id;
        $this->meta = $meta;
        $this->buyPrice = $buyPrice;
        $this->sellPrice = $sellPrice;
        $this->buy = $canBuy;
        $this->sell = $canSell;
    }

    /**
     * @return int
     */
    public function getID() : int{
        return $this->id;
    }

    /**
     * @return int
     */
    public function getMeta() : int{
        return $this->meta;
    }

    /**
     * @return float
     */
    public function getBuyPrice() : float{
        return $this->buyPrice;
    }

    /**
     * @param float $price
     */
    public function setBuyPrice(float $price){
        $this->buyPrice = $price;
    }

    /**
     * @return float
     */
    public function getSellPrice() : float{
        return $this->sellPrice;
    }

    /**
     * @param float $price
     */
    public function setSellPrice(float $price){
        $this->sellPrice = $price;
    }

    /**
     * @param bool $enable
     */
    public function enableBuying(bool $enable = false){
        $this->buy = $enable;
    }

    /**
     * @return bool
     */
    public function canBuy() : bool{
        return $this->buy;
    }

    /**
     * @param bool $enable
     */
    public function enableSelling(bool $enable = false){
        $this->sell = $enable;
    }

    /**
     * @return bool
     */
    public function canSell() : bool{
        return $this->sell;
    }

}