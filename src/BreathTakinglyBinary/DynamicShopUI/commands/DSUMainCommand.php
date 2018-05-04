<?php

namespace BreathTakinglyBinary\DynamicShopUI\commands;

use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class DSUMainCommand extends DSUBaseCommand{

	public function __construct(DynamicShopUI $plugin){
		$name = "dsu";
		$description = "Opens interface to Add/Delete/Update Categories and Items in the Shop";
		$usageMessage = "/dsu <manage>";
		parent::__construct($plugin, $name, $description, $usageMessage);
		$this->setPermission("dsu.command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(isset($args[0])){

			switch($args[0]){
				case "manage":
					if(!$sender instanceof Player){
						$this->sendConsoleError($sender);
						break;
					}elseif(!$sender->hasPermission("dsu.command.manage")){
						$this->sendNoPermission($sender);
						break;
					}else{
						$this->plugin->getDSUManagementForms()->dsuManageMainForm($sender);
						break;
					}
			}
		} else {
			$sender->sendMessage($this->usageMessage);
			return;
		}
	}

}