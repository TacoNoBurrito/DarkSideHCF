<?php namespace Taco\HCF\other\kits\types;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use Taco\HCF\other\kits\KitType;

class Starter extends KitType {

	function getItems() : array {
		return [
			ItemFactory::getInstance()->get(ItemIds::APPLE, 0, 16),
			ItemFactory::getInstance()->get(ItemIds::FISHING_ROD, 0, 1)
		];
	}

	function getName() : string {
		return "Starter";
	}

	function getArmor() : array {
		return [];
	}

}