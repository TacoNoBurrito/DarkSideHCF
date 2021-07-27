<?php namespace Taco\HCF\other\kits\types;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use Taco\HCF\other\kits\KitType;

class Rogue extends KitType {

	function getItems() : array {
		$ret = [];
		$prefix = "§r§7[§6Rogue§7] §r§f";
		$sword = ItemFactory::getInstance()->get(ItemIds::DIAMOND_SWORD);
		$unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2);
		$sword->setCustomName($prefix."Sword");
		$sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2);
		$sword->addEnchantment($sharpness);
		$sword->addEnchantment($unbreaking);
		$pearls = ItemFactory::getInstance()->get(ItemIds::ENDER_PEARL, 0, 16);
		$food = ItemFactory::getInstance()->get(364, 0, 32);
		$potion = ItemFactory::getInstance()->get(438, 22);
		$ret[] = $sword;
		$ret[] = $pearls;
		for ($i = 0; $i <= 7; $i++) {
			$ret[] = $potion;
		}
		$ret[] = $food;
		for ($i = 0; $i <= 9; $i++) {
			$ret[] = ItemFactory::getInstance()->get(ItemIds::GOLD_SWORD,0,1);
		}
		for ($i = 0; $i <= (27-9); $i++) {
			$ret[] = $potion;
		}
		return $ret;
	}

	function getName() : string {
		return "Rogue";
	}

	function getArmor() : array {
		$unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2);
		$prefix = "§7[§6Rogue§7] §r§f";
		$helmet = ItemFactory::getInstance()->get(ItemIds::CHAIN_HELMET);
		$helmet->setCustomName($prefix."Helmet");
		$protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3);
		$helmet->addEnchantment($protection);
		$helmet->addEnchantment($unbreaking);
		$chestplate = ItemFactory::getInstance()->get(ItemIds::CHAIN_CHESTPLATE);
		$chestplate->setCustomName($prefix."Chestplate");
		$chestplate->addEnchantment($protection);
		$chestplate->addEnchantment($unbreaking);
		$leggings = ItemFactory::getInstance()->get(ItemIds::CHAIN_LEGGINGS);
		$leggings->setCustomName($prefix."Leggings");
		$leggings->addEnchantment($protection);
		$leggings->addEnchantment($unbreaking);
		$boots = ItemFactory::getInstance()->get(ItemIds::CHAIN_BOOTS);
		$boots->setCustomName($prefix."Boots");
		$boots->addEnchantment($protection);
		$boots->addEnchantment($unbreaking);
		return [$helmet,$chestplate,$leggings,$boots];
	}

}