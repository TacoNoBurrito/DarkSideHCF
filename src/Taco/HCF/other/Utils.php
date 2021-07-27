<?php namespace Taco\HCF\other;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\World;
use Taco\HCF\Main;
use Taco\HCF\other\entities\LogoutVillager;
use Taco\HCF\partner\entity\PartnerRollEntity;
use UnexpectedValueException;
use function array_rand;
use function count;
use function floor;
use function in_array;

class Utils {

    public function pre_save_array_clean(array $array) : array {
        $exclude = Main::EXCLUDE_DATA_SAVE;
        foreach ($array as $key => $useless) {
            if (in_array($key, $exclude)) unset($array[$key]);
        }
        return $array;
    }

    public function getRankFormatted(string $rank) : string {
        return [
            "None" => "§r§7§lNoob§r",
			"Mars" => "§r§l§6Mars§r",
			"Neptune" => "§r§l§aNeptune§r",
			"Saturn" => "§r§l§cSaturn§r",
			"Dark" => "§r§l§8Dark§r",
			"Helper" => "§r§l§eHelper§r",
			"Mod" => "§r§l§dMod§r",
			"Admin" => "§r§l§cAdmin§r",
			"Owner" => "§r§l§bO§dw§bn§de§br§r"
        ][$rank];
    }

    public function secondsToHourCD(int $int) : string {
        $m = floor(($int % 86400) / 3600);
        $s = floor($int % 60);
        $h = floor(($int % 86400) / 3600);
        return (($h < 10 ? "0" : "").$h.":".($m < 10 ? "0" : "").$m.":".($s < 10 ? "0" : "").$s);
    }

    public function secondsToEnderpearlCD(int $int) : string {
        $m = floor($int / 60);
        $s = floor($int % 60);
        return (($m < 10 ? "0" : "").$m.":".($s < 10 ? "0" : "").$s);
    }

    public function vec3ToString(Vector3 $vec) : string {
        return $vec->getFloorX().":".$vec->getFloorY().":".$vec->getFloorZ();
    }

    public function stringToVec3(string $vec) : Vector3 {
        $ex = explode(":", $vec);
        return new Vector3((int)$ex[0], (int)$ex[1], (int)$ex[2]);
    }

    public function arrayToDescendingString(array $arr) : string {
        return implode("\n", $arr);
    }

    public function intToTimeString(int $seconds) : string {
        if($seconds < 0) throw new UnexpectedValueException("time can't be a negative value");
        if($seconds === 0) {
            return "0 seconds";
        }
        $timeString = "";
        $timeArray = [];
        if($seconds >= 86400) {
            $unit = floor($seconds / 86400);
            $seconds -= $unit * 86400;
            $timeArray[] = $unit . " days";
        }
        if($seconds >= 3600) {
            $unit = floor($seconds / 3600);
            $seconds -= $unit * 3600;
            $timeArray[] = $unit . " hours";
        }
        if($seconds >= 60) {
            $unit = floor($seconds / 60);
            $seconds -= $unit * 60;
            $timeArray[] = $unit . " minutes";
        }
        if($seconds >= 1) {
            $timeArray[] = $seconds . " seconds";
        }
        foreach($timeArray as $key => $value) {
            if($key === 0) {
                $timeString .= $value;
            } elseif($key === count($timeArray) - 1) {
                $timeString .= " and " . $value;
            } else {
                $timeString .= ", " . $value;
            }
        }
        return $timeString;
    }

    public function spawnLogoutVillager(Vector3 $pos, string $name, array $items) : void {
		$entity = new LogoutVillager(Location::fromObject($pos, Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld()), null, $name, $items);
		$entity->spawnToAll();
    }

    public function sortItemInHandName(Player $player) : string {
        return $player->getInventory()->getItemInHand()->getCustomName() == "" ? $player->getInventory()->getItemInHand()->getName() : $player->getInventory()->getItemInHand()->getCustomName();
    }

    public function processDeath(Player $killer, Player $killed) : void {
        $item = $this->sortItemInHandName($killer);
        Main::getInstance()->players[$killer->getName()]["kills"] += 1;
        Main::getInstance()->players[$killer->getName()]["killstreak"] += 1;
        Main::getInstance()->players[$killed->getName()]["deaths"] += 1;
        Main::getInstance()->players[$killed->getName()]["killstreak"] = 0;
        if (Main::getInstance()->players[$killer->getName()]["faction"] !== "None") {
            Main::getInstance()->factions[Main::getInstance()->players[$killer->getName()]["faction"]]["power"] += 2;
        }
        if (Main::getInstance()->players[$killed->getName()]["faction"] !== "None") {
            Main::getInstance()->factions[Main::getInstance()->players[$killed->getName()]["faction"]]["power"] -= 2;
            $members = Main::getFactionManager()->getMembersInFaction(Main::getInstance()->players[$killed->getName()]["faction"]);
            foreach ($members as $name) {
                $player = Main::getInstance()->getServer()->getPlayerByPrefix($name);
                if ($player !== null) {
                    $player->sendMessage("§7[§2FACTION§7] §e".$killed->getName()." §7has died.");
                }
            }
        }
        Main::getInstance()->getServer()->broadcastMessage("§c".$killed->getName()."§4[".Main::getInstance()->players[$killed->getName()]["kills"]."] §r§ewas slain by §r§c".$killer->getName()."§4[".Main::getInstance()->players[$killer->getName()]["kills"]."] §r§eusing §r".$item);
    }

