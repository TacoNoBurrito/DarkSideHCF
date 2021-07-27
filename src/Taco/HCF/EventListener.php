<?php namespace Taco\HCF;

use pocketmine\block\BaseSign;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\FenceGate;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\nbt\NoSuchTagException;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use Taco\HCF\other\entities\HCFPearl;
use Taco\HCF\other\Time;
use Taco\HCF\partner\entity\PartnerRollEntity;
use Taco\HCF\partner\entity\projectile\Switcher;
use function explode;
use function in_array;
use function strtolower;
use function time;
use function var_dump;

class EventListener implements Listener {

    private array $interactCooldown = [];

    private array $unMove = [];

	private array $usedLive = [];

    public function onPreLoad(PlayerLoginEvent $event) : void {
        $player = $event->getPlayer();
        Main::getPlayerManager()->loadPlayer($player);
        if (Main::getInstance()->players[$player->getName()]["deathban-time"] > 1 and !Main::getInstance()->getServer()->isOp($player->getName())) {
        	if (Main::getInstance()->players[$player->getName()]["lives"] > 0) {
				Main::getInstance()->players[$player->getName()]["lives"] -= 1;
				Main::getInstance()->onlinePlayerNames[] = $player->getName();
				$this->interactCooldown[$player->getName()] = 0;
				$this->usedLive[] = $player->getName();
				return;
			}
            $player->kick("§cYou are deathbanned!\n§cTime: §r§e".Main::getUtils()->intToTimeString(Main::getInstance()->players[$player->getName()]["deathban-time"]), false);
        }
    }

    public function onQuit(PlayerQuitEvent $event) : void {
        $player = $event->getPlayer();
        Main::getInstance()->hud->removePlayer($player);
        unset(Main::getInstance()->onlinePlayerNames[$player->getName()]);
        unset($this->interactCooldown[$player->getName()]);
        if (in_array($player->getName(), $this->unMove)) unset($this->unMove[$player->getName()]);
        $event->setQuitMessage("");
        $e = $event->getQuitReason();
        if ($e == Main::SAFE_LOGOUT_MESSAGE) return;
        if ($e == Main::DEATHBAN_LOGOUT_MESSAGE) return;
        $pos = $player->getPosition();
        $items = [];
        foreach ($player->getArmorInventory()->getContents() as $index => $info) {
        	$items[] = $info;
		}
        foreach ($player->getInventory()->getContents() as $index => $te) {
        	$items[] = $te;
		}
        Main::getUtils()->spawnLogoutVillager(new Vector3($pos->getX(), $pos->getY(), $pos->getZ()), $player->getName(), $items);
    }

