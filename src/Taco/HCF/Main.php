<?php namespace Taco\HCF;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\world\World;
use Taco\HCF\commands\admin\CrateCommand;
use Taco\HCF\commands\admin\SetRankCommand;
use Taco\HCF\commands\ClaimCommand;
use Taco\HCF\commands\economy\GiveMoneyCommand;
use Taco\HCF\commands\economy\MyMoneyCommand;
use Taco\HCF\commands\FactionCommand;
use Taco\HCF\commands\KothCommand;
use Taco\HCF\commands\LivesCommand;
use Taco\HCF\commands\LogoutCommand;
use Taco\HCF\commands\PvPCommand;
use Taco\HCF\commands\KitCommand;
use Taco\HCF\commands\random\IDCommand;
use Taco\HCF\commands\random\VerCommand;
use Taco\HCF\commands\ReclaimCommand;
use Taco\HCF\commands\SOTWCommand;
use Taco\HCF\commands\SudoCommand;
use Taco\HCF\manager\ClaimManager;
use Taco\HCF\manager\FactionManager;
use Taco\HCF\manager\KothManager;
use Taco\HCF\manager\PlayerManager;
use Taco\HCF\other\crates\CrateUtil;
use Taco\HCF\other\crates\entity\PulsatingCrateEntity;
use Taco\HCF\other\entities\HCFPearl;
use Taco\HCF\other\entities\LogoutVillager;
use Taco\HCF\other\kits\KitsManager;
use Taco\HCF\other\modules\Classes;
use Taco\HCF\other\Utils;
use Taco\HCF\partner\entity\PartnerRollEntity;
use Taco\HCF\partner\entity\projectile\Switcher;
use Taco\HCF\partner\PartnerManager;
use Taco\HCF\tasks\BardRegenTask;
use Taco\HCF\tasks\EntityClearTask;
use Taco\HCF\tasks\KothTask;
use Taco\HCF\tasks\NESWTask;
use Taco\HCF\tasks\ScoreboardTask;
use Taco\HCF\tasks\ServerPlayerUpdateTask;
use Taco\HCF\tasks\TimerTask;
use xenialdan\apibossbar\DiverseBossBar;
use function strlen;

class Main extends PluginBase {

    public array $players = [];

    public array $factions = [];

    public array $claims = [];

    public array $toDelete = [];

    public array $onlinePlayerNames = [];

    public array $koth = [];

    public array $crate = [];

    private Config $playerData;

    private Config $factionData;

    private Config $claimData;

    private Config $kothData;

    private Config $crateData;

    private static self $instance;

    private static PlayerManager $playerManager;

    private static FactionManager $factionManager;

    private static ClaimManager $claimManager;

    private static Utils $utils;

    private static Classes $classes;

    private static PartnerManager $partnerManager;

    private static KitsManager $kitsManager;

    private static KothManager $kothManager;

    private static CrateUtil $crateUtils;

    public const EXCLUDE_DATA_SAVE = [
        "claim",
        "archertag-time",
        "class",
        "timers",
        "bard-energy",
		"claim-pos1",
		"claim-pos2",
		"claiming",
		"has-pillars-at",
		"stuck",
		"teleporting",
		"teleporting-location",
		"teleport-time-remaining"
    ];

    public const NON_DEATHBAN_CLAIMS = [
        "spawn",
        "Spawn"
    ];

    public const BANNED_FACTION_NAMES = [
        "South Road",
        "North Road",
        "East Road",
        "West Road",
        "Wilderness"
    ];

    public const PROTECTED_CLAIMS = [
		"South Road",
		"North Road",
		"East Road",
		"West Road"
	];

    public const VALID_RANKS = [
    	"None",
		"Mars",
		"Neptune",
		"Saturn",
		"Dark",
		"Helper",
		"Mod",
		"Admin",
		"Owner"
	];

    public const TIER_0 = [
		"None",
		"Mars",
		"Neptune",
		"Saturn",
		"Dark"
	];

    public const TIER_1 = [
    	"Helper",
		"Mod"
	];

