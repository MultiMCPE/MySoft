<?php

/*
  __  __       ____         __ _
 |  \/  |_   _/ ___|  ___  / _| |_
 | |\/| | | | \___ \ / _ \| |_| __|
 | |  | | |_| |___) | (_) |  _| |_
 |_|  |_|\__, |____/ \___/|_|  \__|
         |___/
*/

namespace pocketmine;

use pocketmine\{tile\MobSpawner, tile\ShulkerBox, utils\Color}; //by ssss1
use pocketmine\block\Block;
use pocketmine\command\CommandReader;
use pocketmine\command\CommandSender;
use pocketmine\plugin\ScriptPluginLoader;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\SimpleCommandMap;
use pocketmine\entity\{Entity, Herobrine, ArmorStand, BlazeFireball, Boat, Camera, Car, Chalkboard, Item as DroppedItem, EnderCrystal, EnderPearl, FallingSand, FishingHook, FloatingText, GhastFireball, LeashKnot, Lightning, Minecart, MinecartChest, MinecartCommandBlock, MinecartHopper, MinecartTNT, NPCHuman, Painting, PrimedTNT, ShulkerBullet, XPOrb, Human, Dragon, EnderDragon, Endermite, EvocationFangs, Giant, Guardian, LearnToCodeMascot, PolarBear, Shulker, Slime, SkeletonHorse, Squid, Vindicator, Witch, Wither, WitherSkeleton, ZombieHorse, ElderGuardian, Illusioner, BlueWitherSkull, LavaSlime, FireworkRocket};
use pocketmine\entity\animal\walking\{Axolotl, Chicken, Cow, Donkey, Horse, Llama, Mooshroom, Mule, Ocelot, Pig, Rabbit, Sheep, Villager};
use pocketmine\entity\animal\flying\{Bat, Parrot};
use pocketmine\entity\monster\flying\{Blaze, Ghast, Vex};
use pocketmine\entity\monster\jumping\MagmaCube;
use pocketmine\entity\monster\walking\{CaveSpider, Creeper, Enderman, Husk, IronGolem, PigZombie, Silverfish, Skeleton, SnowGolem, Spider, Stray, Wolf, Zombie, ZombieVillager};
use pocketmine\entity\{Egg, FireBall};
use pocketmine\entity\Arrow;
use pocketmine\entity\Snowball;
use pocketmine\entity\SplashPotion;
use pocketmine\entity\Koni;

use function random_int;
use pocketmine\event\HandlerList;
use pocketmine\event\level\LevelInitEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\Timings;
use pocketmine\event\TimingsHandler;
use pocketmine\inventory\CraftingManager;
use pocketmine\inventory\InventoryType;
use pocketmine\inventory\Recipe;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\inventory\FurnaceRecipe;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\lang\BaseLang;
use pocketmine\level\format\anvil\Anvil;
use pocketmine\level\format\pmanvil\PMAnvil;
use pocketmine\level\format\LevelProviderManager;
use pocketmine\level\format\mcregion\McRegion;
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\metadata\EntityMetadataStore;
use pocketmine\metadata\LevelMetadataStore;
use pocketmine\metadata\PlayerMetadataStore;
use pocketmine\resourcepacks\ResourcePackManager;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\Network;
use pocketmine\network\protocol\BatchPacket;
use pocketmine\network\protocol\CraftingDataPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\PlayerListPacket;
use pocketmine\network\query\QueryHandler;
use pocketmine\network\RakLibInterface;
use pocketmine\network\rcon\RCON;
use pocketmine\network\SourceInterface;
use pocketmine\network\upnp\UPnP;
use pocketmine\permission\BanList;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginLoadOrder;
use pocketmine\plugin\PluginManager;
use pocketmine\scheduler\CallbackTask;
use pocketmine\scheduler\GarbageCollectionTask;
use pocketmine\scheduler\ServerScheduler;
use pocketmine\tile\Bed;
use pocketmine\tile\Cauldron;
use pocketmine\tile\Chest;
use pocketmine\tile\Dispenser;
use pocketmine\tile\Dropper;
use pocketmine\entity\Effect;
use pocketmine\tile\Hopper;
use pocketmine\tile\EnchantTable;
use pocketmine\tile\EnderChest;
use pocketmine\tile\FlowerPot;
use pocketmine\tile\Furnace;
use pocketmine\tile\PistonArm;
use pocketmine\tile\ItemFrame;
use pocketmine\tile\Sign;
use pocketmine\tile\Skull;
use pocketmine\tile\Tile;
use pocketmine\plugin\FolderPluginLoader;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;
use pocketmine\utils\LevelException;
use pocketmine\utils\MainLogger;
use pocketmine\utils\ServerException;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextWrapper;
use pocketmine\utils\Utils;
use pocketmine\utils\UUID;
use pocketmine\utils\VersionString;
use pocketmine\network\protocol\Info;
use pocketmine\level\generator\biome\Biome;
use pocketmine\utils\MetadataConvertor;
use pocketmine\event\server\SendRecipiesList;
use pocketmine\network\protocol\PEPacket;
use pocketmine\tile\Beacon;
use pocketmine\level\generator\ender\Ender;
use pocketmine\level\generator\hell\Nether;
use pocketmine\level\generator\Flat;
use pocketmine\level\generator\normal\Normal;
/**
 * The class that manages everything
 */
class Server{
	const BROADCAST_CHANNEL_ADMINISTRATIVE = "pocketmine.broadcast.admin";
	const BROADCAST_CHANNEL_USERS = "pocketmine.broadcast.user";

	/** @var Server */
	private static $instance = null;

	private static $serverId =  0;

	/** @var BanList */
	private $banByName = null;

    /** @var Tick */
    private $tick = 0.05;

	/** @var BanList */
	private $banByIP = null;

	/** @var Config */
	private $operators = null;

	/** @var Config */
	private $whitelist = null;

	/** @var bool */
	private $isRunning = true;

	private $hasStopped = false;

	/** @var PluginManager */
	private $pluginManager = null;

	/** @var ServerScheduler */
	private $scheduler = null;

	/**
	 * Counts the ticks since the server start
	 *
	 * @var int
	 */

	public $weatherEnabled = false;
	public $weatherRandomDurationMin = 6000;
	public $weatherRandomDurationMax = 12000;

	public $netherEnabled = false;
	public $netherName = "nether";
	public $netherLevel = null;

	public $enderEnabled = true;
	public $enderName = "ender";
	public $enderLevel = null;

	private $tickCounter;
	private $nextTick = 0;
	private $tickAverage = [20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20];
	private $useAverage = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

	/** @var bool */
	private $dispatchSignals = false;

	/** @var \AttachableThreadedLogger */
	private $logger;

	/** @var CommandReader */
	private $console = null;

	/** @var SimpleCommandMap */
	private $commandMap = null;

	/** @var CraftingManager */
	private $craftingManager;

	private $resourceManager;

	/** @var ConsoleCommandSender */
	private $consoleSender;

    /** @var string */
    public $colorTS = "§f";

	/** @var int */
	private $maxPlayers;

	/** @var bool */
	private $autoSave;

	/** @var bool */
	private $savePlayerData;

	/** @var bool */
	private $irongolem;

	/** @var bool */
	private $snowgolem;

	/** @var RCON */
	private $rcon;

	/** @var MemoryManager */
	private $memoryManager;

	/** @var EntityMetadataStore */
	private $entityMetadata;

	/** @var PlayerMetadataStore */
	private $playerMetadata;

	/** @var LevelMetadataStore */
	private $levelMetadata;

	/** @var Network */
	private $network;

	public $networkCompressionLevel = 6;

	private $serverID;

	private $autoloader;
	private $filePath;
	private $dataPath;
	private $pluginPath;

	/** @var QueryHandler */
	private $queryHandler;

	/** @var Config */
	private $properties;

	/** @var Config */
	private $config;

	/** @var Config */
	private $softConfig;

	/** @var Player[] */
	private $players = [];

	/** @var Player[] */
	private $playerList = [];

	private $identifiers = [];

	/** @var Level[] */
	private $levels = [];

	private $autoSaveTicker = 0;
	private $autoSaveTicks = 6000;

	private $baseLang;

	/** @var Level */
	private $levelDefault = null;

	private $jsonCommands = [];
	private $spawnedEntity = [];

	public function addSpawnedEntity($entity) {
		if ($entity instanceof Player) {
			return;
		}
		$this->spawnedEntity[$entity->getId()] = $entity;
	}

	public function removeSpawnedEntity($entity) {
		unset($this->spawnedEntity[$entity->getId()]);
	}

	/**
	 * @return bool
	 */
	public function isRunning(){
		return $this->isRunning === true;
	}

	/**
	 * @return string
	 */
	public function getPocketMineVersion(){
		return \pocketmine\VERSION;
	}

	/**
	 * @return string
	 */
	public function getCodename(){
		return \pocketmine\CODENAME;
	}

	/**
	 * @return string
	 */

	public function getName(){
		return "MySoft";
	}

	/**
	 * @return string
	 */
	public function getVersion(){
		return \pocketmine\MINECRAFT_VERSION;
	}

	/**
	 * @return string
	 */
	public function getApiVersion(){
		return \pocketmine\API_VERSION;
	}

	/**
	 * @return string
	 */
	public function getFilePath(){
		return $this->filePath;
	}

	/**
	 * @return string
	 */
	public function getDataPath(){
		return $this->dataPath;
	}

	/**
	 * @return string
	 */
	public function getPluginPath(){
		return $this->pluginPath;
	}

	/**
	 * @return int
	 */
	public function getMaxPlayers(){
		return $this->maxPlayers;
	}

