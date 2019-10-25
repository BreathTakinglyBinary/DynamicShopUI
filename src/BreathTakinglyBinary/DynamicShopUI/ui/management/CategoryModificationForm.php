<?php


namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\libDynamicForms\CustomForm;
use pocketmine\Player;
use pocketmine\utils\MainLogger;

class CategoryModificationForm extends CustomForm implements FormKeys{

    /** @var DSUCategory */
    private $category;

    /** @var DSUCategory[] */
    private $availableCategories = [];

    /** @var DSUCategory[] */
    private $categoryParents;

    public function __construct(DSUCategory $category){
        parent::__construct();
        $this->category = $category;
        $this->categoryParents = $category->getAllParents();
        $parents = [""];
        foreach ($this->categoryParents as $parentName => $parentObject){
            $parents[] = $parentName;
            $this->categoryParents[] = $parentObject;
        }

        $availableCategories = [""];
        foreach(DynamicShopUI::getInstance()->getDynamicShopManager()->getCategories() as $DSUCategory){
            $categoryName = $DSUCategory->getName();
            if(!isset($this->categoryParents[$categoryName])){
                $availableCategories[] = $categoryName;
                $this->availableCategories[] = $DSUCategory;
            }
        }

        $this->setTitle("Modify Category - " . $category->getName());
        $this->addDropdown("Remove Parent", self::PARENTS_REMOVE, $parents);
        $this->addDropdown("Add Parent", self::PARENTS_ADD, $availableCategories);
        $this->addInput("Update Image", self::IMG_LOCATION, $category->getImage());
    }

    public function onResponse(Player $player, $data) : void{
        $changed = false;

        if(isset($data[self::PARENTS_REMOVE]) and $data[self::PARENTS_REMOVE] > 0){
            $parentIndex = $data[self::PARENTS_REMOVE] - 1;
            if(isset($this->categoryParents[$parentIndex])){
                DynamicShopUI::getInstance()->getLogger()->debug("Removing parent \"" . $this->categoryParents[$parentIndex]->getName() . "\" from " . $this->category->getName());
                $this->category->removeParent($this->categoryParents[$parentIndex]);
                $changed = true;
            }
        }

        if(isset($data[self::PARENTS_ADD]) and $data[self::PARENTS_ADD] > 0){
            $categoryIndex = $data[self::PARENTS_ADD] - 1;
            if(isset($this->availableCategories[$categoryIndex])){
                DynamicShopUI::getInstance()->getLogger()->debug("Adding parent \"" . $this->availableCategories[$categoryIndex]->getName() . "\" to " . $this->category->getName());
                $this->category->addParent($this->availableCategories[$categoryIndex]);
                $changed = true;
            }
        }

        if(isset($data[self::IMG_LOCATION]) and $data[self::IMG_LOCATION] !== null){
            DynamicShopUI::getInstance()->getLogger()->debug("Setting image location to " . $data[self::IMG_LOCATION] . " for " . $this->category->getName());
            $this->category->setImage($data[self::IMG_LOCATION]);
            $changed = true;
        }
        $msg = "";
        if($changed){
            DynamicShopUI::getInstance()->getDynamicShopManager()->updateElement($this->category);
            $msg = $this->category->getName() . " Updated!";
        }
        $player->sendForm(new UpdateCategoriesOptionsForm($msg));

    }

}