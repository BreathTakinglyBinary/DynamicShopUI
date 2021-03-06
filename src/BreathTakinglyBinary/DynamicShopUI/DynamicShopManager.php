<?php


namespace BreathTakinglyBinary\DynamicShopUI;


use BreathTakinglyBinary\DynamicShopUI\data\DataKeys;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUElement;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use pocketmine\item\Item;

class DynamicShopManager{

    /** @var DynamicShopUI */
    private $plugin;

    /** @var DSUCategory[] */
    private $categories = [];

    /** @var DSUItem[] */
    private $items = [];


    public function __construct(DynamicShopUI $plugin){
        $this->plugin = $plugin;
    }

    public function loadShopData() : void{
        $shopData = yaml_parse_file($this->plugin->getDataFolder() . DataKeys::SHOP_DATA_FILE_NAME);
        $pendingParents = [];
        if(isset($shopData[DataKeys::SHOP_DATA_CATEGORIES_KEY])){
            foreach($shopData[DataKeys::SHOP_DATA_CATEGORIES_KEY] as $categoryName => $categoryData){
                $category = new DSUCategory($categoryName);
                if(isset($categoryData[DataKeys::SHOP_DATA_PARENTS_KEY])){
                    foreach($categoryData[DataKeys::SHOP_DATA_PARENTS_KEY] as $parentName){
                        if(($parent = $this->getCategoryByName($parentName)) instanceof DSUCategory){
                            $category->addParent($parent);
                        }else{
                            $pendingParents[] = [$category, $parentName];
                        }
                    }
                }
                if(isset($categoryData[DataKeys::SHOP_DATA_IMAGE_KEY])){
                    $category->setImage($categoryData[DataKeys::SHOP_DATA_IMAGE_KEY]);
                }
                $this->addCategory($category);
            }
        }

        foreach($pendingParents as $data){
            $pendingElement = $data[0];
            $missedParent = $this->getCategoryByName($data[1]);
            if($missedParent instanceof DSUCategory and $pendingElement instanceof DSUElement){
                $pendingElement->addParent($missedParent);
            }
        }

        if(isset($shopData[DataKeys::SHOP_DATA_ITEMS_KEY])){
            foreach($shopData[DataKeys::SHOP_DATA_ITEMS_KEY] as $itemName => $itemData){
                if(!isset($itemData[DataKeys::SHOP_DATA_ITEM_ID_KEY])
                    or !is_numeric($itemData[DataKeys::SHOP_DATA_ITEM_ID_KEY])){
                    $this->plugin->getLogger()->error("Item Data for " . $itemName . " doesn't have an item ID number.");
                    continue;
                }
                $meta = 0;
                if(isset($itemData[DataKeys::SHOP_DATA_ITEM_META_KEY])
                    and is_numeric($itemData[DataKeys::SHOP_DATA_ITEM_META_KEY])){
                    $meta = $itemData[DataKeys::SHOP_DATA_ITEM_META_KEY];
                }
                $item = new DSUItem($itemName, $itemData[DataKeys::SHOP_DATA_ITEM_ID_KEY], $meta);
                if(isset($itemData[DataKeys::SHOP_DATA_ITEM_BUY_PRICE_KEY])){
                    $item->setBuyPrice($itemData[DataKeys::SHOP_DATA_ITEM_BUY_PRICE_KEY]);
                }
                if(isset($itemData[DataKeys::SHOP_DATA_ITEM_CAN_BUY])){
                    $item->enableBuying( (bool) $itemData[DataKeys::SHOP_DATA_ITEM_CAN_BUY]);
                } elseif($item->getBuyPrice() > 0) {
                    $item->enableBuying(true);
                }
                if(isset($itemData[DataKeys::SHOP_DATA_ITEM_SELL_PRICE_KEY])){
                    $item->setSellPrice($itemData[DataKeys::SHOP_DATA_ITEM_SELL_PRICE_KEY]);
                }
                if(isset($itemData[DataKeys::SHOP_DATA_ITEM_CAN_SELL])){
                    $item->enableSelling((bool) $itemData[DataKeys::SHOP_DATA_ITEM_CAN_SELL]);
                } elseif($item->getSellPrice() > 0){
                    $item->enableSelling(true);
                }
                if(isset($itemData[DataKeys::SHOP_DATA_IMAGE_KEY])){
                    $item->setImage(trim($itemData[DataKeys::SHOP_DATA_IMAGE_KEY]));
                }
                if(isset($itemData[DataKeys::SHOP_DATA_PARENTS_KEY])){
                    foreach($itemData[DataKeys::SHOP_DATA_PARENTS_KEY] as $parentName){
                        $parent = $this->getCategoryByName($parentName);
                        if($parent instanceof DSUCategory){
                            $item->addParent($parent);
                        }
                    }
                }
                $this->addItem($item);
            }
        }
    }

