<?php namespace Taco\HCF\manager;

use pocketmine\player\Player;
use Taco\HCF\Main;

/**
 * Class PlayerManager
 * @package Taco\HCF\manager
 *
 * Recode data saving later. Is fast and works but needs to look better
 * code wise.
 */
class PlayerManager {

    private const DEFAULT_VALUES = [
        "claim" => "null",
        "archertag-time" => 0,
        "class" => "None",
        "timers" => [
            "pearl-cooldown" => 0,
            "spawntag" => 0,
			"switcher" => 0,
			"slapper" => 0,
			"rogue" => 0
        ],
        "teleporting" => false,
        "teleporting-location" => "",
        "teleport-time-remaining" => 0,
        "bard-energy" => 0,
		"claim-pos1" => null,
		"claim-pos2" => null,
		"claiming" => false,
		"has-pillars-at" => [],
		"stuck" => 0
    ];

    public function loadPlayer(Player $player) : void {
        $result = isset(Main::getInstance()->players[$player->getName()]);
        if (!$result) Main::getInstance()->players[$player->getName()] = [
            "money" => 0,
            "rank" => "None",
            "faction" => "None",
            "deathban-time" => 0,
            "claim" => "null",
            "kits" => [
                "starter" => 0,
                "rogue" => 0,
                "bard" => 0,
                "archer" => 0,
                "diamond" => 0,
                "builder" => 0,
                "master" => 0
            ],
            "timers" => [
                "pearl-cooldown" => 0,
                "spawntag" => 0,
				"switcher" => 0,
				"slapper" => 0,
				"rogue" => 0
            ],
            "kills" => 0,
            "deaths" => 0,
            "killstreak" => 0,
            "archertag-time" => 0,
            "class" => "None",
            "pvp-timer" => 1800,
            "lives" => 3,
            "invites" => [],
            "teleporting" => false,
            "teleporting-location" => "",
            "teleport-time-remaining" => 0,
            "bard-energy" => 0,
            "reclaim" => false,
			"claim-pos1" => null,
			"claim-pos2" => null,
			"claiming" => false,
			"has-pillars-at" => [],
			"stuck" => 0,
			"didDie" => false
        ]; else $this->insert_default_values($player);
    }

    public function insert_default_values(Player $player) : void {
        foreach (self::DEFAULT_VALUES as $key => $keyVal) {
            if (isset(Main::getInstance()->players[$player->getName()][$key])) continue;
            Main::getInstance()->players[$player->getName()][$key] = $keyVal;
        }
    }

}