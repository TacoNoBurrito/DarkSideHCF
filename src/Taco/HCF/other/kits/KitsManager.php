<?php namespace Taco\HCF\other\kits;

use pocketmine\player\Player;
use Taco\HCF\Main;
use Taco\HCF\other\kits\forms\KitCooldownForm;
use Taco\HCF\other\kits\types\Bard;
use Taco\HCF\other\kits\types\Builder;
use Taco\HCF\other\kits\types\Diamond;
use Taco\HCF\other\kits\types\Rogue;
use Taco\HCF\other\kits\types\Starter;
use Taco\HCF\other\Time;

/**
 * Class KitsManager
 * @package Taco\HCF\other\kits
 *
 * Idk what i was thinking when i did this but it works ig
 */
class KitsManager {

	public const TYPE_STARTER = 0;
	public const TYPE_ROGUE = 1;
	public const TYPE_BARD = 2;
	public const TYPE_ARCHER = 3;
	public const TYPE_MINER = 4;
	public const TYPE_DIAMOND = 5;
	public const TYPE_MASTER = 6;
	public const TYPE_BUILDER = 7;

	public function giveKit(Player $player, int $type) : void {
		$cooldowns = Main::getInstance()->players[$player->getName()]["kits"];
		if ($type == self::TYPE_STARTER) {
			if ($cooldowns["starter"] > 0) {
				$player->sendForm(new KitCooldownForm(Main::getUtils()->intToTimeString($cooldowns["starter"])));
				return;
			}
			Main::getInstance()->players[$player->getName()]["kits"]["starter"] = Time::ONE_DAY;
			$this->giveCombine((new Starter())->getItems(), (new Starter())->getArmor(), $player);
		}
		if ($type == self::TYPE_BARD) {
			if ($cooldowns["bard"] > 0) {
				$player->sendForm(new KitCooldownForm(Main::getUtils()->intToTimeString($cooldowns["bard"])));
				return;
			}
			Main::getInstance()->players[$player->getName()]["kits"]["bard"] = Time::ONE_DAY;
			$this->giveCombine((new Bard())->getItems(), (new Bard())->getArmor(), $player);
		}
		if ($type == self::TYPE_ROGUE) {
			if ($cooldowns["rogue"] > 0) {
				$player->sendForm(new KitCooldownForm(Main::getUtils()->intToTimeString($cooldowns["rogue"])));
				return;
			}
			Main::getInstance()->players[$player->getName()]["kits"]["rogue"] = Time::ONE_DAY;
			$this->giveCombine((new Rogue())->getItems(), (new Rogue())->getArmor(), $player);
		}
		if ($type == self::TYPE_BUILDER) {
			if ($cooldowns["builder"] > 0) {
				$player->sendForm(new KitCooldownForm(Main::getUtils()->intToTimeString($cooldowns["builder"])));
				return;
			}
			Main::getInstance()->players[$player->getName()]["kits"]["builder"] = Time::ONE_DAY;
			$this->giveCombine((new Builder())->getItems(), (new Builder())->getArmor(), $player);
		}
		if ($type == self::TYPE_DIAMOND) {
			if ($cooldowns["diamond"] > 0) {
				$player->sendForm(new KitCooldownForm(Main::getUtils()->intToTimeString($cooldowns["diamond"])));
				return;
			}
			Main::getInstance()->players[$player->getName()]["kits"]["diamond"] = Time::ONE_DAY;
			$this->giveCombine((new Diamond())->getItems(), (new Diamond())->getArmor(), $player);
		}
	}

	public function giveCombine(array $items, array $armor, Player $player) : void {
		foreach ($items as $item) {
			if (!$player->getInventory()->canAddItem($item)) $player->getWorld()->dropItem($player->getPosition(), $item);
			else $player->getInventory()->addItem($item);
		}
		$val = 0;
		foreach ($armor as $item) {
			if (count($armor) < 1) return;
			if ($val == 0) if ($player->getArmorInventory()->getHelmet()->getId() == 0) {
				$player->getArmorInventory()->setHelmet($item);
			} else {
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			if ($val == 1) if ($player->getArmorInventory()->getChestplate()->getId() == 0) {
				$player->getArmorInventory()->setChestplate($item);
			} else {
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			if ($val == 2) if ($player->getArmorInventory()->getLeggings()->getId() == 0) {
				$player->getArmorInventory()->setLeggings($item);
			} else {
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			if ($val == 3) if ($player->getArmorInventory()->getBoots()->getId() == 0) {
				$player->getArmorInventory()->setBoots($item);
			} else {
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			$val++;
			if ($val > 3) return;
		}
	}

}