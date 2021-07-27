<?php namespace Taco\HCF\commands\admin;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Taco\HCF\Main;
use function array_shift;
use function in_array;

class SetRankCommand extends Command {

	public function __construct(string $name) {
		parent::__construct($name);
		$this->setDescription("Set a players rank.");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
		if ($sender instanceof Player) {
			$rank = Main::getInstance()->players[$sender->getName()]["rank"];
			if (!in_array($rank, Main::TIER_2)) return;
		}
		$player = array_shift($args);
		if ($player == "") {
			$sender->sendMessage("§r§l§7(§c!§7) §r§7Please provide a player to set a rank to!");
			return;
		}
		$player = Main::getInstance()->getServer()->getPlayerByPrefix($player);
		if ($player == null) {
			$sender->sendMessage("§r§l§7(§c!§7) §r§7This player is not online or doesn't exist!");
			return;
		}
		$rank = array_shift($args);
		if ($rank == "") {
			$sender->sendMessage("§r§l§7(§c!§7) §r§7Please provide a rank to set to the player!");
			return;
		}
		if (!in_array($rank, Main::VALID_RANKS)) {
			$sender->sendMessage("§r§l§7(§c!§7) §r§7This is not a valid rank! List of valid ranks: [".implode(",", Main::VALID_RANKS)."]");
			return;
		}
		$old = Main::getInstance()->players[$player->getName()]["rank"];
		$player->sendMessage("§a§oYour rank has been changed.\n§r§f".$old." -> $rank");
		Main::getInstance()->players[$player->getName()]["rank"] = $rank;
		$sender->sendMessage("§r§l§7(§c!§7) §r§fSuccessfully set their rank to §e".$rank.".");
	}

}