<?php namespace Taco\HCF\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\player\Player;
use Taco\HCF\Main;
use Taco\HCF\other\Time;
use Taco\HCF\tasks\TeleportTask;

class LogoutCommand extends Command {

	public function __construct(string $name) {
		parent::__construct($name);
		$this->setDescription("Logout safely without spawning a logout villager.");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
		if ($sender instanceof Player) {
			Main::getInstance()->getScheduler()->scheduleRepeatingTask(new TeleportTask(30, "logout", $sender), 20);
		}
	}

}