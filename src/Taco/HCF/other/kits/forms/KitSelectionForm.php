<?php namespace Taco\HCF\other\kits\forms;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use cosmicpe\form\types\Icon;
use pocketmine\player\Player;
use Taco\HCF\other\kits\KitsManager;

class KitSelectionForm extends SimpleForm {

	public function __construct() {
		parent::__construct("DarkSide Kits", "§o§6Select a kit to use in combat.");
		$this->addButton(
			new Button("Starter", new Icon(Icon::URL, "https://i.ibb.co/ThFY9K4/Starter.png")),
			function (Player $player, int $index) : void {
				$player->sendForm(new KitConfirmForm("Starter Kit", "Starter", KitsManager::TYPE_STARTER));
			}
		);
		$this->addButton(
			new Button("Builder", new Icon(Icon::URL, "https://i.ibb.co/kGkBgkh/Untitled-design-1.png")),
			function (Player $player, int $index) : void {
				$player->sendForm(new KitConfirmForm("Builder Kit", "Builder", KitsManager::TYPE_BUILDER));
			}
		);
		$this->addButton(
			new Button("Bard", new Icon(Icon::URL, "https://i.ibb.co/sqC9B57/Starter-2.png")),
			function (Player $player, int $index) : void {
				$player->sendForm(new KitConfirmForm("Bard Kit", "Bard", KitsManager::TYPE_BARD));
			}
		);
		$this->addButton(
			new Button("Rogue", new Icon(Icon::URL, "https://i.ibb.co/CvC3M0M/Untitled-design.png")),
			function (Player $player, int $index) : void {
				$player->sendForm(new KitConfirmForm("Rogue Kit", "Rogue", KitsManager::TYPE_ROGUE));
			}
		);
		$this->addButton(
			new Button("Diamond", new Icon(Icon::URL, "https://i.ibb.co/89d1G2h/Diamond.png")),
			function (Player $player, int $index) : void {
				$player->sendForm(new KitConfirmForm("Diamond Kit", "Diamond", KitsManager::TYPE_DIAMOND));
			}
		);
	}

}