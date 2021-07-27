<?php namespace Taco\HCF\tasks;

use pocketmine\scheduler\Task;
use Taco\HCF\Main;
use Taco\HCF\other\Time;

class KothTask extends Task {

	public function onRun() : void {
		$koth = Main::getKothManager();
		if (!$koth->kothRunning) {
			Main::getKothManager()->nextKoth += 1;
			if (Main::getKothManager()->nextKoth > 5400) {
				Main::getKothManager()->nextKoth = 0;
				Main::getKothManager()->kothRunning = true;
				Main::getKothManager()->startRandomKothMatch();
			}
		} else {
			if ($koth->capper == "") {
				$player = Main::getKothManager()->getPlayerInKoth();
				if ($player !== null) {
					if (Main::getInstance()->players[$player->getName()]["faction"] == "None") {
						$player->sendPopup("§cYou must be in a faction to cap at koth.");
						return;
					}
					Main::getKothManager()->capper = $player->getName();
					Main::getInstance()->getServer()->broadcastMessage("§eThe KoTH is now being capped by §d".$player->getName()."§e! §7(".Main::getInstance()->players[$player->getName()]["faction"].")");
				}
			} else {
				if (Main::getKothManager()->isPlayerInKoth(Main::getInstance()->getServer()->getPlayerByPrefix($koth->capper))) {
					Main::getKothManager()->kothTime -= 1;
					if (Main::getKothManager()->kothTime < 1) {
						Main::getInstance()->getServer()->broadcastMessage("§e§b \n§eThe KoTH has been capped by §d".$koth->capper."§7 (".Main::getInstance()->players[$koth->capper]["faction"]."§7)\n§e§b ");
						Main::getKothManager()->kothRunning = false;
					}
				} else {
					Main::getInstance()->getServer()->broadcastMessage("§eThe KoTH is no longer being capped by §d". $koth->capper."§e!");
					Main::getKothManager()->kothTime = Time::TEN_MINS_IN_SECONDS;
					Main::getKothManager()->capper = "";
				}
			}
		}
	}

}