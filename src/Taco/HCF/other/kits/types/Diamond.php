<?php namespace Taco\HCF\other\kits\types;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use Taco\HCF\other\kits\KitType;

class Diamond extends KitType {


	public function getName() : string {
		return "Diamond";
	}

	public function getItems() : array {
		$ret = [];
		$prefix = "§r§7[§bDiamond§7] §r§f";
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
			$ret[] = $potion;
		}
		for ($i = 0; $i <= (27-9); $i++) {
			$ret[] = $potion;
		}
		return $ret;
	}

	public function getArmor() : array {
		$unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2);
		$prefix = "§7[§bDiamond§7] §r§f";
		$helmet = ItemFactory::getInstance()->get(ItemIds::DIAMOND_HELMET);
		$helmet->setCustomName($prefix."Helmet");
		$protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1);
		$helmet->addEnchantment($protection);
		$helmet->addEnchantment($unbreaking);
		$chestplate = ItemFactory::getInstance()->get(ItemIds::DIAMOND_CHESTPLATE);
		$chestplate->setCustomName($prefix."Chestplate");
		$chestplate->addEnchantment($protection);
		$chestplate->addEnchantment($unbreaking);
		$leggings = ItemFactory::getInstance()->get(ItemIds::DIAMOND_LEGGINGS);
		$leggings->setCustomName($prefix."Leggings");
		$leggings->addEnchantment($protection);
		$leggings->addEnchantment($unbreaking);
		$boots = ItemFactory::getInstance()->get(ItemIds::DIAMOND_BOOTS);
		$boots->setCustomName($prefix."Boots");
		$boots->addEnchantment($protection);
		$boots->addEnchantment($unbreaking);
		return [$helmet,$chestplate,$leggings,$boots];
	}

}