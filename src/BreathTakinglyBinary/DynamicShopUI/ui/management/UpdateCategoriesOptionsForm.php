<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\Player;

class UpdateCategoriesOptionsForm extends SimpleForm implements FormKeys{

    public function __construct(string $msg = ""){
        parent::__construct();
        $this->setTitle("Categories - Choose Action");
        $this->setContent($msg);
        $this->addButton("Add Category", self::CATEGORY_ADD);
        $this->addButton("Modify Category", self::CATEGORY_MODIFY);
        $this->addButton("Delete Category", self::CATEGORY_REMOVE);
        $this->addButton("Back", self::BACK);

    }

    public function onResponse(Player $player, $data) : void{
        switch($data){
            case self::CATEGORY_ADD:
                $player->sendForm(new AddCategoryForm());
                break;
            case self::CATEGORY_MODIFY:
                $player->sendForm(new CategoryModificationSelectionForm());
                break;
            case self::CATEGORY_REMOVE:
                $player->sendForm(new CategoryModificationSelectionForm(true));
                break;
            case self::BACK:
                $player->sendForm(new ManagementMainForm("", $player->hasPermission("dsu.configuration")));
                break;
            default:
                DynamicShopUI::getInstance()->getLogger()->error("Unknown response \"$data\"found in UpdateCategoriesOptionsForm from player " . $player->getName() . ".");
        }
    }

}