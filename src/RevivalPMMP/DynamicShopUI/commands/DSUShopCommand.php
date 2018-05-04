<?php
/**
 * Created by PhpStorm.
 * User: aaron
 * Date: 3/15/2018
 * Time: 4:17 AM
 */

namespace RevivalPMMP\DynamicShopUI\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use RevivalPMMP\DynamicShopUI\DynamicShopUI;

class DSUShopCommand extends DSUBaseCommand{

	public function __construct(DynamicShopUI $plugin){
		$name = "shop";
		$description = "Opens the Shop Interface at least, it's supposed to.";
		$usageMessage = "/shop";
		$this->setPermission("dsu.command.shop");
		parent::__construct($plugin, $name, $description, $usageMessage, ["shop"]);
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

		$this->plugin->getDSUShopForms()->shopMainForm($sender);
	}

}