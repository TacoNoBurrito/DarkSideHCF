<?php namespace Taco\HCF\commands\economy;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Taco\HCF\Main;
use function array_shift;
use function is_numeric;

class GiveMoneyCommand extends Command {

	public function __construct(string $name) {
		parent::__construct($name);
		$this->setDescription("Give money to players.");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
		if (!Main::getInstance()->getServer()->isOp($sender->getName()) and $sender instanceof Player) return;
		$player = array_shift($args);
		if ($player == "") {
			$sender->sendMessage("§r§l§7(§c!§7) §r§7Please provide a player to give money to.");
			return;
		}
		$player = Main::getInstance()->getServer()->getPlayerByPrefix($player);
		if ($player == null) {
			$sender->sendMessage("§r§l§7(§c!§7) §r§7This player is not online or doesn't exist.");
			return;
		}
		$money = array_shift($args);
		if ($money == "") {
			$sender->sendMessage("§r§l§7(§c!§7) §r§7Please provide a amount of money to give to the player.");
			return;
		}
		$money = (int)$money;
		if (!is_numeric($money)) {
			$sender->sendMessage("§r§l§7(§c!§7) §r§7The amount must be a integer!");
			return;
		}
		Main::getInstance()->players[$player->getName()]["money"] += $money;
		$sender->sendMessage("§r§l§7(§c!§7) §r§7Command success.");
	}

}