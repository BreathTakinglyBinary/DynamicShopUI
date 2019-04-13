<?php

namespace BreathTakinglyBinary\DynamicShopUI\elements;


use BreathTakinglyBinary\DynamicShopUI\data\DSUConfig;

abstract class DSUElement{

    /** @var string */
    protected $name;

    /** @var DSUCategory[] */
    protected $parents = [];

    /** @var string */
    protected $image;
    /**
     * @var DSUConfig
     */
    private $settings;

    /**
     * DSUElement constructor.
     *
     * @param DSUConfig $settings
     * @param string    $name
     * @param string    $image
     */
    public function __construct(DSUConfig $settings, string $name, string $image = ""){
        $this->settings = $settings;
        $this->name = $name;
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getName() : string{
        return $this->name;
    }

    /**
     * @return DSUConfig
     */
    public function getSettings() : DSUConfig{
        return $this->settings;
    }

    /**
     * @param string $parentName
     *
     * @return DSUCategory|null
     */
    public function getParent(string $parentName) : ?DSUCategory{
        if($this->isParent($parentName)){
            return $this->parents[$parentName];
        }
        return null;
    }

    /**
     * @return DSUCategory[]
     */
    public function getAllParents() : array{
        return $this->parents;
    }

    /**
     * @param string $parentName
     *
     * @return bool
     */
    public function isParent(string $parentName) : bool{
        if(isset($this->parents[$parentName])){
            return true;
        }

        return false;
    }

    /**
     * @param DSUCategory $newParent
     *
     * @return bool
     */
    public function addParent(DSUCategory $newParent) : bool{
        // An element can't be it's own parent.
        if($newParent->getName() === $this->name){
            return false;
        }
        if(!isset($this->parents[$newParent->getName()])){
            $this->parents[$newParent->getName()] = $newParent;

            return true;
        }else{
            return false;
        }
    }

    /**
     * @param string $parent
     *
     * @return bool
     */
    public function removeParent(string $parent) : bool{
        if(isset($this->parents[$parent])){
            unset($this->parents[$parent]);

            return true;
        }else{
            return false;
        }
    }

    /**
     * @return string
     */
    public function getImage() : string{
        return $this->image;
    }

    /**
     * @param string $url
     */
    public function setImage(string $url){
        $this->image = $url;
    }

}