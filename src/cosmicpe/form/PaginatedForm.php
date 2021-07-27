<?php

declare(strict_types=1);

namespace cosmicpe\form;

use cosmicpe\form\entries\simple\Button;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

abstract class PaginatedForm extends SimpleForm{

	/** @var int */
	protected $current_page;

	public function __construct(string $title, ?string $content = null, int $current_page = 1){
		parent::__construct($title, $content);
		$this->current_page = $current_page;

		$this->populatePage();
		$pages = $this->getPages();
		if($this->current_page === 1){
			if($pages > 1){
				$this->addButton($this->getNextButton(), function(Player $player, int $data) : void{ $this->sendNextPage($player); });
			}
		}else{
			$this->addButton($this->getPreviousButton(), function(Player $player, int $data) : void{ $this->sendPreviousPage($player); });
			if($this->current_page < $pages){
				$this->addButton($this->getNextButton(), function(Player $player, int $data) : void{ $this->sendNextPage($player); });
			}
		}
	}

	protected function getPreviousButton() : Button{
		return new Button(TextFormat::BOLD . TextFormat::BLACK . "Previous Page" . TextFormat::RESET . TextFormat::EOL . TextFormat::DARK_GRAY . "Turn to the previous page");
	}

	protected function getNextButton() : Button{
		return new Button(TextFormat::BOLD . TextFormat::BLACK . "Next Page" . TextFormat::RESET . TextFormat::EOL . TextFormat::DARK_GRAY . "Turn to the next page");
	}

	abstract protected function getPages() : int;

	abstract protected function populatePage() : void;

	abstract protected function sendPreviousPage(Player $player) : void;

	abstract protected function sendNextPage(Player $player) : void;
}