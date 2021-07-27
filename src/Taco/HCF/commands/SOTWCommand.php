<?php namespace Taco\HCF\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Taco\HCF\Main;
use function array_shift;

class SOTWCommand extends Command {

    public function __construct(string $name) {
        parent::__construct($name);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if (!$sender instanceof Player or Main::getInstance()->getServer()->isOp($sender->getName()) or $sender->getName() == Main::SERVER_OWNER_IGN) {
            $arg = array_shift($args);
            switch($arg) {
                case "on":
                    Main::getInstance()->sotw_timer = 5400;
                    Main::getInstance()->getServer()->broadcastMessage("§l§e§a   \n §r§l§7(§c!§7) §r§f§l§aSOTW §r§ahas commenced!\n §l§e§a");
                    break;
                case "off":
                    Main::getInstance()->getServer()->broadcastMessage("§l§e§a   \n §r§l§7(§c!§7) §r§f§l§aSOTW §r§ahas §cbeen disabled!\n §l§e§a");
                    Main::getInstance()->sotw_timer = 0;
                    break;
                default:
                    $sender->sendMessage("§r§l§7(§c!§7) §r§fUsage: /sotw <on|off>");
            }
        }
    }

}