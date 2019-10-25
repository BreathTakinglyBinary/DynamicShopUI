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
     */
    public function addChild(DSUElement &$newChild) : void{
        $selfName = $this->name;
        $childName = $newChild->getName();
        if($newChild->getName() === $this->getName()){
            // Categories cannot have children of themselves.
            return;
        }
        Server::getInstance()->getLogger()->debug("$selfName adding child $childName");
        if($newChild instanceof DSUCategory and !isset($this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY][$newChild->getName()])){
            $this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY][$newChild->getName()] = $newChild;
        }elseif($newChild instanceof DSUItem and !isset($this->children[DataKeys::SHOP_DATA_ITEMS_KEY][$newChild->getName()])){
            $this->children[DataKeys::SHOP_DATA_ITEMS_KEY][$newChild->getName()] = $newChild;
        }else{
            return;
        }
        $newChild->addParent($this);
    }

    /**
     * @param DSUElement $child
     */
    public function removeChild(DSUElement &$child) : void{
        if($child instanceof DSUCategory){
            unset($this->children[DataKeys::SHOP_DATA_CATEGORIES_KEY][$child->getName()]);
        }elseif($child instanceof DSUItem){
            unset($this->children[DataKeys::SHOP_DATA_ITEMS_KEY][$child->getName()]);
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