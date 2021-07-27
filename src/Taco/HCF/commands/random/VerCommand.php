<?php namespace Taco\HCF\commands\random;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use Taco\HCF\Main;

class VerCommand extends Command {

	public function __construct(string $name) {
		parent::__construct($name);
		$this->setAliases(["version", "about"]);
		$this->setDescription("Check the server version.");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
		$sender->sendMessage("§o§aThis server is running on DarkSideHCF v.".Main::getInstance()->getServer()->getPluginManager()->getPlugin("MonkeyMoment")->getDescription()->getVersion()." by Taco!#0788\n§r§fPocketmine Version: ".Main::getInstance()->getServer()->getPocketMineVersion()."\nCurrent Protocol: ".ProtocolInfo::CURRENT_PROTOCOL."\n\nIf you would like to see a list of plugins you can always query the server.");
	}

}