<?php namespace Taco\HCF\tasks;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use Taco\HCF\Main;
use function str_repeat;

class ScoreboardTask extends Task {

    private array $line = [];

    public function onRun() : void {
        foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $player) {
            if (!isset(Main::getInstance()->players[$player->getName()])) continue;
            $info = Main::getInstance()->players[$player->getName()];
            $timers = $info["timers"];
            $count = 0;
            foreach ($timers as $timer => $time) {
                if ($time > 0) $count++;
            }
            if ($info["pvp-timer"] > 0) $count++;
            if ($info["teleport-time-remaining"] > 0) $count++;
            if (Main::getInstance()->sotw_timer > 0) $count++;
            if ($info["class"] == "bard") $count++;
            if (Main::getKothManager()->kothRunning) $count++;
            $this->removeScoreboard($player);
            if ($count < 1) continue;
            $this->showScoreboard($player);
            $this->clearLines($player);
            $this->addLine("§7".str_repeat("―", 25)."§7§a", $player);
            ///////////////////////////////////////////////////////////////
            if ($timers["spawntag"] > 0) $this->addLine(" §l§cSpawnTag§r§7: §c".Main::getUtils()->secondsToEnderpearlCD($timers["spawntag"]), $player);
            if (Main::getInstance()->sotw_timer > 0) $this->addLine(" §l§aSOTW §r§aends in ".Main::getUtils()->secondsToHourCD(Main::getInstance()->sotw_timer), $player);
            if ($info["class"] == "bard") $this->addLine(" §l§6Energy§r§7: §c".$info["bard-energy"], $player);
            if ($timers["pearl-cooldown"] > 0) $this->addLine(" §l§eEnderpearl§r§7: §r§c".Main::getUtils()->secondsToEnderpearlCD($timers["pearl-cooldown"]), $player);
            if ($info["pvp-timer"] > 0) $this->addLine(" §l§2PVP Timer§r§7: §r§c".Main::getUtils()->secondsToEnderpearlCD($info["pvp-timer"]), $player);
            if ($info["teleport-time-remaining"] > 0) $this->addLine(" §6§lHome§r§7: §r§c".Main::getUtils()->secondsToEnderpearlCD(Main::getInstance()->players[$player->getName()]["teleport-time-remaining"]), $player);
			if ($info["teleport-time-remaining"] > 0) $this->addLine(" §c§lStuck§r§7: §r§c".Main::getUtils()->secondsToEnderpearlCD(Main::getInstance()->players[$player->getName()]["stuck"]), $player);
            if ($timers["switcher"] > 0) $this->addLine(" §l§6Switcher§r§7: §r§c".Main::getUtils()->secondsToEnderpearlCD($timers["switcher"]), $player);
            if ($timers["rogue"] > 0)  $this->addLine(" §l§dBackstab§r§7: §r§c". Main::getUtils()->secondsToEnderpearlCD($timers["rogue"]), $player);
            if (Main::getKothManager()->kothRunning) $this->addLine(" §l§9".Main::getKothManager()->runningKothType."§r§7:§c ".Main::getUtils()->secondsToEnderpearlCD(Main::getKothManager()->kothTime), $player);
            ///////////////////////////////////////////////////////////////
            $this->addLine("§7".str_repeat("―", 25)."§7§a§r§7§8", $player);
            $this->addLine("§r§o§7darkside.xyz", $player);
        }
    }

    public function showScoreboard(Player $player) : void {
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $player->getName();
        $pk->displayName = "§l§7DarkSide §r§f[Map 1]";
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function addLine(string $line, Player $player) : void {
        $score = count($this->line) + 1;
        $this->setLine($score,$line,$player);
    }

    public function removeScoreboard(Player $player) : void {
        $objectiveName = $player->getName();
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $objectiveName;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function clearLines(Player $player) {
        for ($line = 0; $line <= 15; $line++) {
            $this->removeLine($line, $player);
        }
    }

    public function setLine(int $loc, string $msg, Player $player) : void {
        $pk = new ScorePacketEntry();
        $pk->objectiveName = $player->getName();
        $pk->type = $pk::TYPE_FAKE_PLAYER;
        $pk->customName = $msg;
        $pk->score = $loc;
        $pk->scoreboardId = $loc;
        if (isset($this->line[$loc])) {
            unset($this->line[$loc]);
            $pkt = new SetScorePacket();
            $pkt->type = $pkt::TYPE_REMOVE;
            $pkt->entries[] = $pk;
            $player->getNetworkSession()->sendDataPacket($pkt);
        }
        $pkt = new SetScorePacket();
        $pkt->type = $pkt::TYPE_CHANGE;
        $pkt->entries[] = $pk;
        $player->getNetworkSession()->sendDataPacket($pkt);
        $this->line[$loc] = $msg;
    }

    public function removeLine(int $line, Player $player) : void {
        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_REMOVE;
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $player->getName();
        $entry->score = $line;
        $entry->scoreboardId = $line;
        $pk->entries[] = $entry;
        $player->getNetworkSession()->sendDataPacket($pk);
        if (isset($this->line[$line])) {
            unset($this->line[$line]);
        }
    }

}