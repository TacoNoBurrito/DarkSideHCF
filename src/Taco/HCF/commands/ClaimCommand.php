<?php namespace Taco\HCF\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use Taco\HCF\Main;
use function array_shift;

class ClaimCommand extends Command {

    private Vector3 $pos11;

    private Vector3 $pos22;

    public function __construct(string $name) {
        parent::__construct($name);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if (Main::getInstance()->getServer()->isOp($sender->getName()) and $sender instanceof Player) {
            $arg = array_shift($args);
            if ($arg == "" or $arg == "help") {
                $sender->sendMessage("§eClaim help >> /claim <pos1|pos2|create [name]>");
                return;
            }
            switch ($arg) {
                case "pos1":
                    $this->pos11 = new Vector3($sender->getPosition()->getFloorX(), $sender->getPosition()->getFloorY(), $sender->getPosition()->getFloorZ());
                    $sender->sendMessage("§eSet claim position 1.");
                    break;
                case "pos2":
                    $this->pos22 = new Vector3($sender->getPosition()->getFloorX(), $sender->getPosition()->getFloorY(), $sender->getPosition()->getFloorZ());
                    $sender->sendMessage("§eSet claim position 2.");
                    break;
                case "create":
                    $name = join(" ", $args);
                    if ($name == "") {
                        $sender->sendMessage("§eYou must provide a name for the the claim.");
                        return;
                    }
                    Main::getClaimManager()->addClaim($name, $this->pos11, $this->pos22);
                    $sender->sendMessage("§eAdded claim successfully.");
                    break;
                default:
                    $sender->sendMessage("§eClaim help >> /claim <pos1|pos2|create [name]>");
            }
        }
    }

}