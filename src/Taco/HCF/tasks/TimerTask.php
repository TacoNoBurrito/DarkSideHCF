<?php namespace Taco\HCF\tasks;

use pocketmine\scheduler\Task;
use Taco\HCF\Main;
use function in_array;

/**
 * Class TimerTask
 * @package Taco\HCF\tasks
 *
 * A big class that basically does x - 1
 *
 * why does this look so confusing, i think i should stop adding crap to it
 * note: i added dtr and now its even bigger
 * note: im back once again for kits
 */
class TimerTask extends Task {

    public function onRun() : void {
        foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $player) {
            foreach (Main::getInstance()->players[$player->getName()]["timers"] as $name => $time) {
                if ($time > 0) Main::getInstance()->players[$player->getName()]["timers"][$name] -= 1;
            }
            if (!isset(Main::getInstance()->players[$player->getName()]["bard-energy"])) Main::getInstance()->players[$player->getName()]["bard-energy"] = 0;
            if (Main::getInstance()->players[$player->getName()]["pvp-timer"] > 0) Main::getInstance()->players[$player->getName()]["pvp-timer"] -= 1;
        }
        if (Main::getInstance()->sotw_timer > 0) Main::getInstance()->sotw_timer -= 1;
        foreach (Main::getInstance()->players as $name => $info) {
        	foreach ($info["kits"] as $namee => $time) {
        		if ($time > 0) Main::getInstance()->players[$name]["kits"][$namee] -= 1;
			}
            foreach ($info["invites"] as $n => $time) {
                if (($time - 1) < 1) {
                    $p = Main::getInstance()->getServer()->getPlayerByPrefix($n);
                    if ($p !== null) {
                        $p->sendMessage("§r§l§7(§c!§7) §r§fYour invite from §e".$n." §fhas expired.");
                    }
                    unset(Main::getInstance()->players[$name]["invites"][$n]);
                }
                if (!isset(Main::getInstance()->players[$name]["invites"][$n])) continue;
                Main::getInstance()->players[$name]["invites"][$n] -= 1;
            }
            if ($info["deathban-time"] > 1) Main::getInstance()->players[$name]["deathban-time"] -= 1;
        }
        foreach (Main::getInstance()->factions as $name => $info) {
        	if ($info["dtr-freeze"] > 0) {
        		Main::getInstance()->factions[$name]["dtr-freeze"] -= 1;
			}
        	if (!in_array("dtr", $info)) continue;
        	if ($info["dtr-freeze"] < 1 and (float)$info["dtr"] < Main::getFactionManager()->getExpectedDTR($name)) {
        		if ($info["dtr-freeze-time"] < 1) {
        			if ((Main::getInstance()->factions[$name]["dtr"] + 1) > Main::getFactionManager()->getExpectedDTR($name)) {
        				Main::getInstance()->factions[$name]["dtr"] = (string)Main::getFactionManager()->getExpectedDTR($name);
					} else {
        				Main::getInstance()->factions[$name]["dtr-freeze-time"] = 120;
        				Main::getInstance()->factions[$name]["dtr"] = (string)((float)Main::getInstance()->factions[$name]["dtr"] + 1);
        				foreach (Main::getFactionManager()->getMembersInFaction($name) as $member) {
        					if (Main::getInstance()->getServer()->getPlayerByPrefix($member) !== null) {
        						$pl = Main::getInstance()->getServer()->getPlayerByPrefix($member);
        						$pl->sendMessage("§a§o + 1 DTR");
							}
						}
					}
				}
			}
		}
    }

}