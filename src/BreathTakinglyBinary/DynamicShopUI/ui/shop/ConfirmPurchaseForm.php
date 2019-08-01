<?php

declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui\shop;


use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use BreathTakinglyBinary\DynamicShopUI\utils\DynamicShopTransaction;
use BreathTakinglyBinary\libDynamicForms\ModalForm;
use pocketmine\Player;

class ConfirmPurchaseForm extends ModalForm{

    /** @var DynamicShopTransaction */
    private $transaction;

    /** @var int */
    private $quantity;

    /** @var float|int  */
    private $totalPrice;

    public function __construct(DynamicShopTransaction $transaction, string $itemName, float $priceEach, int $quantity = 1){
        parent::__construct();
        $this->transaction = $transaction;
        $this->quantity = $quantity;

        $this->totalPrice = $priceEach * $quantity;
        if($quantity > 1){
            $this->setTitle(DynamicShopUI::$shopName . "§r§f - §2Buy $itemName x $quantity for " . $this->totalPrice);
            $content = "§eDo you want to buy this?";
            $content .= "\n§eItem = §b$itemName";
            $content .= "\n§eQuantity = §b$quantity";
            $content .= "\n§eTotal Price = §b" . $this->totalPrice;
            $this->setContent($content);
        }
    }

    public function onResponse(Player $player, $data) : void{
        if(!$data){
            return;
        }
        $this->transaction->completeTransaction($player, $this->quantity, $this->totalPrice);
    }

}