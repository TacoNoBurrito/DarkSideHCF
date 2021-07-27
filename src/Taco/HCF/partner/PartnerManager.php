<?php namespace Taco\HCF\partner;

use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\NoSuchTagException;
use pocketmine\player\Player;
use Taco\HCF\partner\entity\PartnerRollEntity;
use function hex2bin;
use function strpos;
use function wordwrap;

class PartnerManager {

	public const PARTNER_ITEM_NAMES = [
		"switcher" => "§r§l§6Switcher Ball",
		"slapper" => "§r§l§cSlapper Stick"
	];

    public function joinMappedSquare(array $array) : string {
        $square = hex2bin("e29688");
        $string = "";
        $count = 0;
        foreach ($array as $arr) {
            $count++;
            $string .= "§".$arr[0].$square;
            if ($count > 9) {
                $count = 0;
                $string .= "\n";
            }
        }
        return $string;
    }

    public function isValidPartnerPackage(Item $item) : bool {
		try {
			$e = $item->getNamedTag()->getString("isRealPartnerPackage");
			return $e !== null;
		} catch (NoSuchTagException $ex) {
			return false;
		}
    }

    public function hasTag(Item $item, string $tag) : bool {
    	try {
    		$e = $item->getNamedTag()->getString($tag);
    		return $e !== null;
		} catch (NoSuchTagException $ex) {
    		return false;
		}
	}

    public function givePartnerPackage(Player $player) : void {
        $item = ItemFactory::getInstance()->get(ItemIds::ENDER_CHEST);
        $item->clearCustomBlockData();
        $item->setCustomName("§r§l§6Partner Package");
        $item->setLore([wordwrap("§r§fTap this §l§6Partner Package §r§fon the ground to redeem partner rewards.",40)]);
        $item->getNamedTag()->setString("isRealPartnerPackage", "true");
        if ($player->getInventory()->canAddItem($item)) $player->getInventory()->addItem($item);
        else $player->getPosition()->getWorld()->dropItem($player->getPosition(), $item);
    }

    public function openPartnerPackage(Player $player, Block $pos) : void {
        $entity = new PartnerRollEntity(Location::fromObject($pos->getPos(), $pos->getPos()->getWorld()), null, $player);
        $entity->spawnToAll();
        foreach ($player->getInventory()->getContents() as $index => $item) {
            if ($this->isValidPartnerPackage($item)) {
                $item->setCount($item->getCount() - 1);
                $player->getInventory()->setItem($index, $item);
                break;
            }
        }
    }

    public function giveSwitcher(Player $player, int $amount = 1) : void {
    	$item = ItemFactory::getInstance()->get(ItemIds::SNOWBALL);
    	$item->setCustomName(self::PARTNER_ITEM_NAMES["switcher"]);
    	$item->setLore(["§r§fHit another player with this snowball to\nswitch positions with the player!\n\n§ogood for trapping >:)"]);
    	$item->setCount($amount);
    	$item->getNamedTag()->setString("switcher", "lol");
		$player->getInventory()->canAddItem($item) ? $player->getInventory()->addItem($item) : $player->getWorld()->dropItem($player->getPosition(), $item);
	}

	public function giveSlapper(Player $player, int $amount = 1) : void {
		$item = ItemFactory::getInstance()->get(ItemIds::STICK);
		$item->setCustomName(self::PARTNER_ITEM_NAMES["slapper"]);
		$item->setLore(["§r§fHit another player with this stick to\nflip their head 180degrees!\n\n§ogood for combos >:)"]);
		$item->setCount($amount);
		$item->getNamedTag()->setString("slapper", "lol");
		$player->getInventory()->canAddItem($item) ? $player->getInventory()->addItem($item) : $player->getWorld()->dropItem($player->getPosition(), $item);
	}


}