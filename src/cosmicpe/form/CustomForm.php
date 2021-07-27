<?php

declare(strict_types=1);

namespace cosmicpe\form;

use Closure;
use cosmicpe\form\entries\custom\CustomFormEntry;
use cosmicpe\form\entries\ModifyableEntry;
use cosmicpe\form\types\Icon;
use Exception;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

abstract class CustomForm implements Form{

	/** @var string */
	private $title;

	/** @var Icon|null */
	private $icon;

	/** @var CustomFormEntry[] */
	private $entries = [];

	/** @var Closure[] */
	private $entry_listeners = [];

	public function __construct(string $title){
		$this->title = $title;
	}

	final public function setIcon(?Icon $icon) : void{
		$this->icon = $icon;
	}

	/**
	 * @param CustomFormEntry $entry
	 * @param Closure|null $listener
	 *
	 * Listener parameters:
	 *  * Player $player
	 *  * InputEntry $entry
	 *  * mixed $value [NOT NULL]
	 */
	final public function addEntry(CustomFormEntry $entry, Closure $listener = null) : void{
		$this->entries[] = $entry;
		if($listener !== null){
			$this->entry_listeners[array_key_last($this->entries)] = $listener;
		}
	}

	public function handleResponse(Player $player, $data) : void{
		if($data === null){
			$this->onClose($player);
		}else{
			try{
				foreach($data as $key => $value){
					if(isset($this->entry_listeners[$key])){
						$entry = $this->entries[$key];
						if($entry instanceof ModifyableEntry){
							$entry->validateUserInput($value);
						}
						$this->entry_listeners[$key]($player, $this->entries[$key], $value);
					}
				}
			}catch(Exception $e){
				throw new FormValidationException($e->getMessage());
			}
		}
	}

	public function onClose(Player $player) : void{
	}

	final public function jsonSerialize() : array{
		return [
			"type" => "custom_form",
			"title" => $this->title,
			"icon" => $this->icon,
			"content" => $this->entries
		];
	}
}