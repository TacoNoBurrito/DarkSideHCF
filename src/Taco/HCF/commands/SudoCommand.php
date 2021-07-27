<?php namespace Taco\HCF\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Taco\HCF\Main;
use function array_shift;

class SudoCommand extends Command {

    public function __construct(string $name) {
        parent::__construct($name);
        $this->setDescription("Base sudo");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if (!$sender instanceof Player or Main::getInstance()->getServer()->isOp($sender->getName())) {
            $player = array_shift($args);
            if ($player == "") {
                $sender->sendMessage("§ePlease provide a player to sudo.");
                return;
            }
            $player = Main::getInstance()->getServer()->getPlayerByPrefix($player);
            if ($player == null) {
                $sender->sendMessage("§eThis player is not online or doesn't exist.");
                return;
            }
            $msg = join(" ", $args);
            $player->chat($msg);
        }
    }

}