<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use BreathTakinglyBinary\DynamicShopUI\ui\FormKeys;
use BreathTakinglyBinary\libDynamicForms\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\MainLogger;

class ItemModificationSelectionForm extends SimpleForm implements FormKeys{

    /** @var */
    private $delete;

    /** @var DSUItem[] */
    private $items = [];

    public function __construct(bool $delete = false, string $msg = ""){
        parent::__construct();
        $this->setTitle("Select Item to " . ($delete ? "Delete" : "Modify"));
        $this->setContent($msg);
        foreach(DynamicShopUI::getInstance()->getDynamicShopManager()->getItems() as $DSUItem){
            $name = $DSUItem->getName();
            $this->items[$name] = $DSUItem;
            $this->addButton($name, $name);
        }
        $this->addButton("Back", self::BACK);
    }

    public function onResponse(Player $player, $data) : void{
        if($data === self::BACK){
            $player->sendForm(new UpdateItemOptionsForm());
            return;
        }
        if(!isset($this->items[$data])){
            MainLogger::getLogger()->error("Unable to find $data in items options.");
            $player->sendForm(new ItemModificationSelectionForm($this->delete, "§cAn error has occured and was reported."));
            return;
        }

        if($this->delete){
            $player->sendForm(new DeleteElementForm($this->items[$data]));
            return;
        }
        $player->sendForm(new ItemModificationForm($this->items[$data]));
    }

}