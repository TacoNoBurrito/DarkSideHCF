<?php namespace Taco\HCF\manager;

use pocketmine\entity\Effect;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use Taco\HCF\Main;
use function in_array;
use function key;
use function wordwrap;

class FactionManager {

    public array $BARD_ITEM_MAP = [
        353 => [
            "name" => "Speed II",
            "effects" => [
                "1:2:5"
            ],
            "energy-needed" => 30
        ],
		265 => [
			"name" => "Regeneration III",
			"effects" => [
				"11:3:10"
			],
			"energy-needed" => 25
		]
    ];

    public function createFaction(Player $owner, string $name) : void {
        Main::getInstance()->factions[$name] = [
            "owner" => $owner->getName(),
            "captains" => [],
            "members" => [],
            "power" => 0,
            "balance" => 0,
            "claim" => "None",
            "dtr" => "1.1",
            "dtr-freeze" => 0,
            "factions-made-raidable" => 0,
            "home" => "None",
			"dtr-freeze-time" => 0
        ];
        Main::getInstance()->players[$owner->getName()]["faction"] = $name;
        Main::getInstance()->getServer()->broadcastMessage("§eFaction §c{$name}§e has been §acreated §eby §r§f".$owner->getName());
    }

    public function getExpectedDTR(string $faction) : float {
    	return count($this->getMembersInFaction($faction)) + 1.1;
	}

    public function getMembersInFaction(string $faction) : array {
        if (isset(Main::getInstance()->factions[$faction])) {
            $members = [];
            $fac = Main::getInstance()->factions[$faction];
            $members[] = $fac["owner"];
            foreach ($fac["captains"] as $name) {
                $members[] = $name;
            }
            foreach ($fac["members"] as $name) {
                $members[] = $name;
            }
            return $members;
        }
        return [];
    }

    public function isLeaderOfFaction(string $name, string $faction) : bool {
        return Main::getInstance()->factions[$faction]["owner"] == $name;
    }

    public function isCaptainInFaction(string $name, string $faction) : bool {
        return in_array($name, Main::getInstance()->factions[$faction]["captains"]);
    }

    public function giveClaimWand(Player $player) : void {
    	$wand = ItemFactory::getInstance()->get(ItemIds::WOODEN_AXE);
    	$wand->setCustomName("§r§aClaiming Wand");
    	$wand->setLore([wordwrap("§r§fUse this wand to claim land for your faction! To use this wand you must be in claiming mode, and you have to be your faction leader!", 40)]);
		$player->getInventory()->addItem($wand);
    }

    public function isRealClaimWand(Item $wand) : bool {
    	return $wand->getCustomName() == "§r§aClaiming Wand";
	}

}