    public const TIER_2 = [
    	"Owner",
		"Admin"
	];

    public const SERVER_OWNER_IGN = "xXBobPlaysMCXx";

    public const SAFE_LOGOUT_MESSAGE = "You have been safely logged out.";

    public const DEATHBAN_LOGOUT_MESSAGE = "You are now deathbanned!";

    public const DEFAULT_PEARL_COOLDOWN = 12;

    public const MAX_MEMBERS_PER_FACTION = 5;

    public int $sotw_timer = 0;

    public DiverseBossBar $hud;

    //Was added way late in the core. Most commands/events dont use this const for messages.
	// - NOTED BY: Taco (7/25/2021)
    public const MESSAGE_PREFIX = "§r§l§7(§c!§7) §r§";
    public const MESSAGE_SQUIGGLE = "§";

    public function onEnable() : void {
        self::$instance = $this;
        self::$playerManager = new PlayerManager();
        self::$factionManager = new FactionManager();
        self::$claimManager = new ClaimManager();
        self::$utils = new Utils();
        self::$classes = new Classes();
        self::$partnerManager = new PartnerManager();
        self::$kitsManager = new KitsManager();
        self::$kothManager = new KothManager();
        self::$crateUtils = new CrateUtil();
        self::$crateUtils->initCrates();
        self::$crateUtils->reloadCratePulsatingFloatingTextEntities();

        $this->playerData = new Config($this->getDataFolder()."playerData.yml", Config::YAML);
        $this->factionData = new Config($this->getDataFolder()."factionData.yml", Config::YAML);
        $this->claimData = new Config($this->getDataFolder()."claimData.yml", Config::YAML);
		$this->kothData = new Config($this->getDataFolder()."kothData.yml", Config::YAML);
		$this->crateData = new Config($this->getDataFolder()."crateData.yml", Config::YAML);

        $this->players = (array)$this->playerData->getAll();
        $this->factions = (array)$this->factionData->getAll();
        $this->claims = (array)$this->claimData->getAll();
        $this->koth = (array)$this->kothData->getAll();
        $this->crate = (array)$this->crateData->getAll();

        $this->getScheduler()->scheduleRepeatingTask(new ScoreboardTask(), 15);
        $this->getScheduler()->scheduleRepeatingTask(new TimerTask(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new BardRegenTask(), 15);
        $this->getScheduler()->scheduleRepeatingTask(new ServerPlayerUpdateTask(), 20 * 60);
        $this->getScheduler()->scheduleRepeatingTask(new NESWTask(), 2);
		$this->getScheduler()->scheduleRepeatingTask(new EntityClearTask(), 20 * 600);
		if (count($this->koth) < 1) {
			$this->getLogger()->critical("There are no KoTH arenas setup. Please do /koth help in-game to setup arenas.");
		} else $this->getScheduler()->scheduleRepeatingTask(new KothTask(), 22);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		$commands = ["me", "kill"];
		foreach ($commands as $cmd) {
			$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand($cmd));
        }

        $this->getServer()->getCommandMap()->registerAll("DarkSideHCF", [
            new SOTWCommand("sotw"),
            new PvPCommand("pvp"),
            new FactionCommand("faction"),
            new ClaimCommand("claim"),
            new SudoCommand("sudo"),
            new ReclaimCommand("reclaim"),
			new MyMoneyCommand("mymoney"),
			new GiveMoneyCommand("givemoney"),
			new VerCommand("ver"),
			new KitCommand("kit"),
			new IDCommand("id"),
			new SetRankCommand("setrank"),
			new LogoutCommand("logout"),
			new LivesCommand("lives"),
			new KothCommand("koth"),
			new CrateCommand("crate")
        ]);

        $entityFactory = EntityFactory::getInstance();
		$entityFactory->register(PartnerRollEntity::class, function(World $world, CompoundTag $nbt) : PartnerRollEntity {
			return new PartnerRollEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ["PartnerRollEntity"]);
		$entityFactory->register(HCFPearl::class, function(World $world, CompoundTag $nbt) : HCFPearl {
			return new HCFPearl(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ["HCFPearl"]);
		$entityFactory->register(Switcher::class, function(World $world, CompoundTag $nbt) : Switcher {
			return new Switcher(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ["Switcher"]);
		$entityFactory->register(LogoutVillager::class, function(World $world, CompoundTag $nbt) : LogoutVillager {
			return new LogoutVillager(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ["LogoutVillager"]);
		$entityFactory->register(PulsatingCrateEntity::class, function(World $world, CompoundTag $nbt) : PulsatingCrateEntity {
			return new PulsatingCrateEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ["PulsatingCrateEntity"]);

        foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) {
        	$world->loadChunk(0 >> 4, 0 >> 4);
        	$world->setSpawnLocation(new Vector3(0, 100, 0));
        	foreach ($world->getEntities() as $entity) {
        		if (!$entity instanceof Player) $entity->flagForDespawn();
			}
		}

        $this->hud = (new DiverseBossBar())->setTitle(" ");

        $this->getLogger()->alert("\n§a\n§a\n§r§l§cDarkSide HCF Initialized.\nCreated by Taco. Please do not use without permission.\n§c\n§b");
    }

    public function onDisable() : void {
        $this->saveAllData();
    }

    public function saveAllData() : void {
        foreach ($this->toDelete as $key => $info) {
            if ($key == "factionData") {
                $this->factionData->remove($info);
            } else if ($key == "playerData") {
                $this->playerData->remove($info);
            } else if ($key == "claimData") {
                $this->claimData->remove($info);
            } else if ($key == "kothData") {
            	$this->kothData->remove($info);
			} else if ($key == "crateData") {
            	$this->crateData->remove($info);
			}
        }
        $this->factionData->save();
        $this->playerData->save();
        $this->claimData->save();
        $this->kothData->save();
        $this->crateData->save();
        $this->toDelete = [];
        $this->getLogger()->notice("[SAVE] Deleted all removable values.");
        foreach ($this->players as $name => $info) {
            $this->playerData->remove($name);
            $this->playerData->set($name, self::$utils->pre_save_array_clean($info));
        }
        $this->playerData->save();
        $this->getLogger()->notice("[SAVE] Saved all players.");
        foreach ($this->factions as $name => $info) {
            $this->factionData->remove($name);
            $this->factionData->set($name, self::$utils->pre_save_array_clean($info));
        }
        $this->factionData->save();
        $this->getLogger()->notice("[SAVE] Saved all factions.");
        foreach ($this->claims as $name => $info) {
            $this->claimData->remove($name);
            $this->claimData->set($name, self::$utils->pre_save_array_clean($info));
        }
        $this->claimData->save();
        $this->getLogger()->notice("[SAVE] Saved all claims.");
        foreach ($this->koth as $name => $info) {
        	$this->kothData->remove($name);
        	$this->kothData->set($name, $info);
		}
        $this->kothData->save();
		$this->getLogger()->notice("[SAVE] Saved koth.");
		foreach ($this->crate as $name => $info) {
			$this->crateData->remove($info);
			$this->crateData->set($name, $info);
		}
		$this->crateData->save();;
		$this->getLogger()->notice("[SAVE] Saved crates.");
		sleep(4);
    }

    public static function getInstance() : self {
        return self::$instance;
    }

    public static function getPlayerManager() : PlayerManager {
        return self::$playerManager;
    }

    public static function getFactionManager() : FactionManager {
        return self::$factionManager;
    }

    public static function getClaimManager() : ClaimManager {
        return self::$claimManager;
    }

    public static function getUtils() : Utils {
        return self::$utils;
    }

    public static function getClasses() : Classes {
        return self::$classes;
    }

    public static function getPartnerManager() : PartnerManager {
        return self::$partnerManager;
    }

    public static function getKitsManager() : KitsManager {
    	return self::$kitsManager;
	}

	public static function getKothManager() : KothManager {
    	return self::$kothManager;
	}

	public static function getCrateUtils() : CrateUtil {
    	return self::$crateUtils;
	}

}