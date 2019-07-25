<?php


namespace BreathTakinglyBinary\DynamicShopUI\elements;


use BreathTakinglyBinary\DynamicShopUI\data\DataKeys;
use pocketmine\Server;

class DSUCategory extends DSUElement{

    /** @var array  */
    private $children = [];

    /**
     * DSUCategory constructor.
     *
     * @param string    $name
     * @param string    $image
     */
    public function __construct(string $name, string $image = ""){
        parent::__construct($name, $image);
        $this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY] = [];
        $this->children[DataKeys::SHOP_DATA_ITEMS_KEY] = [];
    }

    /**
     * @param DSUElement $newChild
     *
     * @return bool
     */
    public function addChild(DSUElement &$newChild) : bool{
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
        }elseif($newChild instanceof DSUItem){
            if(!isset($this->children[DataKeys::SHOP_DATA_ITEMS_KEY][$newChild->getName()])){
                $this->children[DataKeys::SHOP_DATA_ITEMS_KEY][$newChild->getName()] = $newChild;

                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * @param DSUElement $child
     *
     * @return bool
     */
    public function removeChild(DSUElement &$child) : bool{
        if($child instanceof DSUCategory){
            if(isset($this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY][$child->getName()])){
                unset($this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY][$child->getName()]);

                return true;
            }

            return false;
        }elseif($child instanceof DSUItem){
            if(!isset($this->children[DataKeys::SHOP_DATA_ITEMS_KEY][$child->getName()])){
                $this->children[DataKeys::SHOP_DATA_ITEMS_KEY][$child->getName()] = $child;

                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * @return DSUCategory[]
     */
    public function getCategories() : array{
        return $this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY];
    }

    /**
     * @return DSUItem[]
     */
    public function getItems() : array{
        return $this->children[DataKeys::SHOP_DATA_ITEMS_KEY];
    }
}