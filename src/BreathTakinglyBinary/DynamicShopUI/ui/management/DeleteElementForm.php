<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\management;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUElement;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use BreathTakinglyBinary\libDynamicForms\ModalForm;
use pocketmine\Player;

class DeleteElementForm extends ModalForm{

    /** @var DSUElement */
    private $element;

    public function __construct(DSUElement $element){
        parent::__construct();
        $this->element = $element;
        $this->setTitle("Really delete " . $element->getName() . "?");
        $this->setContent("Are you sure you want to delete " . $element->getName() . " ?");
        $this->setButton1("Yes");
        $this->setButton2("Cancel");
    }

    public function onResponse(Player $player, $data) : void{
        $msg = "";
        if($data){
            if($this->element instanceof DSUItem){
                DynamicShopUI::getInstance()->getDynamicShopManager()->removeItem($this->element);
            }elseif($this->element instanceof DSUCategory){
                DynamicShopUI::getInstance()->getDynamicShopManager()->removeCategory($this->element);
            }else{
                DynamicShopUI::getInstance()->getLogger()->error("Unknown DSUElement passed to DeleteElementForm");
            }
            $msg = "§a" . $this->element->getName() . " successfully removed.";

        }

        if($this->element instanceof DSUCategory){
            $form = new UpdateCategoriesOptionsForm($msg);
        }else{
            $form = new UpdateItemOptionsForm($msg);
        }

        $player->sendForm($form);
    }

}