    /**
     * @param DSUCategory $category
     */
    public function addCategory(DSUCategory $category) : void{
        if(isset($this->categories[$category->getName()])){
            DynamicShopUI::getInstance()->getLogger()->debug("Overwriting Category: " . $category->getName());
        }
        $this->categories[$category->getName()] = $category;
        foreach($category->getAllParents() as $parentCategory){
                $parentCategory->addChild($category);
        }
    }

    /**
     * @param DSUCategory $category
     */
    public function removeCategory(DSUCategory $category){
        $this->removeCategoryByName($category->getName());
        foreach($this->categories as $registeredCategory){
            $registeredCategory->removeChild($category);
            $registeredCategory->removeParentByName($registeredCategory->getName());
        }
        foreach($this->getItems() as $regsiteredItem){
            $regsiteredItem->removeParentByName($category->getName());
        }
    }

    /**
     * @param string $categoryName
     */
    public function removeCategoryByName(string $categoryName){
        foreach($this->categories as $localCategory){
            $localCategory->removeParentByName($categoryName);
        }
        foreach($this->items as $item){
            $item->removeParentByName($categoryName);
        }
        unset($this->categories[$categoryName]);
    }

    /**
     * @return DSUCategory[]
     */
    public function getCategories() : array{
        return $this->categories;
    }

    public function getCategoryByName(string $categoryName) : ?DSUCategory{
        return $this->categories[$categoryName] ?? null;
    }

    /**
     * @param string $parentName
     *
     * @return DSUCategory[]|null
     */
    public function getCategoriesByParent(string $parentName) : ?array{
        return $this->getElementsByParent($parentName, true);
    }

    /**
     * @param bool $canSell
     *
     * @return DSUElement[]
     */
    public function getTopLevelElements() : array {
        $this->plugin->getLogger()->debug("Getting Top Level Elements");
        $elements = [];
        foreach($this->categories as $category){
            $this->plugin->getLogger()->debug("Category " . $category->getName() . ", has " . count($category->getAllParents()) . " parents.");
            if(empty($category->getAllParents())){
                if($this->hasChildren($category, true)){
                    $this->plugin->getLogger()->debug("Suitable Top Level Element found, " . $category->getName());
                    $elements[] = $category;
                }
            }
        }
        foreach($this->items as $item){
            $this->plugin->getLogger()->debug("Category " . $item->getName() . ", has " . count($item->getAllParents()) . " parents.");
            if(empty($item->getAllParents()) and $item->canSell()){

                $elements[] = $item;
            }
        }
        return $elements;
    }

    /**
     * @param DSUItem $item
     */
    public function addItem(DSUItem $item) : void{
        if(isset($this->items[$item->getName()])){
            DynamicShopUI::getInstance()->getLogger()->debug("Overwriting Item: " . $item->getName());
        }
        $this->items[$item->getName()] = $item;
        foreach($item->getAllParents() as $parent){
            $parent->addChild($item);
        }
    }

    /**
     * @param DSUItem $item
     */
    public function removeItem(DSUItem $item) : void{
        unset($this->items[$item->getName()]);
        foreach($this->getCategories() as $category){
            $category->removeChild($item);
        }
    }


    /**
     * @return DSUItem[]
     */
    public function getItems() : array{
        return $this->items;
    }

