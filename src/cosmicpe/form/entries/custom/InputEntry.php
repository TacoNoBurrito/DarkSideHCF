<?php

declare(strict_types=1);

namespace cosmicpe\form\entries\custom;

use cosmicpe\form\entries\ModifyableEntry;
use InvalidArgumentException;

final class InputEntry implements CustomFormEntry, ModifyableEntry{

	/** @var string */
	private $title;

	/** @var string|null */
	private $placeholder;

	/** @var string|null */
	private $default;

	public function __construct(string $title, ?string $placeholder = null, ?string $default = null){
		$this->title = $title;
		$this->placeholder = $placeholder;
		$this->default = $default;
	}

	public function getPlaceholder() : ?string{
		return $this->placeholder;
	}

	public function getDefault() : ?string{
		return $this->default;
	}

	public function getValue() : string{
		return $this->default;
	}

	public function setValue($value) : void{
		$this->default = $value;
	}

	public function validateUserInput($input) : void{
		if(!is_string($input)){
			throw new InvalidArgumentException("Failed to process invalid user input: " . $input);
		}
	}

	public function jsonSerialize() : array{
		return [
			"type" => "input",
			"text" => $this->title,
			"placeholder" => $this->placeholder ?? "",
			"default" => $this->default ?? ""
		];
	}
}