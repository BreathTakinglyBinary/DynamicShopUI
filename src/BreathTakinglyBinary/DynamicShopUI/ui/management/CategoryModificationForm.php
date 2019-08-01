<?php


namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\libDynamicForms\CustomForm;
use pocketmine\Player;

class CategoryModificationForm extends CustomForm implements FormKeys{

    /** @var DSUCategory */
    private $category;

    public function __construct(DSUCategory $category){
        parent::__construct();
        $this->category = $category;
        $parents = $category->getAllParents();
        $availableCategories = [];
        foreach(DynamicShopUI::getInstance()->getDynamicShopManager()->getCategories() as $DSUCategory){
            $categoryName = $DSUCategory->getName();
            if(!in_array($categoryName, $parents)){
                $availableCategories[] = $DSUCategory->getName();
            }
        }

        $this->setTitle("Modify Category - " . $category->getName());
        $this->addDropdown("Remove Parent", self::PARENTS_REMOVE, $parents);
        $this->addDropdown("Add Parent", self::PARENTS_ADD, $availableCategories);
        $this->addInput("Update Image", self::IMG_LOCATION, $category->getImage());
    }

    public function onResponse(Player $player, $data) : void{
        $changed = false;

        if(isset($data[self::PARENTS_REMOVE]) and $data[self::PARENTS_REMOVE] !== null){
            $this->category->removeParent($data[self::PARENTS_REMOVE]);
            $changed = true;
        }

        if(isset($data[self::PARENTS_ADD]) and $data[self::PARENTS_ADD] !== null){
            $this->category->addParent($data[self::PARENTS_ADD]);
            $changed = true;
        }

        if(isset($data[self::IMG_LOCATION]) and $data[self::IMG_LOCATION] !== null){
            $this->category->setImage($data[self::IMG_LOCATION]);
            $changed = true;
        }
        $msg = "";
        if($changed){
            $msg = $this->category->getName() . " Updated!";
        }
        $player->sendForm(new UpdateCategoriesOptionsForm($msg));

    }

}