<?php

namespace BreathTakinglyBinary\DynamicShopUI\elements;


abstract class DSUElement{

    /** @var string */
    protected $name;

    /** @var DSUCategory[] */
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
     * @param DSUCategory $category
     */
    public function addParent(DSUCategory $category) : void{
        if($category->getName() === $this->name){
            return;
        }
        $this->parents[$category->getName()] = $category;
        $category->addChild($this);
    }

    /**
     * @param DSUCategory $category
     */
    public function removeParent(DSUCategory $category) : void{
        $this->removeParentByName($category->getName());
    }

    /**
     * @param string $parentName
     */
    public function removeParentByName(string $parentName) : void{
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