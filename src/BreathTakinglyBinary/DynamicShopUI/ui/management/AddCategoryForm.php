<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\libDynamicForms\CustomForm;
use pocketmine\Player;

class AddCategoryForm extends CustomForm implements FormKeys{

    /** @var DSUCategory[] */
    private $availableParents = [];

    public function __construct(){
        parent::__construct();
        $this->setTitle("Add New Category");
        $this->addInput("Category Name", self::CATEGORY_NAME);
        $parentNames = [""];
        foreach(DynamicShopUI::getInstance()->getDynamicShopManager()->getCategories() as $category){
            $categoryName = $category->getName();
            $parentNames[] = $categoryName;
            $this->availableParents[$categoryName] = $category;
        }

        $this->addDropdown("Parent", self::PARENTS, $parentNames);
        $this->addInput("Image URL", self::IMG_LOCATION);

    }

    public function onResponse(Player $player, $data) : void{
        if(!isset($data[self::CATEGORY_NAME]) or $data[self::CATEGORY_NAME] === null or $data[self::CATEGORY_NAME] === ""){
            $player->sendForm(new UpdateCategoriesOptionsForm());
            return;
        }

        $name = $data[self::CATEGORY_NAME];
        if(DynamicShopUI::getInstance()->getDynamicShopManager()->getCategoryByName($name) instanceof DSUCategory){
            $player->sendForm(new UpdateCategoriesOptionsForm("That category already exists."));
            return;
        }
        $image = "";
        if(isset($data[self::IMG_LOCATION])){
            $image = $data[self::IMG_LOCATION];
        }
        $category = new DSUCategory($name, $image);

        if(isset($data[self::PARENTS]) and $data[self::PARENTS] > 0 and isset($this->availableParents[($data[self::PARENTS] - 1)])){
            $parent = $this->availableParents[$data[self::PARENTS]];
            if($parent instanceof DSUCategory){
                $category->addParent($parent);
            }
        }

        DynamicShopUI::getInstance()->getDynamicShopManager()->addCategory($category);

        $player->sendForm(new UpdateCategoriesOptionsForm("Category, \"$name\", created successfully!"));
    }

}