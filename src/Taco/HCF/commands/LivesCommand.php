<?php namespace Taco\HCF\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\player\Player;
use Taco\HCF\Main;
use function array_shift;

class LivesCommand extends Command {

	public function __construct(string $name) {
		parent::__construct($name);
		$this->setDescription("Lives base command.");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
		$arg = array_shift($args);
		$squiggle = Main::MESSAGE_SQUIGGLE;
		if ($arg == "") {
			$sender->sendMessage(Main::MESSAGE_PREFIX."eDo /lives help {$squiggle}7to see a list of commands.");
			return;
		}
		if ($sender instanceof Player) {
			$lives = Main::getInstance()->players[$sender->getName()]["lives"];
			switch($arg) {
				case "amount":
					$sender->sendMessage(Main::MESSAGE_PREFIX."eYou have {$squiggle}d".$lives." {$squiggle}elives.");
					break;
				case "revive":
					$player = array_shift($args);
					if ($player == "") {
						$sender->sendMessage(Main::MESSAGE_PREFIX."7Please provide a player to revive.");
						return;
					}
					if (isset(Main::getInstance()->players[$player])) {
						if ($lives > 0) {
							Main::getInstance()->players[$sender->getName()]["lives"] -= 1;
							Main::getInstance()->players[$player]["lives"] += 1;
							$sender->sendMessage(Main::MESSAGE_PREFIX."fSuccessfully gave {$squiggle}a1 live{$squiggle}f to {$squiggle}a$player{$squiggle}f.");
						} else {
							$sender->sendMessage(Main::MESSAGE_PREFIX."7You do not have any lives, so you cannot revive this player.");
						}
					} else {
						$sender->sendMessage(Main::MESSAGE_PREFIX."7This player is not in our database. Remember! You must type in their EXACT name. (if the name has spaces, please surround their name in quotes like this: /lives revive \"Tacos Are Cool\").");
					}
					break;
				default:
					$this->sendHelp($sender);
			}
		} else {
			switch($arg) {
				case "revive":
					$player = array_shift($args);
					if ($player == "") {
						$sender->sendMessage(Main::MESSAGE_PREFIX."7Please provide a player to revive.");
						return;
					}
					if (isset(Main::getInstance()->players[$player])) {
							Main::getInstance()->players[$player]["lives"] += 1;
							$sender->sendMessage(Main::MESSAGE_PREFIX."fSuccessfully gave {$squiggle}a1 live{$squiggle}f to {$squiggle}a$player{$squiggle}f.");

					} else {
						$sender->sendMessage(Main::MESSAGE_PREFIX."7This player is not in our database. Remember! You must type in their EXACT name. (if the name has spaces, please surround their name in quotes like this: /lives revive \"Tacos Are Cool\").");
					}
					break;
				default:
					$this->sendHelp($sender);
			}
		}
	}

	public function sendHelp(CommandSender $sender) : void {
		$help = [
			"§7--- §l§cLIVES HELP §r§7---",
			"§r§f/lives amount §e- Check the amount of lives you have.",
			"§r§f/lives revive (player) §e- Give a player a life."
		];
		$adminHelp = [
			"§7--- §l§cCONSOLE HELP §r§7---",
			"§r§f/lives revive (player) §e- Give a life to a player"
		];
		$sender->sendMessage(implode("\n", $help));
		if (!$sender instanceof Player) $sender->sendMessage(implode("\n", $adminHelp));
	}

}