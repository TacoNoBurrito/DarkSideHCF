<?php namespace Taco\HCF\tasks;

use pocketmine\scheduler\Task;
use Taco\HCF\Main;

class NESWTask extends Task {

	public function onRun() : void {
		foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $player) {
			Main::getInstance()->hud->setTitleFor([$player], Main::getUtils()->getDirectionFacing($player));
		}
	}

}