    public function onJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();
        $player->sendMessage("§7You have joined our §r§fBETA HCF §r§7map.\n\n§r§eDiscord: discord.darksidepe.xyz");
        $event->setJoinMessage("");
        Main::getClasses()->reloadClass($player);
        $player->getArmorInventory()->getListeners()->add(new CallbackInventoryListener(function(Inventory $inventory, int $slot, Item $oldItem) : void{
            $player = $inventory->getHolder();
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(
                function () use ($player) : void {
                    Main::getClasses()->reloadClass($player);
                }
            ), 10);
        }, null));
        if (Main::getInstance()->players[$player->getName()]["didDie"]) {
			Main::getInstance()->players[$player->getName()]["didDie"] = false;
			$player->sendMessage("§o§cYour combat logger has died, you lost all of your items.");
			$def = Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation();
			$player->teleport(new Location($def->getX(), $def->getY(), $def->getZ(), 0.0, 0.0, Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld()));
		}
        Main::getInstance()->hud->addPlayer($player);
        Main::getInstance()->hud->setPercentageFor([$player], 1);
        if (in_array($player->getName(), $this->usedLive)) {
        	unset($this->usedLive[$player->getName()]);
        	$player->sendMessage("§o§cYou have used §a1 live §o§cbecause you logged in whilst deathbanned and had a life.");
		}
    }

    public function throwPearl(PlayerItemUseEvent $event) : void {
        $player = $event->getPlayer();
        $this->interactCooldown[$player->getName()] = time();
        $item = $player->getInventory()->getItemInHand();
        $thrown = false;
        if ($item->getId() == ItemIds::ENDER_PEARL) {
        	if (Main::getInstance()->players[$player->getName()]["timers"]["pearl-cooldown"] > 0) {
        		$event->cancel();
        		return;
        	}
        	Main::getInstance()->players[$player->getName()]["timers"]["pearl-cooldown"] = Main::DEFAULT_PEARL_COOLDOWN;
        	$event->cancel();
        	$entity = new HCFPearl(Location::fromObject($player->getPosition(), $player->getPosition()->getWorld()), null, $player);
        	$entity->spawnToAll();
        	$thrown = true;
        } else if ($item->getId() == ItemIds::SNOWBALL) {
        	if (Main::getPartnerManager()->hasTag($item, "switcher")) {
				if (Main::getInstance()->players[$player->getName()]["timers"]["switcher"] > 0) {
					$player->sendMessage("§eYour switcher is still on cooldown for §d".Main::getUtils()->secondsToEnderpearlCD(Main::getInstance()->players[$player->getName()]["timers"]["switcher"])."§e!");
					$event->cancel();
					return;
				}
				Main::getInstance()->players[$player->getName()]["timers"]["switcher"] = 30;
				$event->cancel();
				$entity = new Switcher(Location::fromObject($player->getPosition(), $player->getPosition()->getWorld()), null, $player);
				$entity->spawnToAll();
				$thrown = true;
			}
		}
        if ($thrown) {
        	$player->getInventory()->setItemInHand($player->getInventory()->getItemInHand()->setCount($player->getInventory()->getItemInHand()->getCount() - 1));
		}
    }

    public function onDeath(PlayerDeathEvent $event) : void {
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            $dmg = $cause->getDamager();
            if ($dmg instanceof Player) {
            	if (Main::getInstance()->players[$player->getName()]["faction"] !== "None") {
					$members = Main::getFactionManager()->getMembersInFaction(Main::getInstance()->players[$player->getName()]["faction"]);
					foreach ($members as $name) {
						$player = Main::getInstance()->getServer()->getPlayerByPrefix($name);
						if ($player !== null) {
							$player->sendMessage("§7[§2FACTION§7] §e" . $player->getName() . " §7has died.");
						}
					}
					Main::getInstance()->factions[Main::getInstance()->players[$player->getName()]["faction"]]["dtr-freeze"] = Time::THIRTY_MINUTES;
				}
                Main::getUtils()->processDeath($dmg, $player);
                $event->setDeathMessage("");
                Main::getUtils()->registerDeathban($player);
                return;
            }
        }
        if ($cause instanceof EntityDamageEvent) {
            Main::getInstance()->players[$player->getName()]["deaths"] = 0;
            Main::getInstance()->players[$player->getName()]["killstreak"] = 0;
            $e = "§c".$player->getName()."§4[".Main::getInstance()->players[$player->getName()]["kills"]."]";
            $members = Main::getFactionManager()->getMembersInFaction(Main::getInstance()->players[$player->getName()]["faction"]);
            foreach ($members as $name) {
                $player = Main::getInstance()->getServer()->getPlayerByPrefix($name);
                if ($player !== null) {
                    $player->sendMessage("§7[§2FACTION§7] §e".$player->getName()." §7has died.");
                }
            }
            Main::getUtils()->registerDeathban($player);
            switch($cause->getCause()) {
                case EntityDamageEvent::CAUSE_FALL:
                    $event->setDeathMessage($e."§r§e fell to their death.");
                    break;
                case EntityDamageEvent::CAUSE_FIRE:
                case EntityDamageEvent::CAUSE_FIRE_TICK:
                case EntityDamageEvent::CAUSE_LAVA:
                    $event->setDeathMessage($e."§r§e burned.");
                    break;
                case EntityDamageEvent::CAUSE_DROWNING:
                    $event->setDeathMessage($e."§r§e drowned.");
                    break;
                case EntityDamageEvent::CAUSE_SUFFOCATION:
                    $event->setDeathMessage($e."§r§e suffocated.");
                    break;
                default:
                    $event->setDeathMessage($e."§r§e died.");
            }
        }
    }

    public function onMove(PlayerMoveEvent $event) : void {
        $player = $event->getPlayer();
        if (in_array($player->getName(), $this->unMove)) {
        	$player->teleport($event->getFrom());
        	unset($this->unMove[$player->getName()]);
		}
        $claim = Main::getInstance()->players[$player->getName()]["claim"];
        $currentClaim = Main::getClaimManager()->getClaimAtPosition($player->getPosition());
        if ($currentClaim !== $claim) {
            Main::getInstance()->players[$player->getName()]["claim"] = $currentClaim;
            $type = in_array($currentClaim, Main::NON_DEATHBAN_CLAIMS) ? "§aNon-Deathban" : "§cDeathban";
            $type1 = in_array($claim, Main::NON_DEATHBAN_CLAIMS) ? "§aNon-Deathban" : "§cDeathban";
            if (in_array($claim, Main::NON_DEATHBAN_CLAIMS)) $claim = "§r§7".$claim;
            else $claim = "§r§a".$claim;
			if (in_array($currentClaim, Main::NON_DEATHBAN_CLAIMS)) $currentClaim = "§r§7".$currentClaim;
			else $currentClaim = "§r§a".$currentClaim;
            $player->sendMessage("§eNow leaving: §6".$claim." §e(".$type1."§r§e)");
            $player->sendMessage("§eNow entering: §6".$currentClaim." §e(".$type."§r§e)");
        }
        if (Main::getInstance()->players[$player->getName()]["teleporting"]) {
            Main::getInstance()->players[$player->getName()]["teleporting"] = false;
            Main::getInstance()->players[$player->getName()]["teleport-time-remaining"] = 0;
            $player->sendMessage("§r§l§7(§c!§7) §r§cTeleportation ended due to movement.");
        }
    }

    public function onChat(PlayerChatEvent $event) : void {
        $player = $event->getPlayer();
        $rankf = Main::getUtils()->getRankFormatted(Main::getInstance()->players[$player->getName()]["rank"]);
        $faction = Main::getInstance()->players[$player->getName()]["faction"] == "None" ? "" : "§e[§c".Main::getInstance()->players[$player->getName()]["faction"]."§e]";
        $event->setFormat($faction." $rankf §r§f".$player->getName()."§7: ".$event->getMessage());
    }

    public function onSignInteract(PlayerInteractEvent $event) : void {
        $player = $event->getPlayer();
        $pos = $event->getBlock()->getPos();
        $block = $event->getBlock()->getPos()->getWorld()->getTile(new Vector3($pos->getX(), $pos->getY(), $pos->getZ()));
        if ($block instanceof \pocketmine\block\tile\Sign) {
            $lines = $block->getText()->getLines();
            if (count($lines) < 2) return;
            $ones = ["elevator", "[elevator]"];
            if (in_array(strtolower($lines[0]), $ones)) {
                $two = strtolower($lines[1]);
                if ($two == "up") {
                    $p = $block->getPos();
                    $pos = Main::getUtils()->getSafestUpSignBlock(new Vector3($p->getX(), $p->getY(), $p->getZ()));
                    if ($pos == null) {
                        $player->sendPopup("§cCould not find safe position.");
                    } else {
                        $player->teleport($pos);
                        $player->sendPopup("§aTeleported!");
                    }
                }
            } else {
            	$ones = ["Sell", "Buy"];
            	if (in_array(strtolower($lines[0]), $ones)) {
            		if (in_array(Main::getClaimManager()->getClaimAtPosition($pos), Main::NON_DEATHBAN_CLAIMS)) {
            			$player->sendMessage("no cookies 4 u");
					}
				}
			}
        }
    }

    public function onClaimConfirm(PlayerItemUseEvent $event) : void {
    	$player = $event->getPlayer();
    	$item = $event->getItem();
    	if (Main::getFactionManager()->isRealClaimWand($item)) {
    		$data = Main::getInstance()->players[$player->getName()];
    		if (Main::getFactionManager()->isLeaderOfFaction($player->getName(), $data["faction"])) {
    			$pos1 = $data["claim-pos1"];
    			$pos2 = $data["claim-pos2"];
    			if ($pos1 == null or $pos2 == null) {
    				$player->sendMessage("§r§l§7(§c!§7) §r§7You need to have both claim positions set to confirm a claim.");
    				return;
				} else {
    				$dist = $pos1->distance($pos2);
    				if ($dist < 5) {
    					$player->sendMessage("§r§l§7(§c!§7) §r§7Claims must be at least 5x5!");
    					return;
					} else if ($dist > 125) {
    					$player->sendMessage("§r§l§7(§c!§7) §r§7Claims cannot be more than 100x100");
    					return;
					} else {
    					$distSpawn = false;
    					$spawn = Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation();
    					if (Main::getUtils()->XZDist($spawn, $pos1) < 500) $distSpawn = true;
						if (Main::getUtils()->XZDist($spawn, $pos2) < 500) $distSpawn = true;
						if ($distSpawn) {
							$player->sendMessage("§r§l§7(§c!§7) §r§7Both claim positions must be 500 blocks from spawn!");
							return;
						}
						$price = Main::getClaimManager()->getClaimPrice($pos1, $pos2);
						$money = (int)Main::getInstance()->factions[$data["faction"]]["balance"];
						if ($price > $money) {
							$player->sendMessage("§r§l§7(§c!§7) §r§7Your faction does not have enough money to get this claim! Your faction needs §a$".($price - $money)." §r§7to claim this land!");
							return;
						}
						if (isset(Main::getInstance()->claims[$data["faction"]])) {
							$player->sendMessage("§r§l§7(§c!§7) §r§7Your faction already has a claim! Do §a/f unclaim §r§7if you would like to claim again!");
							return;
						}
						Main::getInstance()->factions[$data["faction"]]["balance"] -= $price;
						Main::getInstance()->players[$player->getName()]["claiming"] = false;
						$player->getInventory()->setItemInHand(ItemFactory::getInstance()->get(0));
						Main::getClaimManager()->addClaim($data["faction"], $pos1, $pos2);
						$player->sendMessage("§r§l§7(§c!§7) §r§eSuccessfully claimed land for your faction.");
						Main::getUtils()->clearAllPillars($player);
						Main::getClaimManager()->addClaim($data["faction"], $data["claim-pos1"], $data["claim-pos2"]);
    				}
				}
    		}
		}
	}

    public function onClaimInteract(PlayerInteractEvent $event) : void {
    	$player = $event->getPlayer();
		/*if (time() - $this->interactCooldown[$player->getName()] < 1) {
			if (!$event->getBlock() instanceof FenceGate) {
				$event->cancel();
				return;
			}
		}*/
		$this->interactCooldown[$player->getName()] = time();
    	$block = $event->getBlock();
    	$item = $event->getItem();
    	if ($player->isSneaking()) {
			if (Main::getFactionManager()->isRealClaimWand($item)) {
				$type = $event->getAction();
				if ($type == PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
					$player->sendMessage("§r§l§7(§c!§7) §r§aSet claim position one.");
					Main::getInstance()->players[$player->getName()]["claim-pos1"] = $block->getPos();
					if (Main::getInstance()->players[$player->getName()]["claim-pos1"] !== null) Main::getUtils()->clearPillar($player, $block->getPos());
					Main::getUtils()->makePillar($player, $block->getPos(), BlockFactory::getInstance()->get(BlockLegacyIds::GLASS, 0));
				} else if ($type == PlayerInteractEvent::LEFT_CLICK_BLOCK) {
					$player->sendMessage("§r§l§7(§c!§7) §r§aSet claim position two.");
					Main::getInstance()->players[$player->getName()]["claim-pos2"] = $block->getPos();
					if (Main::getInstance()->players[$player->getName()]["claim-pos2"] !== null) Main::getUtils()->clearPillar($player, $block->getPos());
					Main::getUtils()->makePillar($player, $block->getPos(), BlockFactory::getInstance()->get(BlockLegacyIds::GLASS, 0));
				}
			}
		}
	}

    public function onHit(EntityDamageByEntityEvent $event) : void {
		if ($event->getModifier(EntityDamageEvent::MODIFIER_PREVIOUS_DAMAGE_COOLDOWN) < 0.0) {
			$event->cancel();
			return;
		}
        $hit = $event->getEntity();
        $damager = $event->getDamager();
        if ($hit instanceof Player and $damager instanceof Player) {
            if (Main::getInstance()->sotw_timer > 0) {
                $damager->sendMessage("§eYou cannot hit players while §a§lSOTW Timer §r§eis enabled!");
                $event->cancel();
                return;
            }
            if (Main::getInstance()->players[$damager->getName()]["pvp-timer"] > 0) {
            	$damager->sendMessage("§eYou cannot hit players while on §2§lPVP-Timer§r§e.");
            	$event->cancel();
            	return;
			}
            if (Main::getInstance()->players[$hit->getName()]["pvp-timer"] > 0) {
                $damager->sendMessage("§eThis player still has §2§lPVP-Timer§r§e for §2".Main::getUtils()->intToTimeString(Main::getInstance()->players[$hit->getName()]["pvp-timer"])."§e!");
                $event->cancel();
                return;
            }
            $regions = [
                Main::getClaimManager()->getClaimAtPosition($hit->getPosition()),
                Main::getClaimManager()->getClaimAtPosition($damager->getPosition())
            ];
            if (in_array($regions[0], Main::NON_DEATHBAN_CLAIMS) or in_array($regions[1], Main::NON_DEATHBAN_CLAIMS)) {
                $event->cancel();
                return;
            }
            $factions = [
                Main::getInstance()->players[$hit->getName()]["faction"],
                Main::getInstance()->players[$damager->getName()]["faction"]
            ];
            if (!in_array("None", $factions)) {
				if ($factions[0] == $factions[1]) {
					$damager->sendMessage("§eYou cannot hit §2" . $hit->getName() . "§e!");
					$event->cancel();
					return;
				}
			}
            $event->setKnockBack(0.382);
            Main::getInstance()->players[$hit->getName()]["timers"]["spawntag"] = 20;
            Main::getInstance()->players[$damager->getName()]["timers"]["spawntag"] = 20;
            $item = $damager->getInventory()->getItemInHand();
            if ($item->getId() == ItemIds::GOLD_SWORD) {
            	$class = Main::getInstance()->players[$damager->getName()]["class"];
            	if ($class == "rogue") {
            		$timer = Main::getInstance()->players[$damager->getName()]["timers"]["rogue"];
            		if ($timer > 0) {
            			$hit->sendMessage("§eYou cannot backstab for §2".Main::getUtils()->secondsToEnderpearlCD($timer)."s§e.");
            			$event->cancel();
            			return;
					}
					Main::getInstance()->players[$damager->getName()]["timers"]["rogue"] = 30;
            		$event->setBaseDamage($event->getBaseDamage()+5);
            		$damager->getInventory()->setItemInHand(ItemFactory::getInstance()->get(0));
            	}
			}
            if (Main::getPartnerManager()->hasTag($item, "slapper")) {
            	if (Main::getInstance()->players[$damager->getName()]["timers"]["slapper"] > 0) {
            		$damager->sendMessage("§eYour still on a cooldown for your slapper stick for §d".Main::getUtils()->secondsToEnderpearlCD(Main::getInstance()->players[$damager->getName()]["timers"]["slapper"])."§e!");
            		return;
				}
            	$hit->teleport(new Location($hit->getPosition()->getX(), $hit->getPosition()->getY(), $hit->getPosition()->getZ(), 0.00, $hit->getLocation()->getYaw()-180, $hit->getWorld()));

            	Main::getInstance()->players[$damager->getName()]["timers"]["slapper"] = 120 * 20;
            	$damager->sendMessage("§eYou have slapped §d".$hit->getName()."§e so hard they turned 180 degrees!");
            	$hit->sendMessage("§eYou were slapped by §d".$damager->getName()."§e.");
			}
        }
    }
	private $bardItemCooldown = [];
    public function bardItem(PlayerInteractEvent $event) : void {
        $player = $event->getPlayer();
        if (!isset($this->bardItemCooldown[$player->getName()])) {
        	$this->bardItemCooldown[$player->getName()] = 0;
		} else {
        	if (time() - $this->bardItemCooldown[$player->getName()] < 1) {
        		return;
			} else {
				$this->bardItemCooldown[$player->getName()] = time();
			}
		}
        $data = Main::getInstance()->players[$player->getName()];
        if ($data["class"] == "bard") {
            $item = $event->getItem();
            $map = Main::getFactionManager()->BARD_ITEM_MAP;
            if (isset($map[$item->getId()])) {
                $energy = $data["bard-energy"];
                if ($energy - $map[$item->getId()]["energy-needed"] < 1) {
                    $player->sendMessage("§cYou do not have enough energy to use §e".$map[$item->getId()]["name"]."§a.");
                    return;
                }
                $faction = $data["faction"];
                $effects = $map[$item->getId()]["effects"];
                $player->sendMessage("§aYou have used §e".$map[$item->getId()]["name"]."§a.");
                Main::getInstance()->players[$player->getName()]["bard-energy"] = $energy - $map[$item->getId()]["energy-needed"];
                if ($faction !== "None") {
                    $members = Main::getFactionManager()->getMembersInFaction($faction);
                    foreach ($members as $member) {
                        $p = Main::getInstance()->getServer()->getPlayerByPrefix($member);
                        if ($p !== null) {
                            if ($p->getName() !== $player->getName()) {
                                $p->sendMessage("§7[§2FACTION§7] §7The bard in your faction has used §e".$map[$item->getId()]["name"]." §r§7(".$player->getName().")");
                            }
                            foreach ($effects as $eff) {
                                $e = explode(":", $eff);
                                $inst = new EffectInstance(EffectIdMap::getInstance()->fromId((int)$e[0]), (int)$e[2] * 20, (int)$e[1] - 1);
                                $p->getEffects()->add($inst);
                            }
                            $player->getInventory()->setItemInHand($player->getInventory()->getItemInHand()->setCount($player->getInventory()->getItemInHand()->getCount() - 1));
                        }
                    }
                } else {
                    foreach ($effects as $eff) {
                        $e = explode(":", $eff);
                        $inst = new EffectInstance(EffectIdMap::getInstance()->fromId((int)$e[0]), (int)$e[2] * 20, (int)$e[1] - 1);
                        $player->getEffects()->add($inst);
						$player->getInventory()->setItemInHand($player->getInventory()->getItemInHand()->setCount($player->getInventory()->getItemInHand()->getCount() - 1));
                    }
                }
            }
        }
    }

    public function onClickItemInClaim(PlayerInteractEvent $event) : void {
    	$player = $event->getPlayer();
    	$block = $event->getBlock();
    	$claim = Main::getClaimManager()->getClaimAtPosition($block->getPos());
    	$faction = Main::getInstance()->players[$player->getName()]["faction"];
    	if ($claim == "Wilderness") return;
    	if (in_array($claim, Main::NON_DEATHBAN_CLAIMS)) {
    		$event->cancel();
    		return;
		}
    	if ($faction !== $claim) {
			if ((float)Main::getInstance()->factions[$faction]["dtr"] < 0.1) return;
    		$player->sendMessage("§eYou cannot do this in §d".$claim."§e's claim!");
    		$event->cancel();
    		if ($block instanceof FenceGate) $this->unMove[] = $player->getName();
		}
	}

    public function onPlace(BlockPlaceEvent $event) : void {
        $player = $event->getPlayer();
        $pos = $event->getBlock();
        $claim = Main::getClaimManager()->getClaimAtPosition($pos->getPos());
        $faction = Main::getInstance()->players[$player->getName()]["faction"];
        $item = $player->getInventory()->getItemInHand();
        if (Main::getPartnerManager()->isValidPartnerPackage($item)) {
            $event->cancel();
            Main::getPartnerManager()->openPartnerPackage($player, $pos);
            return;
        }
		if (Main::getInstance()->getServer()->isOp($player->getName())) return;
        if (in_array($claim, Main::NON_DEATHBAN_CLAIMS) or in_array($claim, Main::PROTECTED_CLAIMS)or $claim == "Spawn" or $claim == "spawn") {
            $event->cancel();
            return;
        }
		$distSpawn = false;
		$spawn = Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation();
		if (Main::getUtils()->XZDist($spawn, $pos->getPos()) < 500) $distSpawn = true;
		if ($distSpawn) {
			$player->sendMessage("§eYou must be at least §d500§e blocks from spawn to build!");
			$event->cancel();
			return;
		}
		var_dump("claim: $claim");
		var_dump("fac: $faction");
		if ($claim == "Wilderness") return;
        if ($claim !== $faction and !Main::getInstance()->getServer()->isOp($player->getName())) {
			if (!isset(Main::getInstance()->factions[$faction])) return;
			if ((float)Main::getInstance()->factions[$faction]["dtr"] < 0.1) return;
            $event->cancel();
            $player->sendMessage("§eYou cannot build in §d".$claim."§e's claim.");
        }
    }

    public function onBreak(BlockBreakEvent $event) : void {
        $player = $event->getPlayer();
        $pos = $event->getBlock()->getPos();
        $claim = Main::getClaimManager()->getClaimAtPosition($pos);
        $faction = Main::getInstance()->players[$player->getName()]["faction"];
        if (in_array($claim, Main::NON_DEATHBAN_CLAIMS) or in_array($claim, Main::PROTECTED_CLAIMS) or $claim == "Spawn" or $claim == "spawn") {
            $event->cancel();
            return;
        }
		if (Main::getInstance()->getServer()->isOp($player->getName())) return;
		$distSpawn = false;
		$spawn = Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation();
		if (Main::getUtils()->XZDist($spawn, $pos) < 500) $distSpawn = true;
		if ($distSpawn) {
			$player->sendMessage("§eYou must be at least §d500§e blocks from spawn to build!");
			$event->cancel();
			return;
		}
        if ($claim !== $faction and !Main::getInstance()->getServer()->isOp($player->getName())) {
        	if (!isset(Main::getInstance()->factions[$faction])) return;
        	if ((float)Main::getInstance()->factions[$faction]["dtr"] < 0.1) return;
            $event->cancel();
            $player->sendMessage("§eYou cannot build in §d".$claim."§e's claim.");
        }
    }

    public function onHitByProjectile(ProjectileHitEntityEvent $event) : void {
    	$hit = $event->getEntityHit();
    	if ($hit instanceof Player) {
    		$entity = $event->getEntity();
    		$owner = $entity->getOwningEntity();
    		if ($owner !== null and $owner instanceof Player) {
    			if ($entity instanceof Arrow) {
					$pk = new PlaySoundPacket();
					$pk->soundName = 'random.orb';
					$pk->pitch = 1.0;
					$pk->volume = 500.0;
					$pk->x = $owner->getPosition()->getX();
					$pk->y = $owner->getPosition()->getY();
					$pk->z = $owner->getPosition()->getZ();
					$owner->getNetworkSession()->sendDataPacket($pk);
    				$owner->sendMessage("§d".$hit->getName()." §eis now at §d".round($hit->getHealth(), 2)." §ehearts!");
				} else if ($entity instanceof Switcher) {
					$owner->sendMessage("§eYou have switchered §d".$hit->getName()."§e! s§d(".floor($hit->getPosition()->distance($entity->getStartPos()))." blocks)");
    				$pos1 = $owner->getPosition();
    				$pos2 = $hit->getPosition();
					$hit->teleport($pos1);
    				$hit->sendMessage("§d".$owner->getName()." §ehas switchered you!");
    				$owner->teleport($pos2);
				}
			}
		}
	}

	public function onHitGround(ProjectileHitBlockEvent $event) : void {
		$entity = $event->getEntity();
		$block = $event->getBlockHit();
		if ($entity instanceof Switcher) {
			$owner = $entity->getOwningEntity();
			if ($owner !== null and $owner instanceof Player) {
				$owner->sendMessage("§eYour switcher ball has hit §d" . $block->getName() . " (" . floor($block->getPos()->distance($entity->getStartPos())) . " blocks)");
			}
		}
	}

	public function onCrateInteract(PlayerInteractEvent $event) : void {
    	$player = $event->getPlayer();
    	if (!in_array(Main::getClaimManager()->getClaimAtPosition($player->getPosition()), Main::NON_DEATHBAN_CLAIMS)) return;
    	$item = $player->getInventory()->getItemInHand();
    	if (Main::getCrateUtils()->isRealCrateKey($item)) {
			$block = $event->getBlock();
			$toString = Main::getUtils()->vec3ToString($block->getPos()->floor());
			foreach (Main::getInstance()->crate as $name => $pos) {
				var_dump($pos.":".$toString);
				if ($pos == $toString) {
					try {
						if ($name == $item->getNamedTag()->getString("type")) {
							$type = $item->getNamedTag()->getString("type");
							$player->getInventory()->setItemInHand($player->getInventory()->getItemInHand()->setCount($player->getInventory()->getItemInHand()->getCount() - 1));
							$player->sendMessage("§eYou have opened a §r".Main::getCrateUtils()::COOL_NAME_TYPE_MAP[$type]." §r§ecrate.");
							Main::getCrateUtils()->giveCrateReward($player, $type);
						} else {
							$player->sendMessage("§eThis key does not work on this crate!");
						}
					} catch (NoSuchTagException $ex) {
						$player->sendMessage("§l§cWARNING >> §r§fThis is a §dCORRUPTED §r§fcrate key. Please exchange with a admin.");
					}
				}
				return;
			}
			$player->sendMessage("§eThis key does not work on this crate.");
		}
	}

	private array $pcpeCooldown = [];

	public function onPreProcess(PlayerCommandPreprocessEvent $event) : void {
    	$player = $event->getPlayer();
    	if (isset($this->pcpeCooldown[$player->getName()])) {
    		if (time() - $this->pcpeCooldown[$player->getName()] < 2) {
    			$player->sendMessage("§cPlease don't spam commands and/or messages.");
    			$event->cancel();
    			return;
			}
    		$this->pcpeCooldown[$player->getName()] = time();
		} else {
    		$this->pcpeCooldown[$player->getName()] = time();
		}
	}

}