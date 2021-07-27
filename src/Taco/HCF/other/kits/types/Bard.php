<?php namespace Taco\HCF\other\kits\types;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use Taco\HCF\other\kits\KitType;

class Bard extends KitType {

	function getItems() : array {
		$ret = [];
		$prefix = "§r§7[§eBard§7] §r§f";
		$sword = ItemFactory::getInstance()->get(ItemIds::DIAMOND_SWORD);
		$unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2);
		$sword->setCustomName($prefix."Sword");
		$sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2);
		$sword->addEnchantment($sharpness);
		$sword->addEnchantment($unbreaking);
		$pearls = ItemFactory::getInstance()->get(ItemIds::ENDER_PEARL, 0, 16);
		$food = ItemFactory::getInstance()->get(364, 0, 32);
		$potions = ItemFactory::getInstance()->get(438, 22);
		$ret[] = $sword;
		$ret[] = $pearls;
		for ($i = 0; $i <= 2; $i++) {
			$ret[] = $potions;
		}
		$ret[] = ItemFactory::getInstance()->get(ItemIds::BLAZE_POWDER, 0, 16);
		$ret[] = ItemFactory::getInstance()->get(ItemIds::SUGAR, 0, 16);
		$ret[] = ItemFactory::getInstance()->get(ItemIds::IRON_INGOT, 0, 16);
		$ret[] = $food;
		for ($i = 0; $i <= (33-8); $i++) {
			$ret[] = $potions;
		}
		return $ret;
	}

	function getName() : string {
		return "Bard";
	}

	function getArmor() : array {
		$unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2);
		$prefix = "§r§7[§eBard§7] §r§f";
		$helmet = ItemFactory::getInstance()->get(ItemIds::GOLD_HELMET);
		$helmet->setCustomName($prefix."Helmet");
		$protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3);
		$helmet->addEnchantment($protection);
		$helmet->addEnchantment($unbreaking);
		$chestplate = ItemFactory::getInstance()->get(ItemIds::GOLD_CHESTPLATE);
		$chestplate->setCustomName($prefix."Chestplate");
		$chestplate->addEnchantment($protection);
		$chestplate->addEnchantment($unbreaking);
		$leggings = ItemFactory::getInstance()->get(ItemIds::GOLD_LEGGINGS);
		$leggings->setCustomName($prefix."Leggings");
		$leggings->addEnchantment($protection);
		$leggings->addEnchantment($unbreaking);
		$boots = ItemFactory::getInstance()->get(ItemIds::GOLD_BOOTS);
		$boots->setCustomName($prefix."Boots");
		$boots->addEnchantment($protection);
		$boots->addEnchantment($unbreaking);
		return [$helmet,$chestplate,$leggings,$boots];
	}

}