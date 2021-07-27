<?php

declare(strict_types=1);

namespace cosmicpe\form\types;

use JsonSerializable;

final class Icon implements JsonSerializable{

	public const URL = "url";
	public const PATH = "path";

	/** @var string */
	private $type;

	/** @var string */
	private $data;

	public function __construct(string $type, string $data){
		$this->type = $type;
		$this->data = $data;
	}

	public function jsonSerialize() : array{
		return [
			"type" => $this->type,
			"data" => $this->data
		];
	}
}