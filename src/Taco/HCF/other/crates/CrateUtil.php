<?php namespace Taco\HCF\other\crates;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\lang\Language;
use pocketmine\nbt\NoSuchTagException;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use Taco\HCF\Main;
use Taco\HCF\other\crates\entity\PulsatingCrateEntity;
use Taco\HCF\other\entities\LogoutVillager;
use function array_rand;
use function is_string;
use function str_replace;

class CrateUtil {

	public const TYPES = [
		"Common",
		"Uncommon",
		"Rare",
		"Legendary",
		"Koth"
	];

	public const COOL_NAME_TYPE_MAP = [
		"Common" => "§r§l§aCommon",
		"Uncommon" => "§r§l§9UnCommon",
		"Rare" => "§r§l§dRare",
		"Legendary" => "§r§l§6Legendary",
		"Koth" => "§r§l§fKoTH"
	];

	public const CRATE_NAME_COLORS = [
		"Common" => "§r§l§a",
		"Uncommon" => "§r§l§9",
		"Rare" => "§r§l§d",
		"Legendary" => "§r§l§6",
		"Koth" => "§r§l§f"
	];

	public array $rewards = [];

	public function giveCrateKey(Player $player, string $type) {
		$item = ItemFactory::getInstance()->get(ItemIds::TRIPWIRE_HOOK);
		$item->setCustomName(self::COOL_NAME_TYPE_MAP[$type]." §r§7Key");
		$item->setLore(["§r§7Take this key to spawn and use it on the $type crate!"]);
		$item->getNamedTag()->setTag("ench", new ListTag([]));
		$item->getNamedTag()->setString("type", $type);
		$item->getNamedTag()->setString("key", "yes");
		$player->getInventory()->addItem($item);
	}

	public function isRealCrateKey(Item $item) : bool {
		try {
			$e = $item->getNamedTag()->getString("key");
			return $e !== null;
		} catch (NoSuchTagException $ex) {
			return false;
		}
	}

	public function giveCrateReward(Player $player, string $type) : void {
		$reward = $this->rewards[$type][array_rand($this->rewards[$type])];
		if (is_string($reward)) {
			Main::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(Main::getInstance()->getServer(), new Language(Language::FALLBACK_LANGUAGE)), str_replace("{name}", $player->getName(), explode(":", $reward)[0]));
			$player->sendMessage("§eYou have gotten a §r§d".explode(":", $reward)[1]."§e!");
			return;
		} else {
			$player->getInventory()->addItem($reward);
		}
		$player->sendMessage("§eYou have gotten your reward from the ".self::COOL_NAME_TYPE_MAP[$type]." §r§ecrate!");
	}

	public function initCrates() : void {
		foreach (self::TYPES as $name) {
			$this->rewards[$name] = [];
		}
		$this->rewards["Common"] = [
			ItemFactory::getInstance()->get(ItemIds::IRON_BLOCK, 0, 16),
			ItemFactory::getInstance()->get(ItemIds::GOLD_BLOCK, 0, 8),
			ItemFactory::getInstance()->get(ItemIds::DIAMOND_BLOCK, 0, 4),
			"reclaim {name}:Partner Package"
		];
	}

	public function reloadCratePulsatingFloatingTextEntities() : void {
		foreach (Main::getInstance()->getServer()->getWorldManager()->getWorlds() as $worlds) {
			foreach ($worlds->getEntities() as $ent) {
				if ($ent instanceof PulsatingCrateEntity) {
					$ent->flagForDespawn();
				}
			}
		}
		foreach (Main::getInstance()->crate as $name => $pos) {
			$toPos = Main::getUtils()->stringToVec3($pos);
			Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->loadChunk($toPos->getX() >> 4, $toPos->getZ() >> 4);
			$entity = new PulsatingCrateEntity(Location::fromObject($toPos->floor()->add(0.5, 2, 0.5), Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld()), null, $name);
			$entity->spawnToAll();
		}
	}

}