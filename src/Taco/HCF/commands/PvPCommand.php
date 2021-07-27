<?php namespace Taco\HCF\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Taco\HCF\Main;
use function array_shift;

class PvPCommand extends Command {

    public function __construct(string $name) {
        parent::__construct($name);
        $this->setDescription("Usage: /pvp help");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if ($sender instanceof Player) {
            $arg = array_shift($args);
            $help_message = "§r§l§7(§c!§7) §r§fUsage: /pvp <[off|disable]|time>";
            if ($arg == "" or $arg == "help") {
                $sender->sendMessage($help_message);
                return;
            }
            switch(strtolower($arg)) {
                case "off":
                case "disable":
				case "enable":
				case "on":
                    if (Main::getInstance()->players[$sender->getName()]["pvp-timer"] < 1) {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§cYou do not have a active pvp timer!");
                        return;
                    }
                    $sender->sendMessage("§r§l§7(§c!§7) §r§fDisabled pvp timer.");
                    Main::getInstance()->players[$sender->getName()]["pvp-timer"] = 0;
                    break;
                case "time":
                    $sender->sendMessage("§r§l§7(§c!§7) §r§fYou currently have: §a".Main::getUtils()->secondsToEnderpearlCD(Main::getInstance()->players[$sender->getName()]["pvp-timer"])." §r§fleft on your pvp-timer!");
                    break;
            }
        }
    }

}