<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\Player;

class UpdateItemOptionsForm extends SimpleForm{

    public function __construct(string $msg = ""){
        parent::__construct();
        $this->setTitle("§f§5Items §f- Choose Action");
        $this->setContent($msg);
        $this->addButton("Add Item");
        $this->addButton("Modify Item");
        $this->addButton("Delete Item");
        $this->addButton("Back");
    }

    public function onResponse(Player $player, $data) : void{
        switch($data){
            case 0:
                $player->sendForm(new AddItemForm());
                break;
            case 1:
                $player->sendForm(new ItemModificationSelectionForm());
                break;
            case 2:
                $player->sendForm(new ItemModificationSelectionForm(true));
                break;
            case 3:
                $player->sendForm(new ManagementMainForm("", $player->hasPermission("dsu.configuration")));
                break;

        }
    }

}