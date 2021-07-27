<?php namespace Taco\HCF\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\player\Player;
use Taco\HCF\Main;
use function array_shift;

/**
 * Class KothCommand
 * @package Taco\HCF\commands
 *
 * got super lazy here so theres no good looking chat format
 *
 * but hey, it works, so chillax bud
 */
class KothCommand extends Command {

	private Vector3 $pos1;

	private Vector3 $pos2;

	public function __construct(string $name) {
		parent::__construct($name);
		$this->setDescription("Base koth command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
		if (Main::getInstance()->getServer()->isOp($sender->getName()) or !$sender instanceof Player) {
			$arg = array_shift($args);
			if ($arg == "") {
				$sender->sendMessage("Please provide a argument for koth!");
				return;
			}
			switch ($arg) {
				case "pos1":
					if ($sender instanceof Player) {
						$sender->sendMessage("set pos1");
						$pos = $sender->getPosition();
						$this->pos1 = new Vector3($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
					}
					break;
				case "pos2":
					if ($sender instanceof Player) {
						$sender->sendMessage("set pos2");
						$pos = $sender->getPosition();
						$this->pos2 = new Vector3($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
					}
					break;
				case "create":
					if ($sender instanceof Player) {
						$name = array_shift($args);
						if ($name == "") {
							$sender->sendMessage("Koth Create Usage: /koth create (name) (type EXAMPLE: 500, 500)");
							return;
						}
						$copy = $args;
						unset($args[0]);
						unset($args[1]);
						$type = join(" ", $copy);
						if ($type == "") {
							$sender->sendMessage("Koth Create Usage: /koth create (name) (type EXAMPLE: 500, 500)");
							return;
						}
						Main::getKothManager()->addKothArea($this->pos1, $this->pos2, $name, $type);
						$sender->sendMessage("creaturd koth");
					}
					break;
				case "forceStart":
					$name = array_shift($args);
					if ($name == "") {
						$sender->sendMessage("Please provide a name to start. Heres a list of koth names:");
						$names = [];
						foreach (Main::getInstance()->koth as $name => $info) {
							$names[] = $name;
						}
						$sender->sendMessage(implode(",", $names));
						return;
					}
					if (isset(Main::getInstance()->koth[$name])) {
						Main::getKothManager()->nextKoth = 0;
						Main::getKothManager()->kothRunning = true;
						Main::getKothManager()->startRandomKothMatch($name);
					} else {
						$sender->sendMessage("There is no koth with the name: $name");
					}
					break;
				default:
					$help = ["--- koth help ---",
					"/koth pos1 - set new arena pos1",
						"/koth pos2 - set new arena pos2",
						"/koth create (name) (type) HINT: the type is the corner example: 500, 500",
						"/koth forceStart (name)"
					];
					$sender->sendMessage(implode("\n", $help));
			}
		}
	}

}