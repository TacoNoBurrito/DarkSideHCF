<?php namespace Taco\HCF\tasks;

use pocketmine\scheduler\Task;
use Taco\HCF\Main;

class BardRegenTask extends Task {

    public function onRun() : void {
        foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $data = Main::getInstance()->players[$player->getName()];
            if ($data["class"] == "bard") {
                if ($data["bard-energy"] < 120) {
                    if (($data["bard-energy"] + 3) > 120) {
                        Main::getInstance()->players[$player->getName()]["bard-energy"] = 120;
                    } else {
                        Main::getInstance()->players[$player->getName()]["bard-energy"] = ($data["bard-energy"] + 3);
                    }
                }
            }
        }
    }

}