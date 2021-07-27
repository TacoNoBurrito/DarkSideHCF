<?php namespace Taco\HCF\manager;

use pocketmine\math\Vector3;
use Taco\HCF\Main;
use function max;
use function min;
use function var_dump;

class ClaimManager {

    public function getClaimPrice(Vector3 $pos1, Vector3 $pos2) : int {
        return $pos1->distance($pos2) * 60;
    }

    public function getClaimAtPosition(Vector3 $pos) : string {
        $ret = "Wilderness";
        foreach (Main::getInstance()->claims as $name => $info) {
            $minX = min($info["x1"], $info["x2"]);
            $maxX = max($info["x1"], $info["x2"]);
            $minZ = min($info["z1"], $info["z2"]);
            $maxZ = max($info["z1"], $info["z2"]);
            if ($pos->getFloorX() >= $minX and $pos->getFloorX() <= $maxX and $pos->getFloorZ() >= $minZ and $pos->getFloorZ() <= $maxZ) {

                return $name;
                break;
            }
        }
        return "Wilderness";
    }

    public function addClaim(string $name, Vector3 $pos1, Vector3 $pos2) : void {
        Main::getInstance()->claims[$name] = [
            "x1" => $pos1->getFloorX(),
            "x2" => $pos2->getFloorX(),
            "z1" => $pos1->getFloorZ(),
            "z2" => $pos2->getFloorZ()
        ];
    }

}
