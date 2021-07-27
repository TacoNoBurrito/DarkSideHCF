<?php

declare(strict_types=1);

namespace cosmicpe\form\entries;

use InvalidArgumentException;

interface ModifyableEntry{

	public function getValue();

	public function setValue($value) : void;

	/**
	 * @param mixed $input
	 * @throws InvalidArgumentException
	 */
	public function validateUserInput($input) : void;
}