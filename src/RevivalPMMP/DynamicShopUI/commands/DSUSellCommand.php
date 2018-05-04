<?php

namespace RevivalPMMP\DynamicShopUI\commands;


use pocketmine\command\CommandSender;
use pocketmine\Player;
use RevivalPMMP\DynamicShopUI\DynamicShopUI;

class DSUSellCommand extends DSUBaseCommand{

	public function __construct(DynamicShopUI $plugin){
		$name = "sell";
		$description = "Allows you to sell items from your inventory.";
		$usageMessage = "/sell <all | hand> Defaults to all";
		$this->setPermission("dsu.command.sell");
		parent::__construct($plugin, $name, $description, $usageMessage);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof Player){
			$this->sendConsoleError($sender);
			return;
		}

		if(!$this->testPermission($sender)){
			$this->sendNoPermission($sender);
			return;
		}
        if(isset($args[0])){
			switch(strtolower($args[0])){
				case "hand":
					$this->plugin->getSellTools()->sellHand($sender);
					break;

				default:
					$sender->sendMessage($this->getUsage());
					break;
			}
		} else {
			$this->plugin->getSellTools()->sellAll($sender);
		}
	}
}