	/**
	 * @return int
	 */
	public function getPort(){
		return $this->getConfigInt("server-port", 19132);
	}

	/**
	 * @return int
	 */
	public function getViewDistance(){
		return max(2, $this->getConfigInt("view-distance", 8));
	}

	/**
	 * Returns a view distance up to the currently-allowed limit.
	 *
	 * @param int $distance
	 *
	 * @return int
	 */
	public function getAllowedViewDistance($distance){
		return max(2, min($distance, $this->memoryManager->getViewDistance($this->getViewDistance())));
	}

	/**
	 * @return string
	 */
	public function getIp(){
		return $this->getConfigString("server-ip", "0.0.0.0");
	}

	/**
	 * @return string
	 */
	public function getServerName(){
		return $this->getConfigString("motd", "Minecraft: PE Server");
	}

	/**
	 * @return bool
	 */
	public function getAutoSave(){
		return $this->autoSave;
	}

	/**
	 * @param bool $value
	 */
	public function setAutoSave($value){
		$this->autoSave = (bool) $value;
		foreach($this->getLevels() as $level){
			$level->setAutoSave($this->autoSave);
		}
	}
	/**
	 * @return bool
	 */
	public function getSavePlayerData(){
		return $this->savePlayerData;
	}


	/**
	 * @param bool $value
	 */
	public function setSavePlayerData($value) {
		$this->savePlayerData = (bool) $value;
	}

	/**
	 * @return bool
	 */
	public function getIronGolem(){
		return $this->irongolem;
	}


	/**
	 * @param bool $value
	 */
	public function setIronGolem($value) {
		$this->irongolem = (bool) $value;
	}
	public function getSnowGolem(){
		return $this->snowgolem;
	}


	/**
	 * @param bool $value
	 */
	public function setSnowGolem($value) {
		$this->snowgolem = (bool) $value;
	}

	/**
	 * @return string
	 */
	public function getLevelType(){
		return $this->getConfigString("level-type", "DEFAULT");
	}

	/**
	 * @return bool
	 */
	public function getGenerateStructures(){
		return $this->getConfigBoolean("generate-structures", true);
	}