    /**
     * @param int $itemId
     * @param int $itemMeta
     *
     * @return DSUItem|null
     */
    public function getItemById(int $itemId, int $itemMeta = 0) : ?DSUItem{
        foreach($this->items as $item){
            if($item->getID() === $itemId and $item->getMeta() === $itemMeta){
                return $item;
            }
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return DSUItem|null
     */
    public function getItemByName(string $name) : ?DSUItem{
        return $this->items[$name] ?? null;
    }

    /**
     * @param string $parentName
     *
     * @return DSUItem[]|null
     */
    public function getItemsByParent(string $parentName) : ?array{
        return $this->getElementsByParent($parentName);
    }

    /**
     * @param Item $item
     *
     * @return DSUItem|null
     */
    public function getItemByItem(Item $item) : ?DSUItem{
        $shopItem = $this->getItemById($item->getId(), $item->getDamage());
        if(!$shopItem instanceof DSUItem){
            return null;
        } else {
            return $shopItem;
        }
    }

    /**
     * @param string $parentName
     * @param bool   $category
     *
     * @return DSUItem[]|DSUCategory[]|null
     */
    private function getElementsByParent(string $parentName, bool $category = false) : ?array{
        $matches = [];
        $type = $category ? "categories" : "items";
        $elements = $this->$type;
        foreach($elements as $element){
            if($element instanceof DSUElement and $element->hasParent($parentName)){
                $matches[] = $element;
            }
        }
        if(!empty($matches)){
            return $matches;
        }
        return null;
    }


    /**
     * @param DSUCategory $category
     * @param bool        $canSell
     *
     * @return bool
     */
    public function hasChildren(DSUCategory $category, bool $canSell = false){
        foreach($this->categories as $localCategory){
            if($localCategory->hasParent($category->getName())){
                return true;
            }
        }
        foreach($this->items as $item){
            if($item->hasParent($category->getName())){
                if(($canSell and $item->canSell()) or !$canSell)
                return true;
            }
        }
        return false;
    }

    /**
     * @param DSUElement $element
     */
    public function updateElement(DSUElement $element) : void{
        if($element instanceof DSUCategory){
            $this->plugin->getLogger()->debug("Updating Category: " . $element->getName());
            $this->categories[$element->getName()] = $element;
        }elseif($element instanceof DSUItem){
            $this->plugin->getLogger()->debug("Updating Item: " . $element->getName());
            $this->items[$element->getName()] = $element;
        }
    }

    public function saveShopData() : void{
        $shopData = [];

        foreach($this->categories as $category){
            $parents = [];
            foreach($category->getAllParents() as $parentName => $parent){
                array_push($parents, $parentName);
            }
            $shopData[DataKeys::SHOP_DATA_CATEGORIES_KEY][$category->getName()][DataKeys::SHOP_DATA_PARENTS_KEY] = $parents;
            $shopData[DataKeys::SHOP_DATA_CATEGORIES_KEY][$category->getName()][DataKeys::SHOP_DATA_IMAGE_KEY] = $category->getImage();
        }

        foreach($this->items as $item){
            /**
             * @var DSUItem $item ;
             */
            $itemName = $item->getName();
            $shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$itemName][DataKeys::SHOP_DATA_ITEM_ID_KEY] = $item->getID();
            $shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$itemName][DataKeys::SHOP_DATA_ITEM_META_KEY] = $item->getMeta();
            $shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$itemName][DataKeys::SHOP_DATA_ITEM_CAN_BUY] = $item->canBuy();
            $shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$itemName][DataKeys::SHOP_DATA_ITEM_BUY_PRICE_KEY] = $item->getBuyPrice();
            $shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$itemName][DataKeys::SHOP_DATA_ITEM_CAN_SELL] = $item->canSell();
            $shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$itemName][DataKeys::SHOP_DATA_ITEM_SELL_PRICE_KEY] = $item->getSellPrice();
            $shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$itemName][DataKeys::SHOP_DATA_IMAGE_KEY] = $item->getImage();
            $shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$itemName][DataKeys::SHOP_DATA_PARENTS_KEY] = [];
            foreach($item->getAllParents() as $parentName => $parent){
                $shopData[DataKeys::SHOP_DATA_ITEMS_KEY][$itemName][DataKeys::SHOP_DATA_PARENTS_KEY][] = $parentName;
            }
        }

        yaml_emit_file($this->plugin->getDataFolder() . DataKeys::SHOP_DATA_FILE_NAME, $shopData);
    }

}