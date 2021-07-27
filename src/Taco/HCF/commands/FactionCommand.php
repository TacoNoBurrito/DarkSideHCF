<?php namespace Taco\HCF\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Taco\HCF\Main;
use Taco\HCF\other\Time;
use Taco\HCF\tasks\TeleportTask;
use function array_shift;
use function count;
use function ctype_alnum;
use function explode;
use function hex2bin;
use function implode;
use function in_array;
use function is_numeric;
use function str_repeat;
use function strlen;

/**
 * Class FactionCommand
 * @package Taco\HCF\commands
 *
 * Finally done with this mess LMAO
 */
class FactionCommand extends Command {

    public function __construct(string $name) {
        parent::__construct($name);
        $this->setDescription("Core factions command.");
        $this->setAliases(["f", "team", "fac"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if (!$sender instanceof Player) return;
        $arg = array_shift($args);
        $help = [
            "§l§cFaction Help",
            "§r§7------------------------------------------------------------------------------------------",
            "§cGeneral Commands:",
            "§7/f create <factionName> - §r§fCreate a new faction.",//done
            "§7/f accept <factionName> - §r§fAccept a pending invite to a faction.",//done
            "§7/f leave - §r§fLeave your current faction.",//done
            "§7/f home - §r§fTeleport to your faction home.",//done
            "§7/f stuck - §r§fTeleport out of enemy territory",//done
            "§7/f deposit <amount|all> - §r§fDeposit money to your factions balance.",//done
            "§7/f tl - §r§fTells your entire faction your current x-y-z.",//done
			"§7/f top - §r§fSee the top factions of the current map.",
            "§r§7    ",
            "§r§cInformation Commands.",
            "§r§7/f info <faction> - §fGet info on a faction.",//done
            "§7/f who <playerName> - §fGet info on a players faction.",//done
            "§l§d     ",
            "§r§cCaptain Commands.",
            "§r§7/f invite <playerName> - §fInvite a player to the faction.",//done
            "§7/f kick <playerName> - §fKick a player from the faction.",//done
            "§7/f withdraw <amount> - §fWithdraw a amount from your factions balance.",//done
            "§l§d",
            "§r§cLeader Commands.",
            "§7/f promote <playerName> - §fPromote a player in your faction.",//done
            "§7/f demote <playerName> - §fDemote a player in your faction.",//done
            "§7/f unclaim - §fUnclaim your factions territory.",//done
            "§7/f disband - §fDisband your faction.",//done
            "§7/f claim - §fClaim land for your faction.",//done
			"§7/f disband - §fDisband your faction.",//done
            "§r§7------------------------------------------------------------------------------------------"
        ];
        if ($arg == "" or $arg == "help") {
            $sender->sendMessage(Main::getUtils()->arrayToDescendingString($help));
            return;
        }
        $data = Main::getInstance()->players[$sender->getName()];
        switch ($arg) {
            case "create":
                if ($data["faction"] == "None") {
                    $name = array_shift($args);
                    if ($name == "") {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7You must provide a faction name!");
                        return;
                    }
                    if (strlen($name) > 12 or strlen($name) < 2) {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7Faction names must be less than 12 characters and more than 1.");
                        return;
                    }
                    if (!ctype_alnum($name)) {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7Faction names can only have letters and digits.");
                        return;
                    }
                    if (isset(Main::getInstance()->factions[$name])) {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7A faction with this name already exists!");
                        return;
                    }
                    if (in_array($name, Main::NON_DEATHBAN_CLAIMS)) {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7You may not use this faction name.");
                        return;
                    }
                    if (in_array($name, Main::BANNED_FACTION_NAMES)) {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7You may not use this faction name.");
                        return;
                    }
                    Main::getFactionManager()->createFaction($sender, $name);
                } else {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You are already in a faction.");
                }
                break;
            case "tl":
                if ($data["faction"] == "None") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to do this!");
                    return;
                }
                $members = Main::getFactionManager()->getMembersInFaction($data["faction"]);
                foreach ($members as $name) {
                    $player = Main::getInstance()->getServer()->getPlayerByPrefix($name);
                    if ($player !== null) {
                        $player->sendMessage("§7[§2FACTION§7] §e".$sender->getName()."§7: ".Main::getUtils()->vec3ToString($sender->getPosition()));
                    }
                }
                break;
            case "invite":
                if ($data["faction"] == "None") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to do this!");
                    return;
                }
                if (Main::getFactionManager()->isLeaderOfFaction($sender->getName(), $data["faction"]) or Main::getFactionManager()->isCaptainInFaction($sender->getName(), $data["faction"])) {
                    $player = array_shift($args);
                    if ($player == "") {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7You must provide a player to invite.");
                        return;
                    }
                    $player = Main::getInstance()->getServer()->getPlayerByPrefix($player);
                    if ($player == null) {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7This player is not online or doesn't exist.");
                        return;
                    }
                    if (isset(Main::getInstance()->players[$player->getName()]["invites"][$data["faction"]])) {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7This player already has a pending invite from this faction!");
                        return;
                    }
                    if (!Main::getInstance()->players[$player->getName()]["faction"] == "None") {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7This player is already in a faction! They must leave their faction before receiving any invites!");
                        return;
                    }
                    Main::getInstance()->players[$player->getName()]["invites"][$data["faction"]] = 120;
                    $player->sendMessage("§r§l§7(§c!§7) §r§fYou have received a invite from §e".$data["faction"]."§f. This invite will expire in §e2 minutes§f do §e/f accept ".$data["faction"]." §fto accept the invite.");
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7Successfully sent invite to §e".$player->getName()."§7.");
                } else {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7Only faction leaders and captains can do this!");
                }
                break;
            case "accept":
                if (!$data["faction"] == "None") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must not be in a faction to run this command!");
                    return;
                }
                $invites = $data["invites"];
                $name = array_shift($args);
                if ($name == "") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must provide a faction invite to accept!");
                    return;
                }
                if (!isset($invites[$name])) {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You do not have a pending invite from this faction! Do §e/f listinvites§7 to see a list of your current invitations.");
                    return;
                }
                Main::getInstance()->players[$sender->getName()]["faction"] = $name;
                $members = Main::getFactionManager()->getMembersInFaction($data["faction"]);
                foreach ($members as $name) {
                    $player = Main::getInstance()->getServer()->getPlayerByPrefix($name);
                    if ($player !== null) {
                        $player->sendMessage("§7[§2FACTION§7] §e".$sender->getName()." §7has joined the faction.");
                    }
                }
                Main::getInstance()->factions[$name]["members"][] = $sender->getName();
                $sender->sendMessage("§r§l§7(§c!§7) §r§fSuccessfully joined faction.");
                break;
            case "listinvites":
                $invites = $data["invites"];
                if (count($invites) < 1) {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You do not have any pending invites.");
                    return;
                }
                $str = "§l§6Pending Invites:§r§7\n";
                foreach ($invites as $inv => $time) {
                    $str .= "§e".$inv." | §7Time Remaining: §f".Main::getUtils()->intToTimeString($time)."\n§r§7";
                }
                $sender->sendMessage($str);
                break;
            case "leave":
                if ($data["faction"] == "None") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to leave a faction!");
                    return;
                }
                $fac = $data["faction"];
                unset(Main::getInstance()->factions[$fac]["members"][$sender->getName()]);
                Main::getInstance()->players[$sender->getName()]["faction"] = "None";
                $members = Main::getFactionManager()->getMembersInFaction($fac);
                foreach ($members as $name) {
                    $player = Main::getInstance()->getServer()->getPlayerByPrefix($name);
                    if ($player !== null) {
                        $player->sendMessage("§7[§2FACTION§7] §e".$sender->getName()." §7has left the faction.");
                    }
                }
                $sender->sendMessage("§r§l§7(§c!§7) §r§7Successfully left the faction.");
                break;
            case "sethome":
                if ($data["faction"] == "None") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to use this command.");
                    return;
                }
                $fac = $data["faction"];
                if (Main::getFactionManager()->isLeaderOfFaction($sender->getName(), $fac)) {
                    if (Main::getClaimManager()->getClaimAtPosition($sender->getPosition()) == $fac) {
                        Main::getInstance()->factions[$fac]["home"] = Main::getUtils()->vec3ToString($sender->getPosition());
                        $sender->sendMessage("§r§l§7(§c!§7) §r§fSuccessfully set faction home!");
                    } else {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7You can only set a faction home in your own claim!");
                    }
                } else {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7Only faction leaders can use this command.");
                }
                break;
            case "home":
                if ($data["faction"] == "None") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to use this command.");
                    return;
                }
                $fac = $data["faction"];
                if (Main::getInstance()->factions[$fac]["home"] == "None") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7Your faction doesn't have a home!");
                    return;
                }
                if (Main::getClaimManager()->getClaimAtPosition($sender->getPosition()) == $fac or in_array(Main::getClaimManager()->getClaimAtPosition($sender->getPosition()), Main::NON_DEATHBAN_CLAIMS)or Main::getClaimManager()->getClaimAtPosition($sender->getPosition()) == "Wilderness") {
                    Main::getInstance()->players[$sender->getName()]["teleporting"] = true;
                    Main::getInstance()->players[$sender->getName()]["teleporting-location"] = "home";
                    Main::getInstance()->getScheduler()->scheduleRepeatingTask(new TeleportTask(Time::TELEPORT_HOME_TIME, "home", $sender), 20);
                    $sender->sendMessage("§r§l§7(§c!§7) §r§fTeleportation timer has commenced!");
                } else {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You cannot use this command in enemy territory!");
                }
                break;
            case "deposit":
                if ($data["faction"] == "None") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to use this command.");
                    return;
                }
                $amount = array_shift($args);
                if ($amount == "") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must provide an amount to deposit!");
                    return;
                }
                $amount = (int)$amount;
                if (!is_numeric($amount)) {
                	if ($amount == "all") {
                		$amount = $data["money"];
                		goto to;
					} else {
						$sender->sendMessage("§r§l§7(§c!§7) §r§7The amount must be a number!");
						return;
					}
                }
                to:
                if ($amount < 5) {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must deposit atleast §a$5§7!");
                    return;
                }
                if ($data["money"] < $amount) {
                	$sender->sendMessage("§r§l§7(§c!§7) §r§7You do not have that much money!");
                	return;
				}
                $sender->sendMessage("§r§l§7(§c!§7) §r§fSuccessfully deposited §a$".$amount."§r§f into your faction balance.");
                Main::getInstance()->factions[$data["faction"]]["balance"] += $amount;
                Main::getInstance()->players[$sender->getName()]["money"] -= $amount;
                break;
			case "disband":
				if ($data["faction"] == "None") {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to use this command.");
					return;
				}
				if (Main::getFactionManager()->isLeaderOfFaction($sender->getName(), $data["faction"])) {
					$members = Main::getFactionManager()->getMembersInFaction($data["faction"]);
					foreach ($members as $member) {
						$player = Main::getInstance()->getServer()->getPlayerByPrefix($member);
						if ($player !== null) {
							$player->sendMessage("§7[§2FACTION§7] §eThe faction has been disbanded.");
						}
						Main::getInstance()->players[$member]["faction"] = "None";
					}
					if (isset(Main::getInstance()->claims[$data["faction"]])) {
						unset(Main::getInstance()->claims[$data["faction"]]);
						Main::getInstance()->toDelete["claimData"][] = $data["faction"];
					}
					Main::getInstance()->toDelete["factionData"][] = $data["faction"];
					unset(Main::getInstance()->factions[$data["faction"]]);
					$sender->sendMessage("§r§l§7(§c!§7) §r§cYour faction was disbanded.");
				} else {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7Only the faction leader can disband the faction.");
				}
				break;
			case "unclaim":
				if ($data["faction"] == "None") {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to use this command.");
					return;
				}
				if (Main::getFactionManager()->isLeaderOfFaction($sender->getName(), $data["faction"])) {
					if (!isset(Main::getInstance()->claims[$data["faction"]])) {
						$sender->sendMessage("§r§l§7(§c!§7) §r§7Your faction doesn't have a claim.");
						return;
					}
					unset(Main::getInstance()->claims[$data["faction"]]);
					Main::getInstance()->toDelete["claimData"][] = $data["faction"];
					$sender->sendMessage("§r§l§7(§c!§7) §r§fSuccessfully unclaimed territory.");
				} else {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7Only the faction leader can unclaim territory.");
				}
				break;

            case "withdraw":
                if ($data["faction"] == "None") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to use this command.");
                    return;
                }
                if (Main::getFactionManager()->isLeaderOfFaction($sender->getName(), $data["faction"]) or Main::getFactionManager()->isCaptainInFaction($sender->getName(), $data["faction"])) {
                    $amount = array_shift($args);
                    if ($amount == "") {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7You must provide an amount to withdraw!");
                        return;
                    }
                    $amount = (int)$amount;
                    if (!is_numeric($amount)) {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7The amount must be a number!");
                        return;
                    }
                    if ($amount < 0) {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7no.");
                        return;
                    }
                    if (!Main::getInstance()->factions[$data["faction"]]["balance"] <= $amount) {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7Your faction does not have this much money!");
                        return;
                    }
                    Main::getInstance()->factions[$data["faction"]]["balance"] += $amount;
                    Main::getInstance()->players[$sender->getName()]["money"] -= $amount;
                    $sender->sendMessage("§r§l§7(§c!§7) §r§fSuccessfully withdrawal §a$".$amount."§r§f from your faction balance.");
                } else {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must be the faction leader, or a faction captain to do this!");
                }
                break;
            case "promote":
                if ($data["faction"] == "None") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to use this command.");
                    return;
                }
                if (Main::getFactionManager()->isLeaderOfFaction($sender->getName(), $data["faction"])) {
                    $member = array_shift($args);
                    if ($member == "") {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7You must provide a member to promote.");
                        return;
                    }
                    $members = Main::getFactionManager()->getMembersInFaction($data["faction"]);
                    if (in_array($member, $members)) {
                        if (Main::getFactionManager()->isCaptainInFaction($member, $data["faction"])) {
                            $sender->sendMessage("§r§l§7(§c!§7) §r§7This member is already a captain. They cannot be promoted anymore!");
                            return;
                        }
						if (Main::getFactionManager()->isLeaderOfFaction($member, $data["faction"])) {
							$sender->sendMessage("§r§l§7(§c!§7) §r§7You cannot promote yourself.");
							return;
						}
						//unset wasnt working smh
						$for = Main::getInstance()->factions[$data["faction"]]["members"];
						$new = [];
						foreach ($for as $name) {
							if ($name !== $member) $new[] = $name;
						}
						Main::getInstance()->factions[$data["faction"]]["members"] = [];
						Main::getInstance()->factions[$data["faction"]]["members"] = $new;
                        Main::getInstance()->factions[$data["faction"]]["captains"][] = $member;
                        $sender->sendMessage("§r§l§7(§c!§7) §r§fSuccessfully promoted §e".$member."§f.");
                    } else {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7There is no one in your faction with that name!");
                    }
                } else {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7Only faction leaders can use this command!");
                }
                break;
            case "demote":
                if ($data["faction"] == "None") {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to use this command.");
                    return;
                }
                if (Main::getFactionManager()->isLeaderOfFaction($sender->getName(), $data["faction"])) {
                    $member = array_shift($args);
                    if ($member == "") {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7You must provide a member to demote.");
                        return;
                    }
                    $members = Main::getFactionManager()->getMembersInFaction($data["faction"]);
                    if (in_array($member, $members)) {
                        if (!Main::getFactionManager()->isCaptainInFaction($member, $data["faction"])) {
                            $sender->sendMessage("§r§l§7(§c!§7) §r§7This member is not a captain, you cannot demote them!");
                            return;
                        }
                        if (Main::getFactionManager()->isLeaderOfFaction($member, $data["faction"])) {
							$sender->sendMessage("§r§l§7(§c!§7) §r§7You cannot demote yourself.");
							return;
						}
						$for = Main::getInstance()->factions[$data["faction"]]["captains"];
						$new = [];
						foreach ($for as $name) {
							if ($name !== $member) $new[] = $name;
						}
						Main::getInstance()->factions[$data["faction"]]["captains"] = [];
						Main::getInstance()->factions[$data["faction"]]["captains"] = $new;
						Main::getInstance()->factions[$data["faction"]]["members"][] = $member;
                        $sender->sendMessage("§r§l§7(§c!§7) §r§fSuccessfully demoted §e".$member."§f.");
                    } else {
                        $sender->sendMessage("§r§l§7(§c!§7) §r§7There is no one in your faction with that name!");
                    }
                } else {
                    $sender->sendMessage("§r§l§7(§c!§7) §r§7Only faction leaders can use this command!");
                }
                break;
			case "kick":
				if ($data["faction"] == "None") {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to use this command.");
					return;
				}
				if (!Main::getFactionManager()->isLeaderOfFaction($sender->getName(), $data["faction"])) {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7You need to be the leader of your faction to kick a player.");
					return;
				}
				$kick = array_shift($args);
				if ($kick == "") {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7You need to provide a player from your faction to kick.");
					return;
				}
				if (!in_array($kick, Main::getFactionManager()->getMembersInFaction($data["faction"]))) {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7This player is not in your faction.");
					return;
				}
				unset(Main::getInstance()->factions[$data["faction"]][$kick]);
				$sender->sendMessage("§r§l§7(§c!§7) §r§fSuccessfully kicked §a$kick §ffrom your faction.");
				break;
			case "claim":
				if ($data["faction"] == "None") {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7You must be in a faction to use this command.");
					return;
				}
				if (!Main::getFactionManager()->isLeaderOfFaction($sender->getName(), $data["faction"])) {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7You need to be the leader of your faction to get a claim!");
					return;
				}
				if (isset(Main::getInstance()->claims[$data["faction"]])) {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7Your faction already has a claim! Do §a/f unclaim §r§7if you would like to claim again!");
					return;
				}
				if ($data["claiming"]) {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7You have left claiming mode.");
					Main::getInstance()->players[$sender->getName()]["claiming"] = false;
					return;
				}
				Main::getFactionManager()->giveClaimWand($sender);
				$sender->sendMessage("§r§l§7(§c!§7) §r§fYou are now in claiming mode.");
				Main::getInstance()->players[$sender->getName()]["claiming"] = true;
				break;
			case "stuck":
				$claim = $data["claim"];
				$arr = [];
				foreach (Main::getInstance()->factions as $name => $fa) {
					$arr[] = $name;
				}
				if (!in_array($claim, $arr)) {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7You can only f stuck in faction bases.");
					return;
				}
				if ($claim == $data["faction"]) {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7You cannot stuck out of your own base!");
					return;
				}
				Main::getInstance()->getScheduler()->scheduleRepeatingTask(new TeleportTask(60 * 20, "stuck",  $sender), 20);
				break;
			case "info":
				$faction = array_shift($args);
				if ($faction == "") {
					$fac = $data["faction"];
					if ($fac !== "None") {
						$this->sendInfo($sender, $fac);
						return;
					}
					$sender->sendMessage("§r§l§7(§c!§7) §r§7Please provide a faction to get info from!");
					return;
				}
				if (!isset(Main::getInstance()->factions[$faction])) {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7There is no faction that goes by the name: §f".$faction."§7.");
					return;
				}
				$this->sendInfo($sender, $faction);
				break;
			case "who":
				$faction = array_shift($args);
				if ($faction == "") {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7Please provide a faction to get info from!");
					return;
				}
				$player = Main::getInstance()->getServer()->getPlayerByPrefix($faction);
				if ($player == null) {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7This player is not online or doesn't exist!");
					return;
				}
				if (Main::getInstance()->players[$player->getName()]["faction"] == "None") {
					$sender->sendMessage("§r§l§7(§c!§7) §r§7This player is not in a faction.");
					return;
				}
				$this->sendInfo($player, Main::getInstance()->players[$player->getName()]["faction"]);
				break;
			case "top":
				$top = [];
				foreach (Main::getInstance()->factions as $name => $info) {
					$top[$name] = 0;
					foreach (Main::getFactionManager()->getMembersInFaction($name) as $pna) {
						$kills = Main::getInstance()->players[$pna]["kills"];
						$top[$name] += $kills;
					}
				}
				asort($top);
				$topString = "";
				$topPos = 1;
				foreach ($top as $name => $kills) {
					$number = "";
					if ($topPos == 1) $number = "§2#1";
					if ($topPos == 2) $number = "§a#2";
					if ($topPos == 3) $number = "§6#3";
					if ($topPos > 3) $number = "§7#".$topPos;
					$topString .= "§r$number"." §c$name §7| §r§f".$kills;
					if (!($topPos + 1) > 10) $topString .= "\n";
					$topPos++;
				}
				$sender->sendMessage("§7".str_repeat("―", 25));
				$sender->sendMessage($topString);
				$sender->sendMessage("§7".str_repeat("―", 25));
				break;
            default:
                $sender->sendMessage(Main::getUtils()->arrayToDescendingString($help));
        }
    }

	//detroit.
    public function sendInfo(Player $player, string $faction) : void {
    	$data = Main::getInstance()->factions[$faction];
    	$home = $data["home"] == "None" ? "None." : explode(":", $data["home"])[0].", ".explode(":", $data["home"])[1];
		$members = count(Main::getFactionManager()->getMembersInFaction($faction));
		$leader = $data["owner"];
		$captains = $data["captains"];
		$memberList = $data["members"];
		$dtr = $data["dtr"];
		$balance = $data["balance"];
		$fac_made_raidable = $data["factions-made-raidable"];
		$dtr_regen = $data["dtr-freeze-time"];
		$player->sendMessage(str_repeat("―", 25));//§
		$player->sendMessage("§9".$faction."§7 [".$members."/".Main::MAX_MEMBERS_PER_FACTION."] §1- §eHQ§7: §f".$home);
		$player->sendMessage("§eLeader§7: ".($this->isPlayerOnline($leader) ? "§a".$leader."§e[§a".Main::getInstance()->players[$leader]["kills"]."§r§e]" : "§7".$leader."§e[§a".Main::getInstance()->players[$leader]["kills"]."§e]"));
		$captainArray = [];
		foreach ($captains as $cap) {
			$captainArray[] = $this->isPlayerOnline($cap) ? "§a".$cap."§e[§a".Main::getInstance()->players[$cap]["kills"]."§e]" : "§7".$cap."§e[§a".Main::getInstance()->players[$cap]["kills"]."§e]";
		}
		$captainString = count($captainArray) > 0 ? implode("§r§7,", $captainArray) : "§7None";
		$player->sendMessage("§eCaptains§7: ".$captainString);
		$memberArray = [];
		foreach ($memberList as $cap) {//Main::getInstance()->players[$leader]["kills"]
			$memberArray[] = $this->isPlayerOnline($cap) ? "§a".$cap."§e[§a".Main::getInstance()->players[$cap]["kills"]."§e]" : "§7".$cap."§e[§a".Main::getInstance()->players[$cap]["kills"]."§e]";
		}
		$memberString = count($memberArray) > 0 ? implode("§r§7,", $memberArray) : "§7None";
		$player->sendMessage("§eMembers§7: ".$memberString);
		$player->sendMessage("§eBalance§7: §9$".$balance);
		$sq = hex2bin("e29688");
		$dtr = (float)$dtr < 0.1 ? "§a".$dtr."§c".$sq : "§a".$dtr.hex2bin("e29688");
		$player->sendMessage("§eDeaths Until Raidable§7: ".$dtr);
		$player->sendMessage("§eFactions Made Raidable§7: §c".$fac_made_raidable);
		$player->sendMessage("§eDTR Freeze§7: §9".Main::getUtils()->intToTimeString($dtr_regen));
		$player->sendMessage(str_repeat("―", 25));
    }

    private function isPlayerOnline(string $player) : bool {
    	return Main::getInstance()->getServer()->getPlayerByPrefix($player) !== null;
	}

}