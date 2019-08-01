<?php

declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\shop;


use BreathTakinglyBinary\DynamicShopUI\utils\DynamicShopTransaction;
use BreathTakinglyBinary\libDynamicForms\CustomForm;
use pocketmine\Player;

class ChooseItemQuantityForm extends CustomForm{

    /** @var DynamicShopTransaction */
    private $transaction;

    private $itemName;

    private $price;

    public function __construct(DynamicShopTransaction $transaction, string $itemName, float $price, int $maxStackSize){
        parent::__construct();
        $this->transaction = $transaction;
        $this->itemName = $itemName;
        $this->price = $price;
        $this->setTitle("Buy $itemName - $price (each)");
        $this->addSlider("How many $itemName would you like to buy?  ($price each)", 1, $maxStackSize);
    }

    public function onResponse(Player $player, $data) : void{
        if($data[0] < 1){
            return;
        }
        $player->sendForm(new ConfirmPurchaseForm($this->transaction, $this->itemName, $this->price, $data[0]));
    }

}