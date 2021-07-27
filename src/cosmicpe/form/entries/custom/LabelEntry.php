<?php

declare(strict_types=1);

namespace cosmicpe\form\entries\custom;

final class LabelEntry implements CustomFormEntry{

	/** @var string */
	private $title;

	public function __construct(string $title){
		$this->title = $title;
	}

	public function jsonSerialize() : array{
		return [
			"type" => "label",
			"text" => $this->title
		];
	}
}