<?php


namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\MainLogger;

class CategoryModificationSelectionForm extends SimpleForm implements FormKeys{

    /** @var DSUCategory[] */
    private $categories = [];

    /** @var bool */
    private $delete;

    public function __construct(bool $delete = false, string $msg = ""){
        parent::__construct();
        $this->delete = $delete;
        $this->setTitle("Select Item to " . ($delete ? "Delete" : "Modify"));
        $this->setContent($msg);
        foreach(DynamicShopUI::getInstance()->getDynamicShopManager()->getCategories() as $category){
            $name = $category->getName();
            $this->categories[$name] = $category;
            $this->addButton($name, $name);
        }
        $this->addButton("Back", self::BACK);
    }


    public function onResponse(Player $player, $data) : void{
        if($data === self::BACK){
            $player->sendForm(new UpdateCategoriesOptionsForm());
        }
        if(!isset($this->categories[$data])){
            MainLogger::getLogger()->error("Unable to find $data in categories options.");
            $player->sendForm(new UpdateCategoriesOptionsForm("§cAn error has occured and was reported."));
            return;
        }

        if($this->delete){
            $player->sendForm(new DeleteElementForm($this->categories[$data]));
            return;
        }
        $player->sendForm(new CategoryModificationForm($this->categories[$data]));
    }
}