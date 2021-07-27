<?php namespace Taco\HCF\partner\entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use Taco\HCF\Main;
use Taco\HCF\partner\PartnerRollOptions;
use function array_rand;
use function implode;

/**
 * Class PartnerRollEntity
 * @package Taco\HCF\partner\entity
 *
 * I spent like 3 hours trying to figure out this
 * and it wouldnt work no matter what I did.
 * If I hear anything about the "cleanliness" of this file
 * I will end you.
 */
class PartnerRollEntity extends Living {

    private int $time = 0;

    private int $ran = 0;

    private Player $player;

    public function getName() : string {
        return "PartnerPackage";
    }

    public function tryChangeMovement() : void {}

    public function attack(EntityDamageEvent $source) : void {$source->cancel();}

    public function __construct(Location $level, ?CompoundTag $nbt, ?Player $player = null) {
        if (!$player) return;
        parent::__construct($level, $nbt);
        $this->player = $player;
        $this->setNameTagAlwaysVisible(true);
        $this->setNameTagVisible(true);
        $this->setScale(0.0001);
    }

    private int $shouldClose = 0;

    private bool $eee= false;
    private bool $closing = false;

    public function entityBaseTick(int $tickDiff = 1) : bool {
    	if ($this->isFlaggedForDespawn()) return false;
    	if ($this->eee) return false;
    	if ($this->isClosed() || !$this->isAlive()) return false;
		if ($this->player == null) {
			$this->flagForDespawn();
			return true;
		}
        if ($this->closing) {
            $this->shouldClose++;
            $this->teleport($this->getPosition()->add(0, 0.1, 0));
			parent::entityBaseTick($tickDiff);
            if ($this->shouldClose > 60) {
                $this->flagForDespawn();
                $this->eee = true;
                $this->setNameTag("");
				parent::entityBaseTick($tickDiff);
				$this->close();
				return true;
            }
            return true;
        }
        $this->time++;
        if (($this->time % 5) == 0) {
            $pk = new PlaySoundPacket();
            $pk->soundName = 'random.orb';
            $pk->pitch = 1.0;
            $pk->volume = 500.0;
            $pk->x = $this->player->getPosition()->getX();
            $pk->y = $this->player->getPosition()->getY();
            $pk->z = $this->player->getPosition()->getZ();
            $this->player->getNetworkSession()->sendDataPacket($pk);
            $this->ran++;
            $list = [PartnerRollOptions::SNOWBALL, PartnerRollOptions::SLAPPER_STICK];
            $e = $list[array_rand($list)];
            if ($this->ran > 20) {
                $string = "";
                if (implode(" ", $e) == implode(" ", PartnerRollOptions::SNOWBALL)) {
                    $string = "§l§fSWITCHER BALL";
                    Main::getPartnerManager()->giveSwitcher($this->player);
                    $this->setNameTag(Main::getPartnerManager()->joinMappedSquare(PartnerRollOptions::SNOWBALL));
                } else if (implode(" ", $e) == implode(" ", PartnerRollOptions::SLAPPER_STICK)) {
                    $string = "§l§cSLAPPER STICK";
                    Main::getPartnerManager()->giveSlapper($this->player);
                    $this->setNameTag(Main::getPartnerManager()->joinMappedSquare(PartnerRollOptions::SLAPPER_STICK));
                }
                $this->player->sendMessage("§aYou have gotten a: ".$string."§r§a.");
                $this->setNameTag(Main::getPartnerManager()->joinMappedSquare($e)."\n§r".$string);
                $this->closing = true;
				parent::entityBaseTick($tickDiff);
                return true;
            } else {
				$this->setNameTag(Main::getPartnerManager()->joinMappedSquare($e));
				parent::entityBaseTick($tickDiff);
			}
        }
        return true;
    }

	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo(0.001, 0.001);
	}


	public static function getNetworkTypeId() : string {
		return EntityIds::CHICKEN;
	}

	protected function onDispose(): void
	{

	}

	public function saveNBT(): CompoundTag
	{//only this entity needs this. Idk why
		return CompoundTag::create()->setByte("bruhhhhIHatePM4Entities", 1);
	}

	public function onRandomUpdate(): void
	{

	}


}