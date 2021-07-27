<?php namespace Taco\HCF\other\kits\forms;

use cosmicpe\form\ModalForm;
use pocketmine\player\Player;
use Taco\HCF\Main;

class KitConfirmForm extends ModalForm {

	private string $kitName = "";

	private int $kitIndex = 0;

	public function __construct(string $title, string $kitName, int $kitIndex) {
		$this->kitName = $kitName;
		$this->kitIndex = $kitIndex;
		parent::__construct($title, "Are you sure you would like to equip the kit $kitName?");
		$this->setFirstButton("Yes");
		$this->setSecondButton("No");
	}

	protected function onAccept(Player $player) : void {
		Main::getKitsManager()->giveKit($player, $this->kitIndex);
	}

	protected function onClose(Player $player) : void {
		$player->sendMessage("§eClosed form.");
	}

}