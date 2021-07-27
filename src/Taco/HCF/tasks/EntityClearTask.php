<?php namespace Taco\HCF\tasks;

use pocketmine\entity\object\ItemEntity;
use pocketmine\scheduler\Task;
use Taco\HCF\Main;

class EntityClearTask extends Task {

	public function onRun() : void {
		foreach (Main::getInstance()->getServer()->getWorldManager()->getWorlds() as $world) {
			foreach ($world->getEntities() as $ent) {
				if ($ent instanceof ItemEntity) {
					$ent->flagForDespawn();
				}
			}
		}
		foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $player) {
			$player->sendPopup("§cCleared all Item Entities.");
		}
	}

}