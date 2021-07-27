<?php namespace Taco\HCF\commands\random;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\player\Player;
use Taco\HCF\Main;

class IDCommand extends Command {

	public function __construct(string $name) {
		parent::__construct($name);
		$this->setDescription("Check the ID of the item your holding.");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
		if (!$sender instanceof Player) return;
		$item = $sender->getInventory()->getItemInHand();
		$sender->sendMessage("[DEBUG] ".$item->getId().":".$item->getMeta());
	}

}