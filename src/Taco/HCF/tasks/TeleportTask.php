<?php namespace Taco\HCF\tasks;

use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use Taco\HCF\Main;

/**
 * Class TeleportTask
 * @package Taco\HCF\tasks
 *
 * Also used for /logout
 */
class TeleportTask extends Task {

    private int $time = 0;

    private string $location = "";

    private Player $player;

    public function __construct(int $time, string $location, Player $player) {
        $this->time = $time;
        $this->location = $location;
        $this->player = $player;
    }

    public function onRun() : void {
        if ($this->player == null) {
            $this->getHandler()->cancel();
            return;
        }
        if (Main::getInstance()->players[$this->player->getName()]["teleporting"] == false) {
            $this->getHandler()->cancel();
            return;
        }
        $this->time--;
        if ($this->location == "home") Main::getInstance()->players[$this->player->getName()]["teleport-time-remaining"] = $this->time;
		if ($this->location == "stuck") Main::getInstance()->players[$this->player->getName()]["stuck"] = $this->time;
        if ($this->time < 1) {
            $this->getHandler()->cancel();
            switch($this->location) {
                case "home":
                    $this->player->teleport(Main::getUtils()->stringToVec3(Main::getInstance()->factions[Main::getInstance()->players[$this->player->getName()]["faction"]]["home"]));
                    $this->player->sendMessage("§r§l§7(§c!§7) §r§fTeleported you to your faction home.");
                    break;
				case "stuck":
					$claim = Main::getClaimManager()->getClaimAtPosition($this->player->getPosition());
					$x1 = Main::getInstance()->claims[$claim]["x1"];
					$z1 = Main::getInstance()->claims[$claim]["z1"];
					$this->player->teleport(new Vector3($x1, $this->player->getWorld()->getHighestBlockAt($x1, $z1) + 4, $z1));
					$this->player->sendMessage("§r§l§7(§c!§7) §r§fYou have been un-stucked.");
					break;
				case "logout":
					$this->player->kick(Main::SAFE_LOGOUT_MESSAGE);
					break;
            }
        }
    }

}