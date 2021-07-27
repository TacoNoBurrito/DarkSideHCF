<?php namespace Taco\HCF\other\kits\types;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use Taco\HCF\other\kits\KitType;

class Builder extends KitType {


	public function getName() : string {
		return "Builder";
	}

	public function getItems() : array {
		$ret = [];
		$wood = ItemFactory::getInstance()->get(ItemIds::LOG, 0, 64);
		$ret[] = $wood;
		$fenceGate = ItemFactory::getInstance()->get(ItemIds::FENCE_GATE, 0, 32);
		$ret[] = $fenceGate;
		$glass = ItemFactory::getInstance()->get(ItemIds::GLASS, 0, 64);
		for ($i = 0; $i <= 3; $i++) {
			$ret[] = $glass;
		}
		$chest = ItemFactory::getInstance()->get(ItemIds::CHEST, 0, 32);
		$ret[] = $chest;
		$ironDoor = ItemFactory::getInstance()->get(ItemIds::IRON_DOOR, 0, 8);
		$ret[] = $ironDoor;
		$ret[] = ItemFactory::getInstance()->get(ItemIds::APPLE,0,32);
		$ret[] = ItemFactory::getInstance()->get(ItemIds::WOOL, 0 ,64);
		for ($i = 0; $i <= 3; $i++) $ret[] = ItemFactory::getInstance()->get(ItemIds::WOOL, 0 ,64);
		return $ret;
	}

	public function getArmor() : array {
		return [];
	}

}