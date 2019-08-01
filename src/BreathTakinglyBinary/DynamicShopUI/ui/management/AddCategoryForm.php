<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\libDynamicForms\CustomForm;
use pocketmine\Player;

class AddCategoryForm extends CustomForm implements FormKeys{

    public function __construct(){
        parent::__construct();
        $form = new CustomForm([$this, "addCategory"]);
        $form->setTitle("Add New Category");
        $form->addInput("Category Name", self::CATEGORY_NAME);
        $parentNames = [];
        foreach(DynamicShopUI::getInstance()->getDynamicShopManager()->getCategories() as $category){
            $parentNames[$category->getName()] = $category->getName();
        }

        $form->addDropdown("Parent", self::PARENTS, $parentNames);
        $form->addInput("Image URL", self::IMG_LOCATION);

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

        if(isset($data[self::PARENTS])){
            $category->addParent($data[self::PARENTS]);
        }

        DynamicShopUI::getInstance()->getDynamicShopManager()->addCategory($category);

        $player->sendForm(new UpdateCategoriesOptionsForm("Category, \"$name\", created successfully!"));
    }

}