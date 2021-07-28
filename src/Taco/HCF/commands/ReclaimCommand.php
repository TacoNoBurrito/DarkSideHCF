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
            	if (Main::getInstance()->players[$sender->getName()]["reclaim"]) {
            		$sender->sendMessage("§r§l§7(§c!§7) §r§7You have already gotten your reclaim for this map!");
            		return;
				}
            	/*
				//DEFAULT /RECLAIM COMMAND
				for ($i = 0; $i <= 20; $i++) {
					Main::getPartnerManager()->givePartnerPackage($sender);
				}
				Main::getInstance()->players[$sender->getName()]["money"] += 100000;
				$sender->sendMessage("recieved beta pitems.");*/
				$rank = Main::getInstance()->players[$sender->getName()]["rank"];
				switch($rank) {
					case "None":
						Main::getInstance()->getServer()->broadcastMessage("§7[§c+§7] §f".$sender->getName()." §r§7has redeemed their §r§fDefault §r§7reclaim.");
						Main::getInstance()->players[$sender->getName()]["reclaim"] = true;
						for ($i = 0; $i <= 2; $i++) {
							Main::getPartnerManager()->givePartnerPackage($sender);
						}
						Main::getCrateUtils()->giveCrateKey($sender, "Common");
						Main::getCrateUtils()->giveCrateKey($sender, "Common");
						Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
						Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
						Main::getCrateUtils()->giveCrateKey($sender, "Rare");
						Main::getCrateUtils()->giveCrateKey($sender, "Rare");
						break;
					case "Mars":
						Main::getInstance()->getServer()->broadcastMessage("§7[§c+§7] §f".$sender->getName()." §r§7has redeemed their §r§6Mars §r§7reclaim.");
						Main::getInstance()->players[$sender->getName()]["reclaim"] = true;
						for ($i = 0; $i <= 4; $i++) {
							Main::getPartnerManager()->givePartnerPackage($sender);
						}
						Main::getCrateUtils()->giveCrateKey($sender, "Common");
						Main::getCrateUtils()->giveCrateKey($sender, "Common");
						Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
						Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
						Main::getCrateUtils()->giveCrateKey($sender, "Rare");
						Main::getCrateUtils()->giveCrateKey($sender, "Rare");
						Main::getCrateUtils()->giveCrateKey($sender, "Common");
						Main::getCrateUtils()->giveCrateKey($sender, "Common");
						Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
						Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
						Main::getCrateUtils()->giveCrateKey($sender, "Rare");
						Main::getCrateUtils()->giveCrateKey($sender, "Rare");
						break;
					case "Neptune":
						Main::getInstance()->getServer()->broadcastMessage("§7[§c+§7] §f".$sender->getName()." §r§7has redeemed their §r§aNeptune §r§7reclaim.");
						Main::getInstance()->players[$sender->getName()]["reclaim"] = true;
						for ($i = 0; $i <= 6; $i++) {
							Main::getPartnerManager()->givePartnerPackage($sender);
						}
						for ($i = 0; $i <= 2; $i ++) {
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
						}
						break;
					case "Saturn":
						Main::getInstance()->getServer()->broadcastMessage("§7[§c+§7] §f".$sender->getName()." §r§7has redeemed their §r§cNeptune §r§7reclaim.");
						Main::getInstance()->players[$sender->getName()]["reclaim"] = true;
						for ($i = 0; $i <= 8; $i++) {
							Main::getPartnerManager()->givePartnerPackage($sender);
						}
						for ($i = 0; $i <= 3; $i ++) {
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
						}
						break;
					case "Dark":
						Main::getInstance()->getServer()->broadcastMessage("§7[§c+§7] §f".$sender->getName()." §r§7has redeemed their §r§8Dark §r§7reclaim.");
						Main::getInstance()->players[$sender->getName()]["reclaim"] = true;
						for ($i = 0; $i <= 12; $i++) {
							Main::getPartnerManager()->givePartnerPackage($sender);
						}
						for ($i = 0; $i <= 5; $i ++) {
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "Common");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "UnCommon");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
							Main::getCrateUtils()->giveCrateKey($sender, "Rare");
						}
						break;
				}
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