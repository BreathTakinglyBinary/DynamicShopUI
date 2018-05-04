<?php

declare(strict_types = 1);

namespace BreathTakinglyBinary\DynamicShopUI\commands;

use BreathTakinglyBinary\DynamicShopUI\DynamicShopUI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as TF;



abstract class DSUBaseCommand extends Command implements PluginIdentifiableCommand {

	/** @var DynamicShopUI */
	protected $plugin = null;

	public function __construct(DynamicShopUI $plugin, $name, $description = "", $usageMessage = null, array $aliases = []) {
		parent::__construct($name, $description, $usageMessage, $aliases);
		$this->plugin = $plugin;
		$this->setPermission("dsu.command." . $name);
		$this->setUsage(TF::DARK_PURPLE . "[Usage] " . $usageMessage);
	}

	/**
	 * @param CommandSender $sender
	 */
	public function sendConsoleError(CommandSender $sender): void {
		$sender->sendMessage($this->getWarning() . "This command cannot be used from the console.");
	}

	/**
	 * @return string
	 */
	public function getWarning(): string {
		return TF::RED . "[WARNING]";
	}

	/**
	 * @return DynamicShopUI
	 */
	public function getPlugin(): Plugin {
		return $this->plugin;
	}

	/**
	 * @param CommandSender $sender
	 */
	public function sendNoPermission(CommandSender $sender): void {
		$sender->sendMessage($this->getWarning() . "You don't have permission to use this command.");
	}
	
	public function getSettings() {
		return $this->plugin->getSettings();
	}
}