	/**
	 * @return int
	 */
	public function getGamemode(){
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	/**
	 * @return bool
	 */
	public function getForceGamemode(){
		return $this->getConfigBoolean("force-gamemode", false);
	}

	/**
	 * Returns the gamemode text name
	 *
	 * @param int $mode
	 *
	 * @return string
	 */
	public static function getGamemodeString($mode){
		switch((int) $mode){
			case Player::SURVIVAL:
				return "SURVIVAL";
			case Player::CREATIVE:
				return "CREATIVE";
			case Player::ADVENTURE:
				return "ADVENTURE";
			case Player::SPECTATOR:
				return "SPECTATOR";
		}

		return "UNKNOWN";
	}

	/**
	 * Parses a string and returns a gamemode integer, -1 if not found
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function getGamemodeFromString($str){
		switch(strtolower(trim($str))){
			case (string) Player::SURVIVAL:
			case "survival":
			case "s":
				return Player::SURVIVAL;

			case (string) Player::CREATIVE:
			case "creative":
			case "c":
				return Player::CREATIVE;

			case (string) Player::ADVENTURE:
			case "adventure":
			case "a":
				return Player::ADVENTURE;

			case (string) Player::SPECTATOR:
			case "spectator":
			case "view":
			case "v":
				return Player::SPECTATOR;
		}
		return -1;
	}

	/**
	 * @param string $str
	 *
	 * @return int
	 */
	public static function getDifficultyFromString($str){
		switch(strtolower(trim($str))){
			case "0":
			case "peaceful":
			case "p":
				return 0;

			case "1":
			case "easy":
			case "e":
				return 1;

			case "2":
			case "normal":
			case "n":
				return 2;

			case "3":
			case "hard":
			case "h":
				return 3;
		}
		return -1;
	}

	public function findEntity(int $entityId, Level $expectedLevel = \null){
		foreach($this->levels as $level){
			\assert(!$level->isClosed());
			if(($entity = $level->getEntity($entityId)) instanceof Entity){
				return $entity;
			}
		}

		return \null;
	}

	/**
	 * @return int
	 */
	public function getDifficulty(){
		return $this->getConfigInt("difficulty", 1);
	}

	/**
	 * @return bool
	 */
	public function hasWhitelist(){
		return $this->getConfigBoolean("white-list", false);
	}

	/**
	 * @return int
	 */
	public function getSpawnRadius(){
		return $this->getConfigInt("spawn-protection", 16);
	}

	/**
	 * @return bool
	 */
	public function getAllowFlight(){
		return $this->getConfigBoolean("allow-flight", false);
	}

	/**
	 * @return bool
	 */
	public function isHardcore(){
		return $this->getConfigBoolean("hardcore", false);
	}

	/**
	 * @return int
	 */
	public function getDefaultGamemode(){
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	/**
	 * @return string
	 */
	public function getMotd(){
		return $this->getConfigString("motd", "Minecraft: PE Server");
	}

	/**
	 * @return \ClassLoader
	 */
	public function getLoader(){
		return $this->autoloader;
	}

	/**
	 * @return \AttachableThreadedLogger
	 */
	public function getLogger(){
		return $this->logger;
	}

	/**
	 * @return EntityMetadataStore
	 */
	public function getEntityMetadata(){
		return $this->entityMetadata;
	}

	/**
	 * @return PlayerMetadataStore
	 */
	public function getPlayerMetadata(){
		return $this->playerMetadata;
	}

	/**
	 * @return LevelMetadataStore
	 */
	public function getLevelMetadata(){
		return $this->levelMetadata;
	}

	/**
	 * @return PluginManager
	 */
	public function getPluginManager(){
		return $this->pluginManager;
	}

	public function getLanguage(){
		return $this->baseLang;
	}

	/**
	 * @return CraftingManager
	 */
	public function getCraftingManager(){
		return $this->craftingManager;
	}

	/**
	 * @return ResourcePackManager
	 */
	public function getResourceManager(){
		return $this->resourceManager;
	}

	public function getResourcePackManager(){
	    return $this->resourceManager;
    }

	/**
	 * @return ServerScheduler
	 */
	public function getScheduler(){
		return $this->scheduler;
	}

	/**
	 * @return int
	 */
	public function getTick(){
		return $this->tickCounter;
	}

	/**
	 * Returns the last server TPS measure
	 *
	 * @return float
	 */
	public function getTicksPerSecond(){
		return round(array_sum($this->tickAverage) / count($this->tickAverage), 2);
	}

	/**
	 * Returns the TPS usage/load in %
	 *
	 * @return float
	 */
	public function getTickUsage(){
		return round((array_sum($this->useAverage) / count($this->useAverage)) * 100, 2);
	}


	/**
	 * @deprecated
	 *
	 * @param     $address
	 * @param int $timeout
	 */
	public function blockAddress($address, $timeout = 300){
		$this->network->blockAddress($address, $timeout);
	}

	/**
	 * @deprecated
	 *
	 * @param $address
	 * @param $port
	 * @param $payload
	 */
	public function sendPacket($address, $port, $payload){
		$this->network->sendPacket($address, $port, $payload);
	}

	/**
	 * @deprecated
	 *
	 * @return SourceInterface[]
	 */
	public function getInterfaces(){
		return $this->network->getInterfaces();
	}

	/**
	 * @deprecated
	 *
	 * @param SourceInterface $interface
	 */
	public function addInterface(SourceInterface $interface){
		$this->network->registerInterface($interface);
	}

	/**
	 * @deprecated
	 *
	 * @param SourceInterface $interface
	 */
	public function removeInterface(SourceInterface $interface){
		$interface->shutdown();
		$this->network->unregisterInterface($interface);
	}

	/**
	 * @return SimpleCommandMap
	 */
	public function getCommandMap(){
		return $this->commandMap;
	}

	/**
	 * @return Player[]
	 */
	public function getOnlinePlayers(){
		return $this->playerList;
	}

	public function addRecipe(Recipe $recipe){
		$this->craftingManager->registerRecipe($recipe);
	}

	/**
	 * @param string $name
	 *
	 * @return OfflinePlayer|Player
	 */
	public function getOfflinePlayer($name){
		$name = strtolower($name);
		$result = $this->getPlayerExact($name);
		if($result === null) $result = new OfflinePlayer($this, $name);
		return $result;
	}

	/**
	 * @param string $name
	 *
	 * @return Compound
	 */
	public function getOfflinePlayerData($name){
		$name = strtolower($name);
		$path = $this->getDataPath() . "players/";
		if(file_exists($path . "$name.dat")){
			try{
				$nbt = new NBT(NBT::BIG_ENDIAN);
				$nbt->readCompressed(file_get_contents($path . "$name.dat"));

				return $nbt->getData();
			}catch(\Exception $e){ //zlib decode error / corrupt data
				rename($path . "$name.dat", $path . "$name.dat.bak");
				$this->logger->warning("Corrupted data found for \"" . $name . "\", creating new profile");
			}
		}else{
			if($this->getConfigBoolean('save-player-data') == true){
		    	$this->logger->notice("Player data not found for \"" . $name . "\", creating new profile");
		    }
		}
		$spawn = $this->getDefaultLevel()->getSafeSpawn();
		$nbt = new Compound("", [
			new LongTag("firstPlayed", floor(microtime(true) * 1000)),
			new LongTag("lastPlayed", floor(microtime(true) * 1000)),
			new Enum("Pos", [
				new DoubleTag(0, $spawn->x),
				new DoubleTag(1, $spawn->y),
				new DoubleTag(2, $spawn->z)
			]),
			new StringTag("Level", $this->getDefaultLevel()->getName()),
			//new StringTag("SpawnLevel", $this->getDefaultLevel()->getName()),
			//new IntTag("SpawnX", (int) $spawn->x),
			//new IntTag("SpawnY", (int) $spawn->y),
			//new IntTag("SpawnZ", (int) $spawn->z),
			//new ByteTag("SpawnForced", 1), //TODO
			new Enum("Inventory", []),
			new Enum("EnderChestInventory", []),
			new Compound("Achievements", []),
			new IntTag("playerGameType", $this->getGamemode()),
			new Enum("Motion", [
				new DoubleTag(0, 0.0),
				new DoubleTag(1, 0.0),
				new DoubleTag(2, 0.0)
			]),
			new Enum("Rotation", [
				new FloatTag(0, 0.0),
				new FloatTag(1, 0.0)
			]),
			new FloatTag("FallDistance", 0.0),
			new ShortTag("Fire", 0),
			new ShortTag("Air", 300),
			new ByteTag("OnGround", 1),
			new ByteTag("Invulnerable", 0),
			new StringTag("NameTag", $name),
		]);
		$nbt->Pos->setTagType(NBT::TAG_Double);
		$nbt->Inventory->setTagType(NBT::TAG_Compound);
		$nbt->EnderChestInventory->setTagType(NBT::TAG_Compound);
		$nbt->Motion->setTagType(NBT::TAG_Double);
		$nbt->Rotation->setTagType(NBT::TAG_Float);

		$this->saveOfflinePlayerData($name, $nbt);

		return $nbt;

	}

	/**
	 * @param string   $name
	 * @param Compound $nbtTag
 	 * @param bool $async
	 */
	public function saveOfflinePlayerData($name, Compound $nbtTag){
		$nbt = new NBT(NBT::BIG_ENDIAN);
		try{
			$nbt->setData($nbtTag);
			file_put_contents($this->getDataPath() . "players/" . strtolower($name) . ".dat", $nbt->writeCompressed());
		}catch(\Exception $e){
			$this->logger->critical("Could not save player " . $name . ": " . $e->getMessage());
			if(\pocketmine\DEBUG > 1 and $this->logger instanceof MainLogger){
				$this->logger->logException($e);
			}
		}
	}

	/**
	 * @param string $name
	 *
	 * @return Player|null
	 */
	public function getPlayer($name){
		$found = null;
		$name = strtolower($name);
		$delta = PHP_INT_MAX;
		foreach ($this->getOnlinePlayers() as $player) {
			$playerName = strtolower($player->getName());
			if (strpos($playerName, $name) === 0) {
				$curDelta = strlen($playerName) - strlen($name);
				if ($curDelta < $delta) {
					$found = $player;
					$delta = $curDelta;
				}
				if ($curDelta == 0) {
					break;
				}
			}
		}

		return $found;
	}

	/**
	 * @param string $name
	 *
	 * @return Player
	 */
	public function getPlayerExact($name){
		$name = strtolower($name);
		foreach($this->getOnlinePlayers() as $player){
			if(strtolower($player->getName()) === $name){
				return $player;
			}
		}

		return null;
	}

	/**
	 * @param string $partialName
	 *
	 * @return Player[]
	 */
	public function matchPlayer($partialName){
		$partialName = strtolower($partialName);
		$matchedPlayers = [];
		foreach($this->getOnlinePlayers() as $player){
			$playerName = strtolower($player->getName());
			if ($playerName === $partialName) {
				$matchedPlayers = [$player];
				break;
			} else if (strpos($playerName, $partialName) !== false) {
				$matchedPlayers[] = $player;
			}
		}

		return $matchedPlayers;
	}

	/**
	 * @param Player $player
	 */
	public function removePlayer(Player $player){
		if(isset($this->identifiers[$hash = spl_object_hash($player)])){
			$identifier = $this->identifiers[$hash];
			unset($this->players[$identifier]);
			unset($this->identifiers[$hash]);
			return;
		}

		foreach($this->players as $identifier => $p){
			if($player === $p){
				unset($this->players[$identifier]);
				unset($this->identifiers[spl_object_hash($player)]);
				break;
			}
		}
	}

	/**
	 * @return Level[]
	 */
	public function getLevels(){
		return $this->levels;
	}

	/**
	 * @return Level
	 */
	public function getDefaultLevel(){
		return $this->levelDefault;
	}

	/**
	 * Sets the default level to a different level
	 * This won't change the level-name property,
	 * it only affects the server on runtime
	 *
	 * @param Level $level
	 */
	public function setDefaultLevel($level){
		if($level === null or ($this->isLevelLoaded($level->getFolderName()) and $level !== $this->levelDefault)) $this->levelDefault = $level;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isLevelLoaded($name){
		return $this->getLevelByName($name) instanceof Level;
	}

	/**
	 * @param int $levelId
	 *
	 * @return Level
	 */
	public function getLevel($levelId){
		if(isset($this->levels[$levelId])) return $this->levels[$levelId];
		return null;
	}

	/**
	 * @param $name
	 *
	 * @return Level
	 */
	public function getLevelByName($name){
		foreach($this->getLevels() as $level){
			if($level->getFolderName() === $name){
				return $level;
			}
		}

		return null;
	}

	/**
	 * @param Level $level
	 * @param bool  $forceUnload
	 *
	 * @return bool
	 */
	public function unloadLevel(Level $level, $forceUnload = false){
		if($level->unload($forceUnload) === true){
			unset($this->levels[$level->getId()]);
			return true;
		}

		return false;
	}

	/**
	 * Loads a level from the data directory
	 *
	 * @param string $name
	 *
	 * @return bool
	 *
	 * @throws LevelException
	 */
	public function loadLevel($name){
		if(trim($name) === ""){
			throw new LevelException("Invalid empty level name");
		}
		if($this->isLevelLoaded($name)){
			return true;
		}elseif(!$this->isLevelGenerated($name)){
			$this->logger->notice("Level \"" . $name . "\" not found");

			return false;
		}

		$path = $this->getDataPath() . "worlds/" . $name . "/";

		$provider = LevelProviderManager::getProvider($path);

		if($provider === null){
			$this->logger->error("Could not load level \"" . $name . "\": Unknown provider");

			return false;
		}
		//$entities = new Config($path."entities.yml", Config::YAML);
		//if(file_exists($path . "tileEntities.yml")){
		//	@rename($path . "tileEntities.yml", $path . "tiles.yml");
		//}

		try{
			$level = new Level($this, $name, $path, $provider);
		}catch(\Exception $e){

			$this->logger->error("Could not load level \"" . $name . "\": " . $e->getMessage());
			if($this->logger instanceof MainLogger){
				$this->logger->logException($e);
			}
			return false;
		}

		$this->levels[$level->getId()] = $level;

		$level->initLevel();

		$this->getPluginManager()->callEvent(new LevelLoadEvent($level));
		return true;
	}

	/**
	 * Generates a new level if it does not exists
	 *
	 * @param string $name
	 * @param int    $seed
	 * @param array  $options
	 *
	 * @return bool
	 */
	public function generateLevel($name, $seed = null, $generator = null, $options = []){
		if(trim($name) === "" or $this->isLevelGenerated($name)){
			return false;
		}

		$seed = $seed ?? random_int(INT32_MIN, INT32_MAX);

		if(!($generator !== null and class_exists($generator, true) and is_subclass_of($generator, Generator::class))){
			$generator = Generator::getGenerator("DEFAULT");
		}

		if(($provider = LevelProviderManager::getProviderByName($providerName = $this->getProperty("level-settings.default-format", "mcregion"))) === null){
			$provider = LevelProviderManager::getProviderByName($providerName = "mcregion");
		}

		try{
			$path = $this->getDataPath() . "worlds/" . $name . "/";
			/** @var \pocketmine\level\format\LevelProvider $provider */
			$provider::generate($path, $name, $seed, $generator, $options);

			$level = new Level($this, $name, $path, $provider);
			$this->levels[$level->getId()] = $level;

			$level->initLevel();
		}catch(\Exception $e){
			$this->logger->error("Could not generate level \"" . $name . "\": " . $e->getMessage());
			if($this->logger instanceof MainLogger){
				$this->logger->logException($e);
			}
			return false;
		}

		$this->getPluginManager()->callEvent(new LevelInitEvent($level));

		$this->getPluginManager()->callEvent(new LevelLoadEvent($level));

		$centerX = $level->getSpawnLocation()->getX() >> 4;
		$centerZ = $level->getSpawnLocation()->getZ() >> 4;

		$order = [];

		for($X = -3; $X <= 3; ++$X){
			for($Z = -3; $Z <= 3; ++$Z){
				$distance = $X ** 2 + $Z ** 2;
				$chunkX = $X + $centerX;
				$chunkZ = $Z + $centerZ;
				$index = Level::chunkHash($chunkX, $chunkZ);
				$order[$index] = $distance;
			}
		}

		asort($order);

		foreach($order as $index => $distance){
			Level::getXZ($index, $chunkX, $chunkZ);
			$level->generateChunk($chunkX, $chunkZ, true);
		}

		return true;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isLevelGenerated($name){
		if(trim($name) === ""){
			return false;
		}
		$path = $this->getDataPath() . "worlds/" . $name . "/";
		if(!($this->getLevelByName($name) instanceof Level)){

			if(LevelProviderManager::getProvider($path) === null){
				return false;
			}
			/*if(file_exists($path)){
				$level = new LevelImport($path);
				if($level->import() === false){ //Try importing a world
					return false;
				}
			}else{
				return false;
			}*/
		}

		return true;
	}

	public function getGeniApiVersion(){
		return \pocketmine\API_VERSION;
	}

	/**
	 * @param string $variable
	 * @param string $defaultValue
	 *
	 * @return string
	 */

	public function getConfigString($variable, $defaultValue = ""){
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (string) $v[$variable];
		}

		return $this->properties->exists($variable) ? $this->properties->get($variable) : $defaultValue;
	}

	/**
	 * @param string $variable
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public function getAdvancedProperty($variable, $defaultValue = null){
			$vars = explode(".", $variable);
			$base = array_shift($vars);
			if($this->softConfig->exists($base)){
					$base = $this->softConfig->get($base);
				}else{
					return $defaultValue;
		}

		while(count($vars) > 0){
					$baseKey = array_shift($vars);
					if(is_array($base) and isset($base[$baseKey])){
							$base = $base[$baseKey];
						}else{
							return $defaultValue;
			}
		}

		return $base;
	}


	/**
	 * @param string $variable
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public function getProperty($variable, $defaultValue = null){
		$value = $this->config->getNested($variable);

		return $value === null ? $defaultValue : $value;
	}

	/**
	 * @param string $variable
	 * @param string $value
	 */
	public function setConfigString($variable, $value){
		$this->properties->set($variable, $value);
	}

	/**
	 * @param string $variable
	 * @param int    $defaultValue
	 *
	 * @return int
	 */
	public function getConfigInt($variable, $defaultValue = 0){
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (int) $v[$variable];
		}

		return $this->properties->exists($variable) ? (int) $this->properties->get($variable) : (int) $defaultValue;
	}

	/**
	 * @param string $variable
	 * @param int    $value
	 */
	public function setConfigInt($variable, $value){
		$this->properties->set($variable, (int) $value);
	}

	/**
	 * @param string  $variable
	 * @param boolean $defaultValue
	 *
	 * @return boolean
	 */
	public function getConfigBoolean($variable, $defaultValue = false){
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			$value = $v[$variable];
		}else{
			$value = $this->properties->exists($variable) ? $this->properties->get($variable) : $defaultValue;
		}

		if(is_bool($value)){
			return $value;
		}
		switch(strtolower($value)){
			case "on":
			case "true":
			case "1":
			case "yes":
				return true;
		}

		return false;
	}

	/**
	 * @param string $variable
	 * @param bool   $value
	 */
	public function setConfigBool($variable, $value){
		$this->properties->set($variable, $value == true ? "1" : "0");
	}

	/**
	 * @param string $name
	 *
	 * @return PluginIdentifiableCommand
	 */
	public function getPluginCommand($name){
		if(($command = $this->commandMap->getCommand($name)) instanceof PluginIdentifiableCommand){
			return $command;
		}else{
			return null;
		}
	}

	/**
	 * @return BanList
	 */
	public function getNameBans(){
		return $this->banByName;
	}

	/**
	 * @return BanList
	 */
	public function getIPBans(){
		return $this->banByIP;
	}

	/**
	 * @param string $name
	 */
	public function addOp($name){
		$this->operators->set(strtolower($name), true);

		if(($player = $this->getPlayerExact($name)) instanceof Player) $player->recalculatePermissions();
		$this->operators->save();
	}

	/**
	 * @param string $name
	 */
	public function removeOp($name){
		$this->operators->remove(strtolower($name));

		if(($player = $this->getPlayerExact($name)) instanceof Player) $player->recalculatePermissions();
		$this->operators->save();
	}

	/**
	 * @param string $name
	 */
	public function addWhitelist($name){
		$this->whitelist->set(strtolower($name), true);
		$this->whitelist->save();
	}

	/**
	 * @param string $name
	 */
	public function removeWhitelist($name){
		$this->whitelist->remove(strtolower($name));
		$this->whitelist->save();
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isWhitelisted($name){
		return !$this->hasWhitelist() or $this->operators->exists($name, true) or $this->whitelist->exists($name, true);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */

	public function isOp($name){
		return $this->operators->exists($name, true);
	}

	/**
	 * @return Config
	 */
	public function getWhitelisted(){
		return $this->whitelist;
	}

	/**
	 * @return Config
	 */
	public function getOps(){
		return $this->operators;
	}

	public function reloadWhitelist(){
		$this->whitelist->reload();
	}

	/**
	 * @return string[]
	 */
	public function getCommandAliases(){
		$section = $this->getProperty("aliases");
		$result = [];
		if(is_array($section)){
			foreach($section as $key => $value){
				$commands = [];
				if(is_array($value)){
					$commands = $value;
				}else{
					$commands[] = $value;
				}

				$result[$key] = $commands;
			}
		}

		return $result;
	}

	/**
	 * @return Server
	 */
	public static function getInstance(){
		return self::$instance;
	}

	public static function getServerId(){
		return self::$serverId;
	}

	public function commandsToArray(){
		$commandList = [];
		$cml = [];
		foreach ($this->getCommandMap()->getAllCommands() as $command) {
			$commandList = [$command => true];
			$cml = array_merge($cml, $commandList);
		}
		return $cml;
	}

	public function commandsCheck(){
		foreach ($this->allCommands->getAll(true) as $command) {
			if($this->allCommands->getC($command) === false){
				if($this->getCommandMap()->getCommand($command) != null){
					$this->getCommandMap()->killCommand($this->getCommandMap()->getCommand($command), "pocketmine", $this->getCommandMap()->getCommand($command)->getName());
				}
			}
		}
	}

	/**
	 * @param \ClassLoader    $autoloader
	 * @param \ThreadedLogger $logger
	 * @param string          $filePath
	 * @param string          $dataPath
	 * @param string          $pluginPath
	 */
	public function __construct(\ClassLoader $autoloader, \ThreadedLogger $logger, $filePath, $dataPath, $pluginPath){
		self::$instance = $this;
		self::$serverId =  mt_rand(0, PHP_INT_MAX);

		$this->autoloader = $autoloader;
		$this->logger = $logger;
		$this->filePath = $filePath;
		if(!file_exists($dataPath . "worlds/")){
			mkdir($dataPath . "worlds/", 0777);
		}

		if(!file_exists($dataPath . "players/")){
			mkdir($dataPath . "players/", 0777);
		}

		if(!file_exists($pluginPath)){
			mkdir($pluginPath, 0777);
		}

		$this->dataPath = realpath($dataPath) . DIRECTORY_SEPARATOR;
		$this->pluginPath = realpath($pluginPath) . DIRECTORY_SEPARATOR;

		$this->console = new CommandReader();

		$version = new VersionString($this->getPocketMineVersion());
		$this->about();

		$this->logger->info("Загрузка pocketmine-soft.yml...");
		if(!file_exists($this->dataPath . "pocketmine-soft.yml")){
			$content = file_get_contents($this->filePath . "src/pocketmine/resources/pocketmine-soft.yml");
			@file_put_contents($this->dataPath . "pocketmine-soft.yml", $content);
		}
		$this->softConfig = new Config($this->dataPath . "pocketmine-soft.yml", Config::YAML, []);

		$this->logger->info("Загрузка pocketmine.yml...");
		if(!file_exists($this->dataPath . "pocketmine.yml")){
			$content = file_get_contents($this->filePath . "src/pocketmine/resources/pocketmine.yml");
			@file_put_contents($this->dataPath . "pocketmine.yml", $content);
		}
		$this->config = new Config($this->dataPath . "pocketmine.yml", Config::YAML, []);

		$this->commandMap = new SimpleCommandMap($this);

		$this->logger->info("Загрузка commands-list.yml...");
		$this->allCommands = new Config($this->dataPath . "commands-list.yml", Config::YAML, $this->commandsToArray());
		$this->commandsCheck();

		$this->logger->info("Загрузка server.properties...");
		$this->properties = new Config($this->dataPath . "server.properties", Config::PROPERTIES, [
			"motd" => "Minecraft: PE Server",
			"server-port" => 19132,
			"memory-limit" => "256M",
			"white-list" => false,
			"spawn-protection" => 16,
			"max-players" => 20,
			"allow-flight" => false,
			"gamemode" => 0,
			"force-gamemode" => false,
			"hardcore" => false,
			"pvp" => true,
			"difficulty" => 1,
			"generator-settings" => "",
			"level-name" => "world",
			"level-seed" => "",
			"level-type" => "DEFAULT",
			"enable-query" => false,
			"enable-rcon" => false,
			"rcon.password" => substr(base64_encode(random_bytes(20)), 3, 10),
			"auto-save" => true,
			"save-player-data" => true,
			"time-update" => true,
			"view-distance" => 8,
			"iron-golem" => false,
			"snow-golem" => false
		]);

		ServerScheduler::$WORKERS = 4;

		$this->baseLang = new BaseLang($this->getProperty("settings.language", BaseLang::FALLBACK_LANGUAGE));

		$this->scheduler = new ServerScheduler();

		if($this->getConfigBoolean("enable-rcon", false) === true){
			$this->rcon = new RCON($this, $this->getConfigString("rcon.password", ""), $this->getConfigInt("rcon.port", $this->getPort()), ($ip = $this->getIp()) != "" ? $ip : "0.0.0.0", $this->getConfigInt("rcon.threads", 1), $this->getConfigInt("rcon.clients-per-thread", 50));
		}

		$this->entityMetadata = new EntityMetadataStore();
		$this->playerMetadata = new PlayerMetadataStore();
		$this->levelMetadata = new LevelMetadataStore();

		$this->memoryManager = new MemoryManager($this);

		$this->operators = new Config($this->dataPath . "ops.txt", Config::ENUM);
		$this->bans = new \SQLite3($this->dataPath .'banlist.db');
		$this->bans->query("CREATE TABLE IF NOT EXISTS bans(name TEXT NOT NULL, due TEXT NOT NULL, bannedby TEXT NOT NULL, reason TEXT NOT NULL);");
		$this->bansip = new \SQLite3($this->dataPath .'baniplist.db');
		$this->bansip->query("CREATE TABLE IF NOT EXISTS bansip(name TEXT NOT NULL, ip TEXT NOT NULL, due TEXT NOT NULL, bannedby TEXT NOT NULL, reason TEXT NOT NULL);");
		$this->banscid = new \SQLite3($this->dataPath .'bancidlist.db');
		$this->banscid->query("CREATE TABLE IF NOT EXISTS banscid(name TEXT NOT NULL, cid TEXT NOT NULL, due TEXT NOT NULL, bannedby TEXT NOT NULL, reason TEXT NOT NULL);");
		$this->whitelist = new Config($this->dataPath . "white-list.txt", Config::ENUM);


		$this->maxPlayers = $this->getConfigInt("max-players", 20);
		$this->setAutoSave($this->getConfigBoolean("auto-save", true));
		$this->setSavePlayerData($this->getConfigBoolean("save-player-data", true));

		$this->setIronGolem($this->getConfigBoolean("iron-golem", false));
		$this->setSnowGolem($this->getConfigBoolean("snow-golem", false));
		$this->getLogger()->setWrite(!$this->getConfigBoolean("server.disable-log", false));
		if(($memory = str_replace("B", "", strtoupper($this->getConfigString("memory-limit", "256M")))) !== false){
			$value = ["M" => 1, "G" => 1024];
			$real = ((int) substr($memory, 0, -1)) * $value[substr($memory, -1)];
			if($real < 128){
				$this->logger->warning($this->getCodename() . " может не работать, если выделено памяти серверу меньше 128МБ RAM");
			}
			@ini_set("memory_limit", $memory);
		}else{
			$this->setConfigString("memory-limit", "256M");
		}
		$this->network = new Network($this);
		$this->network->setName($this->getMotd());

		if($this->getConfigBoolean("hardcore", false) === true and $this->getDifficulty() < 3){
			$this->setConfigInt("difficulty", 3);
		}

		define("pocketmine\\DEBUG", (int) $this->getProperty("debug.level", 1));
		if($this->logger instanceof MainLogger){
			$this->logger->setLogDebug(\pocketmine\DEBUG > 1);
		}

		Level::$COMPRESSION_LEVEL = $this->getProperty("chunk-sending.compression-level", 6);

		if(defined("pocketmine\\DEBUG") and \pocketmine\DEBUG >= 0){
			@\cli_set_process_title($this->getCodename() . " " . $this->getPocketMineVersion());
		}

		$this->logger->info("Сервер MCPE стартовал на IP-Адресе: " . ($this->getIp() === "" ? "*" : $this->getIp()) . ":" . $this->getPort());
		$this->serverID = Utils::getMachineUniqueId($this->getIp() . $this->getPort());

		$this->addInterface($this->mainInterface = new RakLibInterface($this));
		$this->logger->info("Этот сервер запущен на ядре: " . $this->getCodename() . " Версии: " . $this->getPocketMineVersion() . " (API: " . $this->getApiVersion() . ")");
		$this->logger->info($this->getCodename() . " не распространяется под лицензиями, передавать ядро кому либо запрещено.");

		Timings::init();
		TimingsHandler::setEnabled((bool) $this->getProperty("settings.enable-profiling", false));

		$this->consoleSender = new ConsoleCommandSender();

		$this->registerEntities();
		$this->registerTiles();

		InventoryType::init();
		Block::init();
		Enchantment::init();
		Item::init();
		Biome::init();
		Color::init();
		TextWrapper::init();
		MetadataConvertor::init();

		$this->craftingManager = new CraftingManager();

		$this->resourceManager = new ResourcePackManager($this, $this->getDataPath() . "resource_packs" . DIRECTORY_SEPARATOR);

		Generator::addGenerator(Flat::class, "flat");
		Generator::addGenerator(Normal::class, "normal");
		Generator::addGenerator(Normal::class, "default");
		Generator::addGenerator(Nether::class, "hell");
		Generator::addGenerator(Nether::class, "nether");
		Generator::addGenerator(Ender::class, "ender");
		PEPacket::initPallet();

		$this->pluginManager = new PluginManager($this, $this->commandMap);
		$this->pluginManager->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this->consoleSender);
		$this->pluginManager->setUseTimings($this->getProperty("settings.enable-profiling", false));
		$this->pluginManager->registerInterface(PharPluginLoader::class);
		$this->pluginManager->registerInterface(FolderPluginLoader::class);
		$this->pluginManager->registerInterface(ScriptPluginLoader::class);

		\set_exception_handler([$this, "exceptionHandler"]);
		register_shutdown_function([$this, "crashDump"]);

		$plugins = $this->pluginManager->loadPlugins($this->pluginPath);

		$configPlugins = $this->getAdvancedProperty("plugins", []);
		if(count($configPlugins) > 0){
			$this->getLogger()->info("Checking extra plugins");
			$loadNew = false;
			foreach($configPlugins as $plugin => $download){
				if(!isset($plugins[$plugin])){
					$path = $this->pluginPath . "/". $plugin . ".phar";
					if(substr($download, 0, 4) === "http"){
						$this->getLogger()->info("Downloading ". $plugin);
						file_put_contents($path, Utils::getURL($download));
					}else{
						file_put_contents($path, file_get_contents($download));
					}
					$loadNew = true;
				}
			}

			if($loadNew){
				$this->pluginManager->loadPlugins($this->pluginPath);
			}
		}

		$this->enablePlugins(PluginLoadOrder::STARTUP);

		LevelProviderManager::addProvider($this, Anvil::class);
		LevelProviderManager::addProvider($this, PMAnvil::class);
		LevelProviderManager::addProvider($this, McRegion::class);

		foreach((array) $this->getProperty("worlds", []) as $name => $options){
			if($options === null){
				$options = [];
			}elseif(!is_array($options)){
				continue;
			}
			if(!$this->loadLevel($name)){
				$seed = $options["seed"] ?? time();
				if(is_string($seed) and !is_numeric($seed)){
					$seed = Utils::javaStringHash($seed);
				}elseif(!is_int($seed)){
					$seed = (int) $seed;
				}

				$options = explode(":", $this->getProperty("worlds.$name.generator", Generator::getGenerator("default")));
				$generator = Generator::getGenerator(array_shift($options));
				if(count($options) > 0){
					$options = [
						"preset" => implode(":", $options),
					];
				}else{
					$options = [];
				}

				$this->generateLevel($name, $seed, $generator, $options);
			}
		}

		if($this->getDefaultLevel() === null){
			$default = $this->getConfigString("level-name", "world");
			if(trim($default) == ""){
				$this->getLogger()->warning("level-name cannot be null, using default");
				$default = "world";
				$this->setConfigString("level-name", "world");
			}
			if($this->loadLevel($default) === false){
				$seed = $this->getConfigInt("level-seed", time());
				$this->generateLevel($default, $seed === 0 ? time() : $seed);
			}

			$this->setDefaultLevel($this->getLevelByName($default));
		}

		$this->properties->save();

		if(!($this->getDefaultLevel() instanceof Level)){
			$this->getLogger()->emergency("No default level has been loaded");
			$this->forceShutdown();

			return;
		}

		$this->weatherEnabled = $this->getAdvancedProperty("level.weather", false);
		$this->netherEnabled = $this->getAdvancedProperty("nether.allow-nether", false);
		$this->netherName = $this->getAdvancedProperty("nether.level-name", "nether");
		$this->enderEnabled = $this->getAdvancedProperty("ender.allow-ender", false);
		$this->enderName = $this->getAdvancedProperty("ender.level-name", "ender");

		if($this->netherEnabled){
			if(!$this->loadLevel($this->netherName)){
				$this->generateLevel($this->netherName, time(), Generator::getGenerator("nether"));
			}
			$this->netherLevel = $this->getLevelByName($this->netherName);
		}

		if($this->enderEnabled){
			if(!$this->loadLevel($this->enderName)){
				$this->generateLevel($this->enderName, time(), Generator::getGenerator("ender"));
			}
			$this->enderLevel = $this->getLevelByName($this->enderName);
		}

		if($this->getProperty("ticks-per.autosave", 6000) > 0){
			$this->autoSaveTicks = (int) $this->getProperty("ticks-per.autosave", 6000);
		}

		$this->enablePlugins(PluginLoadOrder::POSTWORLD);

		$this->start();
	}

	public function getMainInterface() {
		return $this->mainInterface;
	}

	/**
	 * @param string        $message
	 * @param Player[]|null $recipients
	 *
	 * @return int
	 */
	public function broadcastMessage($message, $recipients = null){
		if(!is_array($recipients)){
			return $this->broadcast($message, self::BROADCAST_CHANNEL_USERS);
		}

		/** @var Player[] $recipients */
		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * @param string        $tip
	 * @param Player[]|null $recipients
	 *
	 * @return int
	 */
	public function broadcastTip($tip, $recipients = null){
		if(!is_array($recipients)){
			/** @var Player[] $recipients */
			$recipients = [];

			foreach($this->pluginManager->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible){
				if($permissible instanceof Player and $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)){
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		/** @var Player[] $recipients */
		foreach($recipients as $recipient){
			$recipient->sendTip($tip);
		}

		return count($recipients);
	}

	/**
	 * @param string        $popup
	 * @param Player[]|null $recipients
	 *
	 * @return int
	 */
	public function broadcastPopup($popup, $recipients = null){
		if(!is_array($recipients)){
			/** @var Player[] $recipients */
			$recipients = [];

			foreach($this->pluginManager->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible){
				if($permissible instanceof Player and $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)){
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		/** @var Player[] $recipients */
		foreach($recipients as $recipient){
			$recipient->sendPopup($popup);
		}

		return count($recipients);
	}

	/**
	 * @param string $message
	 * @param string $permissions
	 *
	 * @return int
	 */
	public function broadcast($message, $permissions){
		/** @var CommandSender[] $recipients */
		$recipients = [];
		foreach(explode(";", $permissions) as $permission){
			foreach($this->pluginManager->getPermissionSubscriptions($permission) as $permissible){
				if($permissible instanceof CommandSender and $permissible->hasPermission($permission)){
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * Broadcasts a Minecraft packet to a list of players
	 *
	 * @param Player[]   $players
	 * @param DataPacket $packet
	 */

	public static function broadcastPacket(array $players, DataPacket $packet) {
		foreach($players as $player){
			$player->dataPacket($packet);
		}
		if(isset($packet->__encapsulatedPacket)){
			unset($packet->__encapsulatedPacket);
		}
	}

	public function about(){
	    $string = "

  __  __       ____         __ _
 |  \/  |_   _/ ___|  ___  / _| |_
 | |\/| | | | \___ \ / _ \| |_| __|
 | |  | | |_| |___) | (_) |  _| |_
 |_|  |_|\__, |____/ \___/|_|  \__|
         |___/

§fИспользуется ядро §b" . $this->getName() . " §fверсии: §6" . $this->getPocketMineVersion() . "
§fMCPE версия: " . $this->getVersion() . "
§fИспользуется php: §e" . PHP_VERSION . "
§fOS: §6" . PHP_OS ."
           ";
	   $this->getLogger()->info($string);
	}

	/**
	 * Broadcasts a list of packets in a batch to a list of players
	 *
	 * @param Player[]            $players
	 * @param DataPacket[]|string $packets
	 */
	public function batchPackets(array $players, array $packets){
		Timings::$playerNetworkTimer->startTiming();

		$playersCount = count($players);

		foreach ($packets as $pk) {
			if ($playersCount < 2) {
				foreach ($players as $p) {
					$pk->setDeviceId($p->getDeviceOS());
					$p->dataPacket($pk);
				}
			} else {
				Server::broadcastPacket($players, $pk);
			}
		}

		Timings::$playerNetworkTimer->stopTiming();
	}

	/**
	 * @param int $type
	 */
	public function enablePlugins($type){
		foreach($this->pluginManager->getPlugins() as $plugin){
			if(!$plugin->isEnabled() and $plugin->getDescription()->getOrder() === $type){
				$this->enablePlugin($plugin);
			}
		}

		if($type === PluginLoadOrder::POSTWORLD){
			$this->commandMap->registerServerAliases();
			DefaultPermissions::registerCorePermissions();
		}
	}

	/**
	 * @param Plugin $plugin
	 */
	public function enablePlugin(Plugin $plugin){
		$this->pluginManager->enablePlugin($plugin);
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @deprecated
	 */
	public function loadPlugin(Plugin $plugin){
		$this->enablePlugin($plugin);
	}

	public function disablePlugins(){
		$this->pluginManager->disablePlugins();
	}

	public function checkConsole(){
		Timings::$serverCommandTimer->startTiming();
		if(($line = $this->console->getLine()) !== null){
			$this->pluginManager->callEvent($ev = new ServerCommandEvent($this->consoleSender, $line));
			if(!$ev->isCancelled()){
				$this->dispatchCommand($ev->getSender(), $ev->getCommand());
			}
		}
		Timings::$serverCommandTimer->stopTiming();
	}

	/**
	 * Executes a command from a CommandSender
	 *
	 * @param CommandSender $sender
	 * @param string        $commandLine
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function dispatchCommand(CommandSender $sender, $commandLine){
		if(!($sender instanceof CommandSender)){
			throw new ServerException("CommandSender is not valid");
		}

		if($this->commandMap->dispatch($sender, $commandLine)){
			return true;
		}

		if($sender instanceof Player){
			$message = $this->getAdvancedProperty("messages.unknown-command", "Неизвестная команда. Введите команду /help, чтобы узнать все команды.");
			if(is_string($message) and strlen($message) > 0){
				$sender->sendMessage(TextFormat::RED.$message);
			}
		}else{
			$sender->sendMessage("Неизвестная команда. Введите команду /help, чтобы узнать все команды.");
		}

		return false;
	}

	public function reload(){
		$this->logger->info("Saving levels...");

		foreach($this->levels as $level){
			$level->save();
		}

		$this->pluginManager->disablePlugins();
		$this->pluginManager->clearPlugins();
		$this->commandMap->clearCommands();

		$this->logger->info("Reloading properties...");
		$this->properties->reload();
		$this->maxPlayers = $this->getConfigInt("max-players", 20);

		if(($memory = str_replace("B", "", strtoupper($this->getConfigString("memory-limit", "256M")))) !== false){
			$value = ["M" => 1, "G" => 1024];
			$real = ((int) substr($memory, 0, -1)) * $value[substr($memory, -1)];
			if($real < 128){
				$this->logger->warning($this->getCodename() . " может не работать, если выделено памяти серверу меньше 128МБ RAM");
			}
			@ini_set("memory_limit", $memory);
		}else{
			$this->setConfigString("memory-limit", "256M");
		}

		if($this->getConfigBoolean("hardcore", false) === true and $this->getDifficulty() < 3){
			$this->setConfigInt("difficulty", 3);
		}

		$this->reloadWhitelist();
		$this->operators->reload();

		$this->memoryManager->doObjectCleanup();

		/*foreach($this->getIPBans()->getEntries() as $entry){
			$this->blockAddress($entry->getName(), -1);
		}*/

		$this->pluginManager->registerInterface(PharPluginLoader::class);
		$this->pluginManager->registerInterface(FolderPluginLoader::class);
		$this->pluginManager->registerInterface(ScriptPluginLoader::class);
		$this->pluginManager->loadPlugins($this->pluginPath);
		$this->enablePlugins(PluginLoadOrder::STARTUP);
		$this->enablePlugins(PluginLoadOrder::POSTWORLD);
		TimingsHandler::reload();
	}

	/**
	 * Shutdowns the server correctly
	 */
	 public function shutdown(bool $restart = false, string $msg = ""){
 		$this->isRunning = false;
 		if($msg != ""){
 			$this->propertyCache["settings.shutdown-message"] = $msg;
 		}
 	}

	public function forceShutdown(){
		if($this->hasStopped){
			return;
		}

		if($this->doTitleTick){
			echo "\x1b]0;\x07";
		}

		try{

			$this->hasStopped = true;

			$this->shutdown();
			if($this->rcon instanceof RCON){
				$this->rcon->stop();
			}

			if($this->getProperty("network.upnp-forwarding", false)){
				$this->logger->info("[UPnP] Removing port forward...");
				UPnP::RemovePortForward($this->getPort());
			}

			if($this->pluginManager instanceof PluginManager){
				$this->getLogger()->debug("Disabling all plugins");
				$this->pluginManager->disablePlugins();
			}

			foreach($this->players as $player){
				$player->close($player->getLeaveMessage(), $this->getProperty("settings.shutdown-message", "Server closed"));
			}

			$this->getLogger()->debug("Unloading all worlds");
			foreach($this->getLevels() as $level){
				$this->unloadLevel($level, true);
			}

			$this->getLogger()->debug("Removing event handlers");
			HandlerList::unregisterAll();

			$this->scheduler->cancelAllTasks();
			$this->scheduler->mainThreadHeartbeat(PHP_INT_MAX);

			$this->properties->save();

			if($this->console instanceof CommandReader){
				$this->getLogger()->debug("Closing console");
				$this->console->shutdown();
				$this->console->notify();
			}

			if($this->network instanceof Network){
				$this->getLogger()->debug("Stopping network interfaces");
				foreach($this->network->getInterfaces() as $interface){
					$this->getLogger()->debug("Stopping network interface " . get_class($interface));
					$interface->shutdown();
					$this->network->unregisterInterface($interface);
				}
			}
		}catch(\Throwable $e){
			$this->logger->logException($e);
			$this->logger->emergency("Crashed while crashing, killing process");
			@Utils::kill(getmypid());
		}

	}

	/*public function forceShutdown(){
		if($this->hasStopped){
			return;
		}

		try{
			$this->hasStopped = true;

			foreach($this->players as $player){
				$player->close(TextFormat::YELLOW . $player->getName() . " has left the game", $this->getProperty("settings.shutdown-message", "Server closed"));
			}

			foreach($this->network->getInterfaces() as $interface){
				$interface->shutdown();
				$this->network->unregisterInterface($interface);
			}

			$this->shutdown();
			if($this->rcon instanceof RCON){
				$this->rcon->stop();
			}

			$this->pluginManager->disablePlugins();

			foreach($this->getLevels() as $level){
				$this->unloadLevel($level, true);
			}

			HandlerList::unregisterAll();

			$this->scheduler->cancelAllTasks();
			$this->scheduler->mainThreadHeartbeat(PHP_INT_MAX);

			$this->properties->save();

			$this->console->shutdown();
			$this->console->notify();
		}catch(\Exception $e){
			$this->logger->emergency("Crashed while crashing, killing process");
			@kill(getmypid());
		}

	}*/

	/**
	 * Starts the PocketMine-MP server and starts processing ticks and packets
	 */
	public function start(){
		DataPacket::initPackets();
		$jsonCommands = @json_decode(@file_get_contents(__DIR__ . "/command/commands.json"), true);
		if ($jsonCommands) {
			$this->jsonCommands = $jsonCommands;
		}

			$this->queryHandler = new QueryHandler();


		/*foreach($this->getIPBans()->getEntries() as $entry){
			$this->network->blockAddress($entry->getName(), -1);

		}*/


		$this->tickCounter = 0;

		if(function_exists("pcntl_signal")){
			pcntl_signal(SIGTERM, [$this, "handleSignal"]);
			pcntl_signal(SIGINT, [$this, "handleSignal"]);
			pcntl_signal(SIGHUP, [$this, "handleSignal"]);
            $this->dispatchSignals = true;
		}

		$this->logger->info("Default game type: " . self::getGamemodeString($this->getGamemode()));

		Effect::init();

		$this->logger->info("Загрузился (" . round(microtime(true) - \pocketmine\START_TIME, 3) . ")!");

		$this->tickAverage = [];
		$this->useAverage = [];
		for($i = 0; $i < 1200; $i++) {
			$this->tickAverage[] = 20;
			$this->useAverage[] = 0;
		}

		$this->tickProcessor();
		$this->forceShutdown();
	}

	public function handleSignal($signo){
		if($signo === SIGTERM or $signo === SIGINT or $signo === SIGHUP){
			$this->shutdown();
		}
	}

	public function exceptionHandler(\Throwable $e, $trace = null){

		global $lastError;

		if($trace === null){
			$trace = $e->getTrace();
		}

		$errstr = $e->getMessage();
		$errfile = $e->getFile();
		$errno = $e->getCode();
		$errline = $e->getLine();

		$type = ($errno === E_ERROR or $errno === E_USER_ERROR) ? \LogLevel::ERROR : (($errno === E_USER_WARNING or $errno === E_WARNING) ? \LogLevel::WARNING : \LogLevel::NOTICE);
		if(($pos = strpos($errstr, "\n")) !== false){
			$errstr = substr($errstr, 0, $pos);
		}

		$errfile = cleanPath($errfile);

		if($this->logger instanceof MainLogger){
			$this->logger->logException($e, $trace);
		}

		$lastError = [
			"type" => $type,
			"message" => $errstr,
			"fullFile" => $e->getFile(),
			"file" => $errfile,
			"line" => $errline,
			"trace" => @getTrace(1, $trace)
		];

		global $lastExceptionError, $lastError;
		$lastExceptionError = $lastError;
		$this->crashDump();
	}

	public function crashDump(){
		if($this->isRunning === false){
			return;
		}
		$this->isRunning = false;
		$this->hasStopped = false;

		ini_set("error_reporting", 0);
		ini_set("memory_limit", -1); //Fix error dump not dumped on memory problems
		$this->logger->emergency("An unrecoverable error has occurred and the server has crashed. Creating a crash dump");
		try{
			$dump = new CrashDump($this);
		}catch(\Exception $e){
			$this->logger->critical("Could not create Crash Dump: " . $e->getMessage());
			return;
		}

		$this->logger->emergency("Please submit the \"" . $dump->getPath() . "\" file to the Bug Reporting page. Give as much info as you can.");


		if($this->getProperty("auto-report.enabled", true) !== false){
			$report = true;
			$plugin = $dump->getData()["plugin"];
			if(is_string($plugin)){
				$p = $this->pluginManager->getPlugin($plugin);
				if($p instanceof Plugin and !($p->getPluginLoader() instanceof PharPluginLoader)){
					$report = false;
				}
			}elseif(\Phar::running(true) == ""){
				$report = false;
			}
			if($dump->getData()["error"]["type"] === "E_PARSE" or $dump->getData()["error"]["type"] === "E_COMPILE_ERROR"){
				$report = false;
			}

			if($report){
				$reply = Utils::postURL("http://" . $this->getProperty("auto-report.host", "crash.pocketmine.net") . "/submit/api", [
					"report" => "yes",
					"name" => $this->getCodename() . " " . $this->getPocketMineVersion(),
					"email" => "crash@pocketmine.net",
					"reportPaste" => base64_encode($dump->getEncodedData())
				]);

				if(($data = json_decode($reply)) !== false and isset($data->crashId)){
					$reportId = $data->crashId;
					$reportUrl = $data->crashUrl;
					$this->logger->emergency("The crash dump has been automatically submitted to the Crash Archive. You can view it on $reportUrl or use the ID #$reportId.");
				}
			}
		}

		//$this->checkMemory();
		//$dump .= "Memory Usage Tracking: \r\n" . chunk_split(base64_encode(gzdeflate(implode(";", $this->memoryStats), 9))) . "\r\n";

		$this->forceShutdown();
		@kill(getmypid());
		exit(1);
	}

	public function __debugInfo(){
		return [];
	}




	private function tickProcessor(){
		$this->nextTick = microtime(true);
		while($this->isRunning){
			$this->tick();
			$next = $this->nextTick - 0.0001;
			if($next > microtime(true)){
				@time_sleep_until($next);
			}
		}
	}

	public function addOnlinePlayer(Player $player){
		$this->updatePlayerListData($player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getSkinName(), $player->getSkinData(), $player->getSkinGeometryName(), $player->getSkinGeometryData(), $player->getCapeData(), $player->getOriginalProtocol() >= Info::PROTOCOL_140 ? $player->getXUID() : "", $player->getDeviceOS(), $player->getAdditionalSkinData());

		$this->playerList[$player->getRawUniqueId()] = $player;
	}

	public function removeOnlinePlayer(Player $player) {
		if (isset($this->playerList[$player->getRawUniqueId()])) {
			unset($this->playerList[$player->getRawUniqueId()]);
			$this->removePlayerListData($player->getUniqueId(), $this->playerList);
		}
	}

	public function updatePlayerListData(UUID $uuid, $entityId, $name, $skinName, $skinData, $skinGeometryName, $skinGeometryData, $capeData, $xuid, $deviceOS, $additionalSkinData, $players = null, $osForHuman = false){
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		$pk->entries[] = [$uuid, $entityId, $name, $skinName, $skinData, $capeData, $skinGeometryName, $skinGeometryData, $xuid, !$osForHuman ? $deviceOS : -1, $additionalSkinData];

	    $pk->setDeviceId($deviceOS);

		$this->broadcastPacket($players ?? $this->playerList, $pk);
	}

	public function removePlayerListData(UUID $uuid, $players = null){
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_REMOVE;
		$pk->entries[] = [$uuid];

		Server::broadcastPacket($players ?? $this->playerList, $pk);
	}

	/**
	 * @return void
	 */
	public function sendFullPlayerListData(Player $p){
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		foreach($this->playerList as $player){
			$pk->entries[] = [$player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getSkinName(), $player->getSkinData(), $player->getSkinGeometryName(), $player->getSkinGeometryData(), $player->getCapeData(), $player->getOriginalProtocol() >= Info::PROTOCOL_140 ? $player->getXUID() : "", $player->getDeviceOS(), $player->getAdditionalSkinData()];
			$pk->setDeviceId($player->getDeviceOS());
		}

		$p->dataPacket($pk);
	}

	public function onPlayerLogin(Player $player){
		$this->sendFullPlayerListData($player);
	}

	private $craftList = [];

	public function sendRecipeList(Player $p){
		if(!isset($this->craftList[$p->getPlayerProtocol()])) {
	    	Timings::$craftingDataCacheRebuildTimer->startTiming();
			$pk = new CraftingDataPacket();
			$pk->cleanRecipes = true;

			$recipies = [];

			foreach($this->getCraftingManager()->getRecipes() as $recipe){
			    $recipies[] = $recipe;
		    }

		    foreach ($this->getCraftingManager()->getFurnaceRecipes() as $recipe) {
			    $recipies[] = $recipe;
		    }

			$this->getPluginManager()->callEvent($ev = new SendRecipiesList($recipies));

			foreach($ev->getRecipies() as $recipe){
				if($recipe instanceof ShapedRecipe){
					$pk->addShapedRecipe($recipe);
				}elseif($recipe instanceof ShapelessRecipe){
					$pk->addShapelessRecipe($recipe);
				}elseif($recipe instanceof FurnaceRecipe) {
					$pk->addFurnaceRecipe($recipe);
				}
			}

			$pk->encode($p->getPlayerProtocol());
			$bpk = new BatchPacket();
			$buffer = $pk->getBuffer();
			$bpk->payload = zlib_encode(Binary::writeVarInt(strlen($buffer)) . $buffer, Player::getCompressAlg($p->getPlayerProtocol()), 7);
			$bpk->encode($p->getPlayerProtocol());
			$this->craftList[$p->getPlayerProtocol()] = $bpk->getBuffer();
	    	Timings::$craftingDataCacheRebuildTimer->stopTiming();
		}
		$p->getInterface()->putReadyPacket($p, $this->craftList[$p->getPlayerProtocol()]);
	}

	public function addPlayer($identifier, Player $player){
		$this->players[$identifier] = $player;
		$this->identifiers[spl_object_hash($player)] = $identifier;
	}

	private function checkTickUpdates($currentTick){

		//Do level ticks
		foreach($this->getLevels() as $level){
			try{
				$level->doTick($currentTick);
			}catch(\Exception $e){
				$this->logger->critical("Could not tick level " . $level->getName() . ": " . $e->getMessage());
				if(\pocketmine\DEBUG > 1 and $this->logger instanceof MainLogger){
					$this->logger->logException($e);
				}
			}
		}
	}

	public function doAutoSave(){
		if($this->getSavePlayerData()){
			foreach($this->getOnlinePlayers() as $index => $player){
				if($player->isOnline()){
					$player->save();
				}elseif(!$player->isConnected()){
					$this->removePlayer($player);
				}
			}
		}
		if($this->getAutoSave()){
		    Timings::$worldSaveTimer->startTiming();

			foreach($this->getLevels() as $level){
				$level->save(false);
			}

			Timings::$worldSaveTimer->stopTiming();
		}
	}

	/**
	 * @return Network
	 */
	public function getNetwork(){
		return $this->network;
	}

	/**
	 * @return MemoryManager
	 */
	public function getMemoryManager(){
		return $this->memoryManager;
	}

	private function titleTick(){
		if(defined("pocketmine\\DEBUG") and \pocketmine\DEBUG >= 0 and \pocketmine\ANSI === true){
			echo "\x1b]0;" . $this->getCodename() . " " . $this->getPocketMineVersion() . " | Online " . count($this->players) . "/" . $this->getMaxPlayers() . " | RAM " . round((memory_get_usage() / 1024) / 1024, 2) . "/" . round((memory_get_usage(true) / 1024) / 1024, 2) . " MB | U " . round($this->network->getUpload() / 1024, 2) . " D " . round($this->network->getDownload() / 1024, 2) . " kB/s | TPS " . $this->getTicksPerSecond() . " | Load " . $this->getTickUsage() . "%\x07";
		}
	}

	/**
	 * @param string $address
	 * @param int    $port
	 * @param string $payload
	 *
	 * TODO: move this to Network
	 */
	public function handlePacket($address, $port, $payload){
	    Timings::$serverRawPacketTimer->startTiming();

		try{
			if(strlen($payload) > 2 and substr($payload, 0, 2) === "\xfe\xfd" and $this->queryHandler instanceof QueryHandler){
				$this->queryHandler->handle($address, $port, $payload);
			}
		}catch(\Exception $e){
			if(\pocketmine\DEBUG > 1){
				if($this->logger instanceof MainLogger){
					$this->logger->logException($e);
				}
			}

			$this->getNetwork()->blockAddress($address, 600);
		}
		//TODO: add raw packet events

		Timings::$serverRawPacketTimer->stopTiming();
	}


	/**
	 * Tries to execute a server tick
	 */
	private function tick(){
		$tickTime = microtime(true);
		if($tickTime < $this->nextTick){
			return false;
		}

		Timings::$serverTickTimer->startTiming();

		++$this->tickCounter;

		$this->checkConsole();

		if($this->rcon !== null){
			$this->rcon->check();
		}

		Timings::$connectionTimer->startTiming();
		$this->network->processInterfaces();
		Timings::$connectionTimer->stopTiming();

	    Timings::$schedulerTimer->startTiming();
		$this->scheduler->mainThreadHeartbeat($this->tickCounter);
		Timings::$schedulerTimer->stopTiming();

		$this->checkTickUpdates($this->tickCounter);

        foreach($this->players as $player){
            $player->checkNetwork();
        }

		if(($this->tickCounter & 0b1111) === 0){
			$this->titleTick();
			if($this->queryHandler !== null and ($this->tickCounter & 0b111111111) === 0){
				try{
					$this->queryHandler->regenerateInfo();
				}catch(\Exception $e){
					if($this->logger instanceof MainLogger){
						$this->logger->logException($e);
					}
				}
			}
		}

		if($this->autoSave and ++$this->autoSaveTicker >= $this->autoSaveTicks){
			$this->autoSaveTicker = 0;
			$this->getLogger()->debug("[Auto Save] Saving worlds...");
			$start = microtime(true);
			$this->doAutoSave();
			$time = (microtime(true) - $start);
			$this->getLogger()->debug("[Auto Save] Save completed in " . ($time >= 1 ? round($time, 3) . "s" : round($time * 1000) . "ms"));
		}

		if(($this->tickCounter % 100) === 0){

			foreach($this->levels as $level){
				$level->clearCache();
			}

	    	if($this->getTicksPerSecond() < 12){
			    $this->logger->warning("Сервер нагружен.");
	    	}
		}

		if($this->dispatchSignals and $this->tickCounter % 5 === 0){
			pcntl_signal_dispatch();
		}

		$this->getMemoryManager()->check();

		Timings::$serverTickTimer->stopTiming();

		$now = microtime(true);
		array_shift($this->tickAverage);
		$tickDiff = $now - $tickTime;
		$this->tickAverage[] = ($tickDiff <= $this->tick) ? 20 : 1 / $tickDiff;
		array_shift($this->useAverage);
		$this->useAverage[] = min(1, $tickDiff * 20);

		if(($this->nextTick - $tickTime) < -1){
			$this->nextTick = $tickTime;
		}
		$this->nextTick += 0.05;

		return true;
	}

	private function registerEntities(){
	    Entity::registerEntity(Axolotl::class);
	    Entity::registerEntity(Arrow::class);
		Entity::registerEntity(BlazeFireball::class);
		Entity::registerEntity(Camera::class);
		Entity::registerEntity(Car::class);
		Entity::registerEntity(Chalkboard::class);
		Entity::registerEntity(DroppedItem::class);
		Entity::registerEntity(Egg::class);
		Entity::registerEntity(EnderCrystal::class);
		Entity::registerEntity(EnderPearl::class);
		Entity::registerEntity(FallingSand::class);
		Entity::registerEntity(FireBall::class);
		Entity::registerEntity(FireworkRocket::class);
		Entity::registerEntity(FishingHook::class);
		Entity::registerEntity(FloatingText::class);
		Entity::registerEntity(GhastFireball::class);
		Entity::registerEntity(LeashKnot::class);
		Entity::registerEntity(Lightning::class);
		Entity::registerEntity(Minecart::class);
		Entity::registerEntity(MinecartChest::class);
		Entity::registerEntity(MinecartCommandBlock::class);
		Entity::registerEntity(MinecartHopper::class);
		Entity::registerEntity(MinecartTNT::class);
		Entity::registerEntity(Painting::class);
		Entity::registerEntity(PrimedTNT::class);
		Entity::registerEntity(Snowball::class);
		Entity::registerEntity(XPOrb::class);
		Entity::registerEntity(Human::class, true);
		Entity::registerEntity(ArmorStand::class);
		Entity::registerEntity(Bat::class);
		Entity::registerEntity(Blaze::class);
		Entity::registerEntity(BlueWitherSkull::class);
		Entity::registerEntity(Boat::class);
		Entity::registerEntity(CaveSpider::class);
		Entity::registerEntity(Chicken::class);

		Entity::registerEntity(Cow::class);
		Entity::registerEntity(Creeper::class);
		Entity::registerEntity(Dragon::class);
		Entity::registerEntity(Donkey::class);
		Entity::registerEntity(ElderGuardian::class);
		Entity::registerEntity(EnderDragon::class);
		Entity::registerEntity(Enderman::class);
		Entity::registerEntity(Endermite::class);
		Entity::registerEntity(EvocationFangs::class);
		Entity::registerEntity(Giant::class);
		Entity::registerEntity(Ghast::class);
		Entity::registerEntity(Guardian::class);
		Entity::registerEntity(Herobrine::class);
		Entity::registerEntity(Horse::class);
		Entity::registerEntity(Husk::class);
		Entity::registerEntity(Illusioner::class);
		Entity::registerEntity(IronGolem::class);
		Entity::registerEntity(LavaSlime::class);
		Entity::registerEntity(LearnToCodeMascot::class);
		Entity::registerEntity(Llama::class);
		Entity::registerEntity(MagmaCube::class);
		Entity::registerEntity(Mooshroom::class);
		Entity::registerEntity(Mule::class);
		Entity::registerEntity(NPCHuman::class);
		Entity::registerEntity(Ocelot::class);
		Entity::registerEntity(Parrot::class);
		Entity::registerEntity(Pig::class);
		Entity::registerEntity(PigZombie::class);
		Entity::registerEntity(PolarBear::class);
		Entity::registerEntity(Rabbit::class);
		Entity::registerEntity(Sheep::class);
		Entity::registerEntity(Shulker::class);
		Entity::registerEntity(ShulkerBullet::class);
		Entity::registerEntity(Silverfish::class);
		Entity::registerEntity(Slime::class);
		Entity::registerEntity(Skeleton::class);
		Entity::registerEntity(SkeletonHorse::class);
		Entity::registerEntity(SnowGolem::class);
		Entity::registerEntity(Spider::class);
		Entity::registerEntity(Stray::class);
		Entity::registerEntity(Squid::class);
		Entity::registerEntity(Vex::class);
		Entity::registerEntity(Villager::class);
		Entity::registerEntity(Vindicator::class);
		Entity::registerEntity(Witch::class);
		Entity::registerEntity(Wither::class);
		Entity::registerEntity(WitherSkeleton::class);
		Entity::registerEntity(Wolf::class);
		Entity::registerEntity(Zombie::class);
		Entity::registerEntity(ZombieHorse::class);
		Entity::registerEntity(ZombieVillager::class);
		Entity::registerEntity(SplashPotion::class);
		Entity::registerEntity(Koni::class); // Кони топ люблю
		Entity::registerEntity(EnderCrystal::class);
	}

	private function registerTiles(){
	    Tile::registerTile(MobSpawner::class);
		Tile::registerTile(Chest::class);
		Tile::registerTile(Furnace::class);
		Tile::registerTile(Sign::class);
		Tile::registerTile(EnchantTable::class);
		Tile::registerTile(Skull::class);
		Tile::registerTile(FlowerPot::class);
        Tile::registerTile(EnderChest::class);
		Tile::registerTile(Bed::class);
		Tile::registerTile(Cauldron::class);
		Tile::registerTile(Dispenser::class);
		Tile::registerTile(PistonArm::class);
		Tile::registerTile(ItemFrame::class);
		Tile::registerTile(Dropper::class);
		Tile::registerTile(Hopper::class);
		Tile::registerTile(Beacon::class);
		Tile::registerTile(ShulkerBox::class);
	}

	public function getJsonCommands() {
		return $this->jsonCommands;
	}

	public function addLevel($level) {
		$this->levels[$level->getId()] = $level;
	}

}
