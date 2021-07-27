<?php namespace Taco\HCF\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Taco\HCF\other\kits\forms\KitSelectionForm;

class KitCommand extends Command {

	public function __construct(string $name) {
		parent::__construct($name);
		$this->setDescription("Get a kit.");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
		if ($sender instanceof Player) {
			$sender->sendForm(new KitSelectionForm());
		}
	}

}