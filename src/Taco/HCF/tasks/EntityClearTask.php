<?php namespace Taco\HCF\tasks;

use pocketmine\entity\object\ItemEntity;
use pocketmine\scheduler\Task;
use Taco\HCF\Main;
use Taco\HCF\other\crates\entity\PulsatingCrateEntity;

class EntityClearTask extends Task {

	public function onRun() : void {
		foreach (Main::getInstance()->getServer()->getWorldManager()->getWorlds() as $world) {
			foreach ($world->getEntities() as $ent) {
				if ($ent instanceof ItemEntity and !($ent instanceof PulsatingCrateEntity)) {
					$ent->flagForDespawn();
				}
			}
		}
		foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $player) {
			$player->sendPopup("§cCleared all Item Entities.");
		}
	}

}