    public function registerDeathban(Player $player) : void {
        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(
            function () use ($player) : void {
                $player->kick(Main::DEATHBAN_LOGOUT_MESSAGE, false);
            }
        ), 40);
        Main::getInstance()->players[$player->getName()]["deathban-time"] = Time::ONE_HOUR;
    }

    /**
     * @param Vector3 $pos
     * @return Vector3|null
     */
    public function getSafestUpSignBlock(Vector3 $pos) : ?Vector3 {
        $level = Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld();
        $p = null;
        for ($i = $pos->getFloorY(); $i <= 256-$pos->getFloorY(); $i++) {
            if ($level->getBlock(new Vector3($pos->getX(), $i, $pos->getZ()))->getId() == 0 and $level->getBlock(new Vector3($pos->getX(), $i+1, $pos->getZ()))->getId() == 0) {
                $p = new Vector3($pos->getX(), $i, $pos->getZ());
                break;
            }
        }
        return $p;
    }

    public function XZDist(Vector3 $pos1, Vector3 $pos2) : int {
    	return sqrt((($pos1->x - $pos2->x) ** 2)  + (($pos1->z - $pos2->z) ** 2));
	}

	public function makePillar(Player $player, Vector3 $pos, Block $block) : void {
    	Main::getInstance()->players[$player->getName()]["has-pillars-at"][] = $this->vec3ToString($pos);
		Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use (
			$player,
			$block,
			$pos
		) : void {
			$y = ($lvl = $player->getPosition()->getWorld())->getHighestBlockAt($pos->x, $pos->z) + 1;
			for($i = 0; $y <= World::Y_MAX; $y++, $i++) {
				if(($y % 6) == 0) {
					$b = clone $block;
				} else {
					$b = BlockFactory::getInstance()->get(BlockLegacyIds::GLASS, 0);
				}
				$pk = UpdateBlockPacket::create($pos->getX(), $y, $pos->getZ(), RuntimeBlockMapping::getInstance()->toRuntimeId(BlockFactory::getInstance()->get($b->getId(), 0)->getFullId()));
				$player->getNetworkSession()->sendDataPacket($pk);
			}
		}), 2);
	}

	public function clearAllPillars(Player $player) : void {
		foreach (Main::getInstance()->players[$player->getName()]["has-pillars-at"] as $vec) {
			$v = $this->stringToVec3($vec);
			$this->clearPillar($player, $v);
		}
	}

	public function clearPillar(Player $player, Vector3 $pos) : void {
		unset(Main::getInstance()->players[$player->getName()]["has-pillars-at"][$this->vec3ToString($pos)]);
		$y = ($lvl = $player->getPosition()->getWorld())->getHighestBlockAt($pos->x, $pos->z);
		if(empty($lvl->getBlockAt($pos->x, $y, $pos->z)->getCollisionBoxes())) {
			$y--;
		}
		$v3s = [];
		for(; $y <= World::Y_MAX; $y++) {
			$v3s[] = new Vector3($pos->x, $y, $pos->z);
		}
		foreach ($v3s as $v) {
			$pk = UpdateBlockPacket::create($v->getX(), $v->getY(), $v->getZ(), RuntimeBlockMapping::getInstance()->toRuntimeId(BlockFactory::getInstance()->get(0, 0)->getFullId()));
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}

	public function getRandomColor() : string {
    	$arr = ["§0", "§1", "§2", "§3", "§4", "§5", "§6", "§7", "§8", "§9", "§a", "§c", "§d", "§e"];
    	return $arr[array_rand($arr)];
	}

	public function getDirectionFacing(Player $player) : string {
		$yaw = $player->getLocation()->getYaw();
		$direction = ($yaw - 180) % 360;
		if ($direction < 0) $direction += 360;
		if (0 <= $direction && $direction < 22.5) return $this->getRandomColor()."N";
		elseif (22.5 <= $direction && $direction < 67.5) return $this->getRandomColor()."NE";
		elseif (67.5 <= $direction && $direction < 112.5) return $this->getRandomColor()."E";
		elseif (112.5 <= $direction && $direction < 157.5) return $this->getRandomColor()."SE";
		elseif (157.5 <= $direction && $direction < 202.5) return $this->getRandomColor()."S";
		elseif (202.5 <= $direction && $direction < 247.5) return $this->getRandomColor()."SW";
		elseif (247.5 <= $direction && $direction < 292.5) return $this->getRandomColor()."W";
		elseif (292.5 <= $direction && $direction < 337.5) return $this->getRandomColor()."NW";
		elseif (337.5 <= $direction && $direction < 360.0) return $this->getRandomColor()."N";
		else return $this->getRandomColor()."?";
	}




}