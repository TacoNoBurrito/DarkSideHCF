<?php namespace Taco\HCF\commands\random;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\player\Player;
use Taco\HCF\Main;

class SaveFactionsCommand extends Command {

	public function __construct(string $name) {
		parent::__construct($name);
		$this->setDescription("Save all faction data.");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
		if (!$sender instanceof Player) {
			Main::getInstance()->getServer()->broadcastMessage("saving all data. Server might lag.");
			Main::getInstance()->saveAllData();
		}
	}

}