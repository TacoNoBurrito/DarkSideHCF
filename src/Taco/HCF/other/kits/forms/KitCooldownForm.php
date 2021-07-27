<?php namespace Taco\HCF\other\kits\forms;

use cosmicpe\form\ModalForm;
use pocketmine\player\Player;

class KitCooldownForm extends ModalForm {

	public function __construct(string $timeLeft) {
		parent::__construct(
			"This kit is on a cooldown!",
			"This kit is still on cooldown for ".$timeLeft."."
		);
		$this->setFirstButton("Go Back");
		$this->setSecondButton("Close");
	}
	protected function onAccept(Player $player) : void {
		$player->sendForm(new KitSelectionForm());
	}

	protected function onClose(Player $player) : void {}


}