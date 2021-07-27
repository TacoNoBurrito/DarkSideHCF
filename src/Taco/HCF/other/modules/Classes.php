<?php namespace Taco\HCF\other\modules;

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\EffectIds;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use Taco\HCF\Main;
use function var_dump;

class Classes {

    private array $classEffects = [
        "bard" => [
            "1:2",
			"11:2",
			"10:1"
        ],
		"rogue" => [
			"11:1",
			"8:2",
			"1:3"
		]
    ];

    public function reloadClass(Player $player) : void {
        $preClass = Main::getInstance()->players[$player->getName()]["class"];
        $class = "None";
        $classes = [
            "bard" => [
                ItemIds::GOLD_HELMET,
                ItemIds::GOLD_CHESTPLATE,
                ItemIds::GOLD_LEGGINGS,
                ItemIds::GOLD_BOOTS
            ],
			"rogue" => [
				ItemIds::CHAINMAIL_HELMET,
				ItemIds::CHAINMAIL_CHESTPLATE,
				ItemIds::CHAINMAIL_LEGGINGS,
				ItemIds::CHAINMAIL_BOOTS
			]
        ];
        $armor = $player->getArmorInventory();
        $helmet = $armor->getHelmet()->getId();
        $chestplate = $armor->getChestplate()->getId();
        $leggigns = $armor->getLeggings()->getId();
        $boots = $armor->getBoots()->getId();
        foreach ($classes as $name => $needs) {
            if (
                $needs[0] == $helmet and
                $needs[1] == $chestplate and
                $needs[2] == $leggigns and
                $needs[3] == $boots
            ) {
                $class = $name;
                break;
            }
        }
        if ($preClass !== $class) $this->removeAllClassEffects($player);
        if ($preClass == "bard" and $class !== "bard") Main::getInstance()->players[$player->getName()]["bard-energy"] = 0;
        Main::getInstance()->players[$player->getName()]["class"] = $class;
        $this->giveEffectsOfClass($player, $class);
    }

    public function giveEffectsOfClass(Player $player, string $class) : void {
        if ($class == "None") return;
        foreach ($this->classEffects[$class] as $eff) {
            $exp = explode(":", $eff);
            $inst = new EffectInstance(EffectIdMap::getInstance()->fromId((int)$exp[0]), 2147483647, (int)$exp[1]);
            $player->getEffects()->add($inst);
        }
    }

    public function removeAllClassEffects(Player $player) : void {
        foreach ($player->getEffects()->all() as $eff) {
            if ($eff->getDuration() > 1000) $player->getEffects()->remove($eff->getType());
        }
    }

}