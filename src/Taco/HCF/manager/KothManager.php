<?php namespace Taco\HCF\manager;

use pocketmine\math\Vector3;
use pocketmine\player\Player;
use Taco\HCF\Main;
use Taco\HCF\other\Time;
use function explode;
use function max;
use function min;
use function mt_rand;
use function var_dump;

class KothManager {

	/*
	 * Types
	 * 500, 500
	 * -500, 500
	 * 500, -500
	 * -500, -500
	 */

	public string $runningKothType = "";

	public int $kothTime = 0;

	public int $nextKoth = 0;

	public string $capper = "";

	public bool $kothRunning = false;

	public function addKothArea(Vector3 $pos1, Vector3 $pos2, string $name, string $type) : void {
		Main::getInstance()->koth[$name] = [
			"pos1" => Main::getUtils()->vec3ToString($pos1),
			"pos2" => Main::getUtils()->vec3ToString($pos2),
			"type" => $type
		];
		var_dump(Main::getInstance()->koth);
	}

	public function startRandomKothMatch(?string $namee = null) : void {
		if ($namee !== null) {
			$this->runningKothType = $namee;
			$this->kothTime = Time::TEN_MINS_IN_SECONDS;
			Main::getInstance()->getServer()->broadcastMessage("§e§b \n§eKoTH §d".$namee." §eis starting at §d{darkside.hcf.kothPos.{$namee}}§e!\n§e§b ");
			return;
		}
		$count = count(Main::getInstance()->koth);
		$rand = mt_rand(0, $count);
		$count = 0;
		foreach (Main::getInstance()->koth as $name => $info) {
			if ($count == $rand) {
				Main::getInstance()->getServer()->broadcastMessage("§e§b \n§eKoTH §d".$name." §eis starting at §d{$info["type"]}§e!\n§e§b ");
				$this->runningKothType = $name;
				$this->kothTime = Time::TEN_MINS_IN_SECONDS;
				break;
			} else {
				$count++;
			}
		}
	}

	public function getPlayerInKoth() : ?Player {
		$pos1 = explode(":", Main::getInstance()->koth[$this->runningKothType]["pos1"]);
		$pos2 = explode(":", Main::getInstance()->koth[$this->runningKothType]["pos2"]);
		$x1 = min((int)$pos1[0], (int)$pos2[0]);
		$y1 = min((int)$pos1[1], (int)$pos2[1]);
		$z1 = min((int)$pos1[2], (int)$pos2[2]);
		$x2 = max((int)$pos1[0], (int)$pos2[0]);
		$y2 = max((int)$pos1[1], (int)$pos2[1]);
		$z2 = max((int)$pos1[2], (int)$pos2[2]);
		$player = null;
		foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $playe) {
			$pos = $playe->getPosition();
			if ($pos->getFloorX() <= $x2 and $pos->getFloorX() >= $x1 and $pos->getFloorY() <= $y2 and $pos->getFloorY() >= $y1 and $pos->getFloorZ() <= $z2 and $pos->getFloorZ() >= $z1) {
				$player = $playe;
				break;
			}
		}
		return $player;
	}

	public function isPlayerInKoth(?Player $player) : bool {
		if ($player == null) return false;
		$pos1 = explode(":", Main::getInstance()->koth[$this->runningKothType]["pos1"]);
		$pos2 = explode(":", Main::getInstance()->koth[$this->runningKothType]["pos2"]);
		$x1 = min((int)$pos1[0], (int)$pos2[0]);
		$y1 = min((int)$pos1[1], (int)$pos2[1]);
		$z1 = min((int)$pos1[2], (int)$pos2[2]);
		$x2 = max((int)$pos1[0], (int)$pos2[0]);
		$y2 = max((int)$pos1[1], (int)$pos2[1]);
		$z2 = max((int)$pos1[2], (int)$pos2[2]);
		$pos = $player->getPosition();
		if ($pos->getFloorX() <= $x2 and $pos->getFloorX() >= $x1 and $pos->getFloorY() <= $y2 and $pos->getFloorY() >= $y1 and $pos->getFloorZ() <= $z2 and $pos->getFloorZ() >= $z1) return true;
		return false;
	}

}