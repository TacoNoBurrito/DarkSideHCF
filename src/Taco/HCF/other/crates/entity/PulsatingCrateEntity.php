<?php namespace Taco\HCF\other\crates\entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use Taco\HCF\Main;
use function strlen;

/**
 * Class PulsatingCrateEntity
 * @package Taco\HCF\other\crates\entity
 *
 * Tried to make a random tick from \Transparent but forgot its not in \Living
 */
class PulsatingCrateEntity extends Living  {

	private string $crateSpecialName = "";

	private string $savedCrateSpecialName = "";

	private int $rollingPosition = 0;

	public bool $isRandomUpdate = false;

	private int $tick = 0;

	private string $crateColor = "";

	private int $randomUpdateTick = 0;

	private string $crateType = "";

	public function __construct(Location $location, ?CompoundTag $nbt = null, string $crateType = "") {
		if ($crateType == "") return;
		parent::__construct($location, $nbt);
		$this->setNameTagAlwaysVisible(true);
		$this->setNameTagVisible(true);
		$this->crateSpecialName = $crateType;
		$this->setCanSaveWithChunk(true);
		$this->savedCrateSpecialName = $crateType;
		$this->crateColor = Main::getCrateUtils()::CRATE_NAME_COLORS[$crateType];
		$this->setScale(0.0001);
		$this->setNameTag(Main::getCrateUtils()::CRATE_NAME_COLORS[$crateType].$this->savedCrateSpecialName);
		$this->crateType = $crateType;
	}

	protected function getInitialSizeInfo() : EntitySizeInfo {
		return new EntitySizeInfo(0.0001, 0.0001);
	}

	public static function getNetworkTypeId() : string {
		return EntityIds::CHICKEN;
	}

	public function getName() : string {
		return "PulsatingCrateEntity";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		if ($this->isFlaggedForDespawn()) return false;
		$this->tick++;
		$this->randomUpdateTick++;
		if ($this->randomUpdateTick > (20 * 3)) {
			$this->isRandomUpdate = true;
			$this->randomUpdateTick = 0;
		}
		if ($this->tick > 20) $this->tick = 0;
		if (($this->tick % 4) == 0) {
			if ($this->isRandomUpdate) {
				if ($this->rollingPosition + 1 > strlen($this->crateSpecialName)) {
					$this->isRandomUpdate = false;
					$this->rollingPosition = 0;
					$this->crateSpecialName = Main::getCrateUtils()::CRATE_NAME_COLORS[$this->crateType].$this->savedCrateSpecialName;
					return true;
				}
				$newString = "";
				for ($i = 0; $i <= (strlen($this->savedCrateSpecialName)-1); $i++) {
					if ($i == $this->rollingPosition) {
						$newString .= "§l§f".$this->savedCrateSpecialName[$i];
					} else {
						$newString .= $this->crateColor.$this->savedCrateSpecialName[$i];
					}
				}
				$this->crateSpecialName = $newString;
				$this->rollingPosition++;
			}
			$this->setNameTag("§l§7Use your ".$this->crateSpecialName."§r§7 key on this crate\n§r§7To get awesome rewards!\n\n§r§7§oYou can buy more keys at\n§edarksidepe.tebex.io");
		}
		return parent::entityBaseTick($tickDiff);
	}

	public function onRandomUpdate() : void {

	}

	protected function onDispose(): void
	{

	}

	public function saveNBT(): CompoundTag
	{//only this entity needs this x2. Idk why
		return CompoundTag::create()->setByte("bruhhhhIHatePM4Entities", 1);
	}

	public function tryChangeMovement() : void {}

	public function attack(EntityDamageEvent $source) : void {$source->cancel();}


}