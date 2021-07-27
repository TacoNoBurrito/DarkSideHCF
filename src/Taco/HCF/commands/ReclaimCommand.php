<?php namespace Taco\HCF\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use Taco\HCF\Main;
use Taco\HCF\partner\entity\PartnerRollEntity;
use function array_shift;

class ReclaimCommand extends Command {

    public function __construct(string $name) {
        parent::__construct($name);
        $this->setDescription("Get your reclaim!");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if ($sender instanceof Player) {
            $arg = array_shift($args);
            if ($arg !== "" and Main::getInstance()->getServer()->isOp($sender->getName())) {
                if ($arg == "admin") {
                    $type = array_shift($args);
                    switch ($type) {
                        case "givepp":
                            $player = array_shift($args);
                            if ($player == "") {
                                $sender->sendMessage("§ePlease provide a player to give a partner package to.");
                                return;
                            }
                            $player = Main::getInstance()->getServer()->getPlayerByPrefix($player);
                            if ($player == null) {
                                $sender->sendMessage("§eThis player is not online or doesn't exist.");
                                return;
                            }
                            Main::getPartnerManager()->givePartnerPackage($player);
                            $sender->sendMessage("§eGiven partner package to §2".$player->getName()."§r§e.");
                            break;
                    }
                }
            } else {
				//DEFAULT /RECLAIM COMMAND
				for ($i = 0; $i <= 20; $i++) {
					Main::getPartnerManager()->givePartnerPackage($sender);
				}
				Main::getInstance()->players[$sender->getName()]["money"] += 100000;
				$sender->sendMessage("recieved beta pitems.");
            }
        } else {
        	//CONSOLE
			$player = array_shift($args);
			if ($player == "") {
				$sender->sendMessage("§ePlease provide a player to give a partner package to.");
				return;
			}
			$player = Main::getInstance()->getServer()->getPlayerByPrefix($player);
			if ($player == null) {
				$sender->sendMessage("§eThis player is not online or doesn't exist.");
				return;
			}
			Main::getPartnerManager()->givePartnerPackage($player);
			$sender->sendMessage("§eGiven partner package to §2".$player->getName()."§r§e.");
		}
    }

}