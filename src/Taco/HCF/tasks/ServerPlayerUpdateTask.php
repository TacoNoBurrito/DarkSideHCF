<?php namespace Taco\HCF\tasks;

use pocketmine\scheduler\Task;
use Taco\HCF\Main;

class ServerPlayerUpdateTask extends Task {

	public function onRun() : void {
		$data = Main::getInstance()->players;
		foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $player) {
			$fac = $data[$player->getName()]["faction"];
			if ($fac == "None") {
				$player->setNameTag("§c".$player->getName());
			} else {
				$dtr = Main::getInstance()->factions[$fac]["dtr"];
				$player->setNameTag("§e[§c".$dtr.(Main::getFactionManager()->getExpectedDTR($fac) < $dtr ? "§c" : "§a")." □§r§e]"."\n§c".$player->getName());
			}
		}
	}

}