<?php namespace Taco\HCF\commands\admin;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\player\Player;
use Taco\HCF\Main;
use function array_shift;
use function implode;
use function in_array;
use function is_numeric;

class CrateCommand extends Command {

	public function __construct(string $name) {
		parent::__construct($name);
		$this->setDescription("Crates base command.");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
		if (Main::getInstance()->getServer()->isOp($sender->getName()) or !$sender instanceof Player) {
			$arg = array_shift($args);
			if ($arg == "") {
				$sender->sendMessage("Please provide a arguemnt. do /crate help for a list of commands");
				return;
			}
			switch ($arg) {
				case "keyall":
					$type = array_shift($args);
					if ($type == "") {
						$sender->sendMessage("Please provide a type of crate key to keyall. for a list do /crate list");
						return;
					}
					if (!in_array($type, Main::getCrateUtils()::TYPES)) {
						$sender->sendMessage("$type is not a real crate type. Here is a list of types: [".implode(",", Main::getCrateUtils()::TYPES)."]");
						return;
					}
					$amount = array_shift($args);
					if ($amount == "") {
						$sender->sendMessage("Please provide an amount of keys to keyall");
						return;
					}
					$amount = (int)$amount;
					if (!is_numeric($amount)) {
						$sender->sendMessage("The amount of keys must be a number!");
						return;
					}//§
					$giver = $sender instanceof Player ? $sender->getName() : "CONSOLE";
					Main::getInstance()->getServer()->broadcastMessage("§e§b  \n§d".$giver."§e has given everyone online §dx".$amount." {$type}§e key(s)!");
					foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $player) {
						Main::getCrateUtils()->giveCrateKey($player, $type);
					}
					break;
				case "list":
					$sender->sendMessage("crate type list: [".implode(",", Main::getCrateUtils()::TYPES)."]");
					break;
				case "addcrate":
					if ($sender instanceof Player) {
						$type = array_shift($args);
						if ($type == "") {
							$sender->sendMessage("Please provide a type of crate key to keyall. for a list do /crate list");
							return;
						}
						if (!in_array($type, Main::getCrateUtils()::TYPES)) {
							$sender->sendMessage("$type is not a real crate type. Here is a list of types: [".implode(",", Main::getCrateUtils()::TYPES)."]");
							return;
						}
						Main::getInstance()->crate[$type] = Main::getUtils()->vec3ToString($sender->getPosition()->add(0, 0, 0));
						$sender->sendMessage("set crate pos");
						Main::getCrateUtils()->reloadCratePulsatingFloatingTextEntities();
					}
					break;
			}
		}
	}

}