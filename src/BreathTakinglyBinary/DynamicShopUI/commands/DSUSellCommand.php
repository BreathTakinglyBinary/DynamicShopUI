<?php

namespace BreathTakinglyBinary\DynamicShopUI\commands;

use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class DSUSellCommand extends DSUBaseCommand{

    public function __construct(DynamicShopUI $plugin){
        $name = "sell";
        $description = "Allows you to sell items from your inventory.";
        $usageMessage = "/sell <all | hand> Defaults to /sell hand";
        parent::__construct($plugin, $name, $description, $usageMessage);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender instanceof Player){
            $this->sendConsoleError($sender);
            return;
        }

        if(isset($args[0])){
            switch(strtolower($args[0])){
                case "all":
                    $this->sellAll($sender);
                    break;
                default:
                    $this->sellHand($sender);
            }
        }else{
            $this->sellHand($sender);
        }
        return;
    }

    /**
     * @param Player $player
     *
     * @throws \InvalidStateException
     */
    private function sellAll(Player $player){
        if(!$player->hasPermission("dsu.command.sell.all")){
            $this->sendNoPermission($player);
            return;
        }
        $this->plugin->getSellTools()->sellAll($player);
    }

    /**
     * @param Player $player
     *
     * @throws \InvalidStateException
     */
    private function sellHand(Player $player){
        if(!$player->hasPermission("dsu.command.sell.hand")){
            $this->sendNoPermission($player);
            return;
        }
        $this->plugin->getSellTools()->sellHand($player);
    }
}