<?php namespace Taco\HCF\other\kits;

abstract class KitType {

	abstract function getItems() : array;

	abstract function getName() : string;

	abstract function getArmor() : array;

}