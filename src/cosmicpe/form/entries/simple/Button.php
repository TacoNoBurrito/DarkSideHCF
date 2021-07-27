<?php

declare(strict_types=1);

namespace cosmicpe\form\entries\simple;

use cosmicpe\form\entries\FormEntry;
use cosmicpe\form\types\Icon;

final class Button implements FormEntry{

	/** @var string */
	private $title;

	/** @var Icon|null */
	private $icon;

	public function __construct(string $title, ?Icon $icon = null){
		$this->title = $title;
		$this->icon = $icon;
	}

	public function jsonSerialize() : array{
		return [
			"text" => $this->title,
			"image" => $this->icon
		];
	}
}