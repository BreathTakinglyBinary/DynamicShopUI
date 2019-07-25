<?php

namespace BreathTakinglyBinary\DynamicShopUI\elements;


abstract class DSUElement{

    /** @var string */
    protected $name;

    /** @var string[] */
    protected $parents = [];

    /** @var string */
    protected $image;

    /**
     * DSUElement constructor.
     *
     * @param string    $name
     * @param string    $image
     */
    public function __construct(string $name, string $image = ""){
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
     * @return string[]
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
     * @param string $newParentName
     *
     * @return bool
     */
    public function addParent(string $newParentName) : bool{
        // An element can't be it's own parent.
        if($newParentName === $this->name){
            return false;
        }
        if(!isset($this->parents[$newParentName])){
            $this->parents[$newParentName] = $newParentName;
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param string $parentName
     */
    public function removeParent(string $parentName) : void{
        unset($this->parents[$parentName]);
    }

    /**
     * @param string $parentName
     *
     * @return bool
     */
    public function hasParent(string $parentName): bool{
        return isset($this->parents[$parentName]);
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