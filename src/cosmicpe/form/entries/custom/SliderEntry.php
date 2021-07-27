<?php

declare(strict_types=1);

namespace cosmicpe\form\entries\custom;

use cosmicpe\form\entries\ModifyableEntry;
use InvalidArgumentException;

final class SliderEntry implements CustomFormEntry, ModifyableEntry{

	/** @var string */
	private $title;

	/** @var float */
	private $minimum;

	/** @var float */
	private $maximum;

	/** @var float */
	private $step;

	/** @var float */
	private $default;

	public function __construct(string $title, float $minimum, float $maximum, float $step = 0.0, float $default = 0.0){
		$this->title = $title;
		$this->minimum = $minimum;
		$this->maximum = $maximum;
		$this->step = $step;
		$this->default = $default;
	}

	public function getValue() : float{
		return $this->default;
	}

	public function setValue($value) : void{
		$this->default = $value;
	}

	public function validateUserInput($input) : void{
		if(!is_float($input) || $input > $this->maximum || $input < $this->minimum){
			throw new InvalidArgumentException("Failed to process invalid user input: " . $input);
		}
	}

	public function jsonSerialize() : array{
		return [
			"type" => "slider",
			"text" => $this->title,
			"min" => $this->minimum,
			"max" => $this->maximum,
			"step" => $this->step,
			"default" => $this->default
		];
	}
}