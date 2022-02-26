<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

/**
 * All Level related classes are here
 */
namespace pocketmine\level;

use pocketmine\block\Air;
use pocketmine\block\Beetroot;
use pocketmine\block\Block;
use pocketmine\block\BrownMushroom;
use pocketmine\block\Cactus;
use pocketmine\block\Carrot;
use pocketmine\block\Farmland;
use pocketmine\block\Grass;
use pocketmine\block\Ice;
use pocketmine\block\Leaves;
use pocketmine\block\Leaves2;
use pocketmine\block\MelonStem;
use pocketmine\block\Mycelium;
use pocketmine\block\Potato;
use pocketmine\block\PumpkinStem;
use pocketmine\block\RedMushroom;
use pocketmine\block\Sapling;
use pocketmine\block\SnowLayer;
use pocketmine\block\Sugarcane;
use pocketmine\block\Wheat;
use pocketmine\entity\Arrow;
use pocketmine\entity\Entity;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\ChunkPopulateEvent;
use pocketmine\event\level\ChunkUnloadEvent;
use pocketmine\event\level\LevelSaveEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\level\SpawnChangeEvent;
use pocketmine\event\LevelTimings;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Timings;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\FullChunk;
use pocketmine\level\format\generic\BaseFullChunk;
use pocketmine\level\format\generic\BaseLevelProvider;
use pocketmine\level\format\generic\EmptyChunkSection;
use pocketmine\level\format\LevelProvider;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\sound\Sound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Math;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\BlockMetadataStore;
use pocketmine\level\weather\Weather;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\Network;
use pocketmine\network\protocol\FullChunkDataPacket;
use pocketmine\network\protocol\MoveEntityPacket;
use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\protocol\BatchPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\tile\Chest;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\LevelException;
use pocketmine\utils\MainLogger;
use pocketmine\utils\ReversePriorityQueue;
use pocketmine\utils\TextFormat;
use pocketmine\network\protocol\Info;
use pocketmine\level\generator\GenerationTask;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GeneratorRegisterTask;
use pocketmine\level\generator\GeneratorUnregisterTask;
use pocketmine\utils\Random;
use pocketmine\level\generator\LightPopulationTask;
use pocketmine\level\generator\PopulationTask;
use pocketmine\entity\monster\Monster;
use pocketmine\entity\animal\Animal;
use pocketmine\nbt\NBT;
use pocketmine\network\protocol\LevelSoundEventPacket;
use pocketmine\network\multiversion\Entity as MultiversionEntity;
use pocketmine\utils\Binary;

class Level implements ChunkManager, Metadatable{

	private static $levelIdCounter = 1;
	public static $COMPRESSION_LEVEL = 6;


	const BLOCK_UPDATE_NORMAL = 1;
	const BLOCK_UPDATE_RANDOM = 2;
	const BLOCK_UPDATE_SCHEDULED = 3;
	const BLOCK_UPDATE_WEAK = 4;
	const BLOCK_UPDATE_TOUCH = 5;

	const TIME_DAY = 0;
	const TIME_SUNSET = 12000;
	const TIME_NIGHT = 14000;
	const TIME_SUNRISE = 23000;

	const TIME_FULL = 24000;

    const DIMENSION_NORMAL = 0;
    const DIMENSION_NETHER = 1;
    const DIMENSION_END = 2;

	/** @var Tile[] */
	protected $tiles = [];

	/** @var Player[] */
	protected $players = [];

	/** @var Entity[] */
	protected $entities = [];

	/** @var Entity[] */
	public $updateEntities = [];
	/** @var Tile[] */
	public $updateTiles = [];

    /** @var Weather */
    private $weather;

	protected $blockCache = [];

	/** @var Server */
	protected $server;

	/** @var int */
	protected $levelId;

	/** @var LevelProvider */
	protected $provider;

	/** @var Player[][] */
	protected $usedChunks = [];

	/** @var FullChunk[]|Chunk[] */
	protected $unloadQueue = [];

	protected $time;
	public $stopTime;

	private $folderName;

	/** @var FullChunk[]|Chunk[] */
	protected $chunks = [];

	/** @var Block[][] */
	protected $changedBlocks = [];
	protected $changedCount = [];

	/** @var ReversePriorityQueue */
	private $updateQueue;
	private $updateQueueIndex = [];
	private $autoSave = true;

	/** @var BlockMetadataStore */
	private $blockMetadata;

	private $useSections;

	/** @var Position */
	private $temporalPosition;
	/** @var Vector3 */
	private $temporalVector;

	/** @var \SplFixedArray */
	protected $blockStates;
	protected $playerHandItemQueue = [];

	private $chunkGenerationQueue = [];
	private $chunkGenerationQueueSize = 8;

	private $chunkPopulationQueue = [];
	private $chunkPopulationLock = [];
	private $chunkPopulationQueueSize = 2;

	protected $chunkTickRadius;
	protected $chunkTickList = [];
	protected $chunksPerTick;
	protected $clearChunksOnTick;
	protected $randomTickBlocks = [
		Block::GRASS => Grass::class,
		Block::SAPLING => Sapling::class,
		Block::LEAVES => Leaves::class,
		Block::WHEAT_BLOCK => Wheat::class,
		Block::FARMLAND => Farmland::class,
		Block::SNOW_LAYER => SnowLayer::class,
		Block::ICE => Ice::class,
		Block::CACTUS => Cactus::class,
		Block::SUGARCANE_BLOCK => Sugarcane::class,
		Block::RED_MUSHROOM => RedMushroom::class,
		Block::BROWN_MUSHROOM => BrownMushroom::class,
		Block::PUMPKIN_STEM => PumpkinStem::class,
		Block::MELON_STEM => MelonStem::class,
		//Block::VINE => true,
		Block::MYCELIUM => Mycelium::class,
		//Block::COCOA_BLOCK => true,
		Block::CARROT_BLOCK => Carrot::class,
		Block::POTATO_BLOCK => Potato::class,
		Block::LEAVES2 => Leaves2::class,

		Block::BEETROOT_BLOCK => Beetroot::class,
	];

	/** @var LevelTimings */
	public $timings;

	private $isFrozen = false;

	private $closed = false;

	protected $yMask;
	protected $maxY;
	protected $chunkCache = [];
	protected $generator = null;
    private $dimension = self::DIMENSION_NORMAL;
	/**
	 * Returns the chunk unique hash/key
	 *
	 * @param int $x
	 * @param int $z
	 *
	 * @return string
	 */
	public static function chunkHash($x, $z){
		return PHP_INT_SIZE === 8 ? (($x & 0xFFFFFFFF) << 32) | ($z & 0xFFFFFFFF) : $x . ":" . $z;
	}

	public static function blockHash($x, $y, $z){
		return PHP_INT_SIZE === 8 ? (($x & 0xFFFFFFF) << 36) | (($y & 0xFF) << 28) | ($z & 0xFFFFFFF) : $x . ":" . $y .":". $z;
	}

	public static function getBlockXYZ($hash, &$x, &$y, &$z){
		if(PHP_INT_SIZE === 8){
			$x = ($hash >> 36);
			$y = (($hash >> 28) & 0xFF);// << 57 >> 57; //it's always positive
			$z = ($hash & 0xFFFFFFF) << 36 >> 36;
		}else{
			$hash = explode(":", $hash);
			$x = (int) $hash[0];
			$y = (int) $hash[1];
			$z = (int) $hash[2];
		}
	}

	public static function getXZ($hash, &$x, &$z){
		if(PHP_INT_SIZE === 8){
			$x = ($hash >> 32) << 32 >> 32;
			$z = ($hash & 0xFFFFFFFF) << 32 >> 32;
		}else{
			$hash = explode(":", $hash);
			$x = (int) $hash[0];
			$z = (int) $hash[1];
		}
	}

	/**
	 * Init the default level data
	 *
	 * @param Server $server
	 * @param string $name
	 * @param string $path
	 * @param string $provider Class that extends LevelProvider
	 *
	 * @throws \Exception
	 */
	public function __construct(Server $server, $name, $path, $provider){
		$this->blockStates = Block::$fullList;
		$this->levelId = self::$levelIdCounter++;
		$this->blockMetadata = new BlockMetadataStore($this);
		$this->server = $server;
		$this->autoSave = $server->getAutoSave();

		/** @var LevelProvider $provider */

		if(is_subclass_of($provider, LevelProvider::class, true)){
			$this->provider = new $provider($this, $path);
			$this->yMask = $provider::getYMask();
			$this->maxY = $provider::getMaxY();
		}else{
			throw new LevelException("Provider is not a subclass of LevelProvider");
		}
		$this->server->getLogger()->info("Preparing level \"" . $this->provider->getName() . "\"");

		$this->useSections = $provider::usesChunkSection();

		$this->folderName = $name;
		$this->updateQueue = new ReversePriorityQueue();
		$this->updateQueue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
		$this->time = (int) $this->provider->getTime();

		$this->chunkTickRadius = min($this->server->getViewDistance(), max(1, (int) $this->server->getProperty("chunk-ticking.tick-radius", 4)));
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-ticking.per-tick", 40);
        $this->chunkPopulationQueueSize = (int) $this->server->getProperty("chunk-generation.population-queue-size", 2);
		$this->clearChunksOnTick = (bool) $this->server->getProperty("chunk-ticking.clear-tick-list", false);

		$this->timings = new LevelTimings($this);
		$this->temporalPosition = new Position(0, 0, 0, $this);
		$this->temporalVector = new Vector3(0, 0, 0);
        $this->weather = new Weather($this, 0);
		$this->generator = Generator::getGenerator($this->provider->getGenerator());
        $this->setDimension(self::DIMENSION_NORMAL);

        if ($this->server->netherEnabled and $this->server->netherName == $this->folderName)
            $this->setDimension(self::DIMENSION_NETHER);
        elseif ($this->server->enderEnabled and $this->server->enderName == $this->folderName)
            $this->setDimension(self::DIMENSION_END);
        if ($this->server->weatherEnabled and $this->getDimension() == self::DIMENSION_NORMAL) {
            $this->weather->setCanCalculate(true);
        } else $this->weather->setCanCalculate(false);
	}
    public function setDimension($dimension) {
        $this->dimension = $dimension;
    }

    public function getDimension(){
        return $this->dimension;
    }
	public function initLevel(){
		if (!is_null($this->generator)) {
			$generator = $this->generator;
			$this->generatorInstance = new $generator($this->provider->getGeneratorOptions());
			$this->generatorInstance->init($this, new Random($this->getSeed()));
			$this->registerGenerator();
		}
	}

    /**
     * @return Weather
     */
    public function getWeather() {
        return $this->weather;
    }

	/**
	 * @return BlockMetadataStore
	 */
	public function getBlockMetadata(){
		return $this->blockMetadata;
	}

	/**
	 * @return Server
	 */
	public function getServer(){
		return $this->server;
	}

	/**
	 * @return LevelProvider
	 */
	final public function getProvider(){
		return $this->provider;
	}

	/**
	 * Returns the unique level identifier
	 *
	 * @return int
	 */
	final public function getId(){
		return $this->levelId;
	}

	public function close(){
		if ($this->closed) {
			return;
		}
		if($this->getAutoSave()){
			$this->save();
		}
        $this->closed = true;
		foreach($this->chunks as $chunk){
			$this->unloadChunk($chunk->getX(), $chunk->getZ(), false);
		}

		$this->unregisterGenerator();
		$this->provider->close();
		$this->provider = null;
		$this->blockMetadata = null;
		$this->blockCache = [];
		$this->temporalPosition = null;
	}

	public function addSound(Sound $sound, array $players = null){
		$pk = $sound->encode();

		if($players === null){
			$players = $this->getUsingChunk($sound->x >> 4, $sound->z >> 4);
		}

		if($pk !== null){
			if(!is_array($pk)){
				Server::broadcastPacket($players, $pk);
			}else{
				foreach ($pk as $p) {
					Server::broadcastPacket($players, $p);
				}
			}
		}
	}

	public function broadcastLevelSoundEvent(Vector3 $pos, $soundId) {
		$pk = new LevelSoundEventPacket();
		$pk->eventId = $soundId;
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->blockId = -1;
		$pk->entityType = 1;
		Server::getInstance()->batchPackets($this->getUsingChunk($pos->x >> 4, $pos->z >> 4), [$pk]);
  }

	public function addParticle(Particle $particle, array $players = null){
		if($players === null){
			$players = $this->getUsingChunk($particle->x >> 4, $particle->z >> 4);
		}
		if (!empty($players)) {
			$particle->spawnFor($players, $this->getDimension());
		}
	}


	/**
	 * @return bool
	 */
	public function getAutoSave(){
		return $this->autoSave === true;
	}

	/**
	 * @param bool $value
	 */
	public function setAutoSave($value){
		$this->autoSave = $value;
	}

	/**
	 * Unloads the current level from memory safely
	 *
	 * @param bool $force default false, force unload of default level
	 *
	 * @return bool
	 */
	public function unload($force = false){

		$ev = new LevelUnloadEvent($this);

		if($this === $this->server->getDefaultLevel() and $force !== true){
			$ev->setCancelled(true);
		}

		$this->server->getPluginManager()->callEvent($ev);

		if(!$force and $ev->isCancelled()){
			return false;
		}

		$this->server->getLogger()->info("Unloading level \"" . $this->getName() . "\"");
		$defaultLevel = $this->server->getDefaultLevel();
		foreach($this->getPlayers() as $player){
			if($this === $defaultLevel or $defaultLevel === null){
				$player->close(TextFormat::YELLOW . $player->getName() . " has left the game", "Forced default level unload");
			} else {
				$player->teleport($defaultLevel->getSafeSpawn());
			}
		}

		if($this === $defaultLevel){
			$this->server->setDefaultLevel(null);
		}

		$this->close();

		return true;
	}

	/**
	 * Gets the chunks being used by players
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Player[]
	 */
	public function getUsingChunk($X, $Z){
		return isset($this->usedChunks[$index = self::chunkHash($X, $Z)]) ? $this->usedChunks[$index] : [];
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param int    $X
	 * @param int    $Z
	 * @param Player $player
	 */
	public function useChunk($X, $Z, Player $player, $autoLoad = true){
	    if ($autoLoad) {
	    	$this->loadChunk($X, $Z);
	    }

		$this->usedChunks[self::chunkHash($X, $Z)][$player->getId()] = $player;
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param int    $X
	 * @param int    $Z
	 * @param Player $player
	 */
	public function freeChunk($X, $Z, Player $player){
		unset($this->usedChunks[self::chunkHash($X, $Z)][$player->getId()]);
		$this->unloadChunkRequest($X, $Z, true);
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function checkTime(){
		if($this->stopTime == true){
			return;
		}else{
			$this->time += 1.25;
		}
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function sendTime(){
		$pk = new SetTimePacket();
		$pk->time = (int) $this->time;
		$pk->started = $this->stopTime == false;

		Server::broadcastPacket($this->players, $pk);
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param int $currentTick
	 *
	 * @return bool
	 */
	public function doTick($currentTick){

		$this->timings->doTick->startTiming();

		$this->checkTime();

		if(($currentTick % 200) === 0 && $this->server->getConfigBoolean("time-update", true)){
			$this->sendTime();
		}

        $this->weather->calcWeather($currentTick);

		$this->unloadChunks();

		$X = null;
		$Z = null;

		//Do block updates
		$this->timings->doTickPending->startTiming();
		while($this->updateQueue->count() > 0 and $this->updateQueue->current()["priority"] <= $currentTick){
			$blockData = $this->updateQueue->extract()["data"];
			$vec = $blockData["pos"];
			unset($this->updateQueueIndex[self::blockHash($vec->x, $vec->y, $vec->z)]);
			if(!$this->isChunkLoaded($vec->getX() >> 4, $vec->getZ() >> 4)){
			    continue;
			}
			$block = $this->getBlock($vec);
			$block->onUpdate($blockData["type"], 0);
			$block->onUpdate2($blockData["type"]);
		}
		$this->timings->doTickPending->stopTiming();

		$this->timings->entityTick->startTiming();
		//Update entities that need update
		Timings::$tickEntityTimer->startTiming();
		foreach($this->updateEntities as $id => $entity){
			if($entity->closed or !$entity->onUpdate($currentTick)){
				unset($this->updateEntities[$id]);
			}
		}
		Timings::$tickEntityTimer->stopTiming();
		$this->timings->entityTick->stopTiming();

		$this->timings->tileEntityTick->startTiming();
		//Update tiles that need update
		if(count($this->updateTiles) > 0){
			Timings::$tickTileEntityTimer->startTiming();
			foreach($this->updateTiles as $id => $tile){
				if($tile->onUpdate() !== true){
					unset($this->updateTiles[$id]);
				}
			}
			Timings::$tickTileEntityTimer->stopTiming();
		}
		$this->timings->tileEntityTick->stopTiming();

		$this->timings->doTickTiles->startTiming();
		$this->tickChunks();
		$this->timings->doTickTiles->stopTiming();

		if(count($this->changedCount) > 0){
			if(count($this->players) > 0){
				foreach($this->changedCount as $index => $mini){
					for($Y = 0; $Y < 8; ++$Y){
						if(($mini & (1 << $Y)) === 0){
							continue;
						}
						if(count($this->changedBlocks[$index][$Y]) < 256){
							continue;
						}else{
							self::getXZ($index, $X, $Z);
							foreach($this->getUsingChunk($X, $Z) as $p){
								$p->unloadChunk($X, $Z);
							}
							unset($this->changedBlocks[$index][$Y]);
						}
					}
				}
				$this->changedCount = [];
				if(count($this->changedBlocks) > 0){
					foreach($this->changedBlocks as $index => $mini){
						foreach($mini as $blocks){
							/** @var Block $b */
							foreach($blocks as $b){
								$pk = new UpdateBlockPacket();
								$pk->records[] = [$b->x, $b->z, $b->y, $b->getId(), $b->getDamage(), UpdateBlockPacket::FLAG_ALL];
								Server::broadcastPacket($this->getUsingChunk($b->x >> 4, $b->z >> 4), $pk);
							}
						}
					}
					$this->changedBlocks = [];
				}
			}else{
				$this->changedCount = [];
				$this->changedBlocks = [];
			}

		}

		foreach ($this->playerHandItemQueue as $senderId => $playerList) {
			foreach ($playerList as $recipientId => $data) {
				if ($data['time'] + 1 < microtime(true)) {
					unset($this->playerHandItemQueue[$senderId][$recipientId]);
					if ($data['sender']->isSpawned($data['recipient'])) {
						$data['sender']->getInventory()->sendHeldItem($data['recipient']);
					}
					if (count($this->playerHandItemQueue[$senderId]) == 0) {
						unset($this->playerHandItemQueue[$senderId]);
					}
				}
			}
		}
		$this->timings->doTick->stopTiming();
	}

	/**
	 * @param Player[] $target
	 * @param Block[]  $blocks
	 * @param int      $flags
	 */
	public function sendBlocks(array $target, array $blocks, $flags = UpdateBlockPacket::FLAG_ALL) {
		foreach ($blocks as $b) {
			if ($b === null) {
				continue;
			}
			$pk = new UpdateBlockPacket();
			if ($b instanceof Block) {
				$pk->records[] = [$b->x, $b->z, $b->y, $b->getId(), $b->getDamage(), $flags];
			} else {
				$fullBlock = $this->getFullBlock($b->x, $b->y, $b->z);
				$pk->records[] = [$b->x, $b->z, $b->y, $fullBlock >> 4, $fullBlock & 0xf, $flags];
			}
			Server::broadcastPacket($target, $pk);
		}
	}

	public function clearCache(bool $force = false){
        if($force){
            $this->chunkCache = [];
            $this->blockCache = [];
        }else{
            $count = 0;
			foreach($this->blockCache as $list){
				$count += count($list);
				if($count > 2048){
					$this->blockCache = [];
					break;
				}
            }
        }
	}

	protected function tickChunks(){
		if($this->chunksPerTick <= 0 or count($this->players) === 0){
			$this->chunkTickList = [];
			return;
		}

		$chunksPerPlayer = min(200, max(1, (int) ((($this->chunksPerTick - count($this->players)) / count($this->players)) + 0.5)));
		$randRange = 3 + $chunksPerPlayer / 30;
		$randRange = $randRange > $this->chunkTickRadius ? $this->chunkTickRadius : $randRange;

		foreach($this->players as $player){
			$x = $player->x >> 4;
			$z = $player->z >> 4;

			$index = self::chunkHash($x, $z);
			$existingPlayers = max(0, isset($this->chunkTickList[$index]) ? $this->chunkTickList[$index] : 0);
			$this->chunkTickList[$index] = $existingPlayers + 1;
			for($chunk = 0; $chunk < $chunksPerPlayer; ++$chunk){
				$dx = mt_rand(-$randRange, $randRange);
				$dz = mt_rand(-$randRange, $randRange);
				$hash = self::chunkHash($dx + $x, $dz + $z);
				if(!isset($this->chunkTickList[$hash]) and isset($this->chunks[$hash])){
					$this->chunkTickList[$hash] = -1;
				}
			}
		}

		$blockTest = 0;

		$chunkX = $chunkZ = null;
		foreach($this->chunkTickList as $index => $players){
			self::getXZ($index, $chunkX, $chunkZ);
			if(!isset($this->chunks[$index]) or ($chunk = $this->getChunk($chunkX, $chunkZ, false)) === null){
				unset($this->chunkTickList[$index]);
				continue;
			}elseif($players <= 0){
				unset($this->chunkTickList[$index]);
			}




			foreach($chunk->getEntities() as $entity){
				$entity->scheduleUpdate();
			}


			if($this->useSections){
				foreach($chunk->getSections() as $section){
					if(!($section instanceof EmptyChunkSection)){
						$Y = $section->getY();
						$k = mt_rand(0, 0x7fffffff);
						for($i = 0; $i < 3; ++$i, $k >>= 10){
							$x = $k & 0x0f;
							$y = ($k >> 8) & 0x0f;
							$z = ($k >> 16) & 0x0f;

							$blockId = $section->getBlockId($x, $y, $z);
							if(isset($this->randomTickBlocks[$blockId])){
								$class = $this->randomTickBlocks[$blockId];
								/** @var Block $block */
								$block = new $class($section->getBlockData($x, $y, $z));
								$block->x = $chunkX * 16 + $x;
								$block->y = ($Y << 4) + $y;
								$block->z = $chunkZ * 16 + $z;
								$block->level = $this;
								$block->onUpdate(self::BLOCK_UPDATE_RANDOM, 0);
								$block->onUpdate2(self::BLOCK_UPDATE_RANDOM);
							}
						}
					}
				}
			}else{
				for($Y = 0; $Y < 8 and ($Y < 3 or $blockTest !== 0); ++$Y){
					$blockTest = 0;
					$k = mt_rand(0, 0x7fffffff);
					for($i = 0; $i < 3; ++$i, $k >>= 10){
						$x = $k & 0x0f;
						$y = ($k >> 8) & 0x0f;
						$z = ($k >> 16) & 0x0f;

						$blockTest |= $blockId = $chunk->getBlockId($x, $y + ($Y << 4), $z);
						if(isset($this->randomTickBlocks[$blockId])){
							$class = $this->randomTickBlocks[$blockId];
							/** @var Block $block */
							$block = new $class($chunk->getBlockData($x, $y + ($Y << 4), $z));
							$block->x = $chunkX * 16 + $x;
							$block->y = ($Y << 4) + $y;
							$block->z = $chunkZ * 16 + $z;
							$block->level = $this;
							$block->onUpdate(self::BLOCK_UPDATE_RANDOM, 0);
							$block->onUpdate2(self::BLOCK_UPDATE_RANDOM);
						}
					}
				}
			}
		}

		if($this->clearChunksOnTick){
			$this->chunkTickList = [];
		}
	}

	public function __debugInfo(){
		return [];
	}

	/**
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function save($force = false){

		if($this->getAutoSave() === false and $force === false){
			return false;
		}

		$this->server->getPluginManager()->callEvent($ev = new LevelSaveEvent($this));
		if($ev->isCancelled())
			return false;

		$this->provider->setTime((int) $this->time);
		$this->saveChunks();
		if($this->provider instanceof BaseLevelProvider){
			$this->provider->saveLevelData();
		}

		return true;
	}

	public function saveChunks(){
        $this->timings->syncChunkSaveTimer->startTiming();
		foreach($this->chunks as $chunk){
            if(($chunk->hasChanged() or count($chunk->getTiles()) > 0 or count($chunk->getEntities()) > 0) and $chunk->isGenerated()){
				$this->provider->setChunk($chunk->getX(), $chunk->getZ(), $chunk);
				$this->provider->saveChunk($chunk->getX(), $chunk->getZ());
				$chunk->setChanged(false);
			}
		}
        $this->timings->syncChunkSaveTimer->stopTiming();
	}

	/**
	 * @param Vector3 $pos
	 */
	public function updateAround(Vector3 $pos, $deep){
		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x - 1, $pos->y, $pos->z))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL, $deep);
			$ev->getBlock()->onUpdate2(self::BLOCK_UPDATE_NORMAL);
		}

		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x + 1, $pos->y, $pos->z))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL, $deep);
			$ev->getBlock()->onUpdate2(self::BLOCK_UPDATE_NORMAL);
		}

		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x, $pos->y - 1, $pos->z))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL, $deep);
			$ev->getBlock()->onUpdate2(self::BLOCK_UPDATE_NORMAL);
		}

		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x, $pos->y + 1, $pos->z))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL, $deep);
			$ev->getBlock()->onUpdate2(self::BLOCK_UPDATE_NORMAL);
		}

		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x, $pos->y, $pos->z - 1))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL, $deep);
			$ev->getBlock()->onUpdate2(self::BLOCK_UPDATE_NORMAL);
		}

		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x, $pos->y, $pos->z + 1))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL, $deep);
			$ev->getBlock()->onUpdate2(self::BLOCK_UPDATE_NORMAL);
		}
	}

	/**
	 * @param Vector3 $pos
	 * @param int     $delay
	 */
	public function scheduleUpdate(Vector3 $pos, $delay, $type = self::BLOCK_UPDATE_SCHEDULED){
		if(isset($this->updateQueueIndex[$index = self::blockHash($pos->x, $pos->y, $pos->z)]) and $this->updateQueueIndex[$index] <= $delay){
			return;
		}
		$this->updateQueueIndex[$index] = $delay;
		$this->updateQueue->insert(['pos' => new Vector3((int) $pos->x, (int) $pos->y, (int) $pos->z), 'type' => $type], (int) $delay + $this->server->getTick());
	}

	/**
	 * @param AxisAlignedBB $bb
	 *
	 * @return Block[]
	 */
	public function getCollisionBlocks(AxisAlignedBB $bb){
		$minX = Math::floorFloat($bb->minX);
		$minY = Math::floorFloat($bb->minY);
		$minZ = Math::floorFloat($bb->minZ);
		$maxX = Math::floorFloat($bb->maxX + 1);
		$maxY = Math::floorFloat($bb->maxY + 1);
		$maxZ = Math::floorFloat($bb->maxZ + 1);

		$collides = [];

		$v = $this->temporalVector;

		for($v->z = $minZ; $v->z < $maxZ; ++$v->z){
			for($v->x = $minX; $v->x < $maxX; ++$v->x){
				for($v->y = $minY - 1; $v->y < $maxY; ++$v->y){
					$block = $this->getBlock($v);
					if($block->getId() !== 0 and $block->collidesWithBB($bb)){
						$collides[] = $block;
					}
				}
			}
		}

		return $collides;
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return bool
	 */
	public function isFullBlock(Vector3 $pos){
		if($pos instanceof Block){
			$bb = $pos->getBoundingBox();
		}else{
			$bb = $this->getBlock($pos)->getBoundingBox();
		}

		return $bb !== null and $bb->getAverageEdgeLength() >= 1;
	}

	/**
	 * @param Entity        $entity
	 * @param AxisAlignedBB $bb
	 * @param boolean       $entities
	 *
	 * @return AxisAlignedBB[]
	 */
	public function getCollisionCubes(Entity $entity, AxisAlignedBB $bb, $entities = true){
		$minX = Math::floorFloat($bb->minX);
		$minY = Math::floorFloat($bb->minY);
		$minZ = Math::floorFloat($bb->minZ);
		$maxX = Math::floorFloat($bb->maxX + 1);
		$maxY = Math::floorFloat($bb->maxY + 1);
		$maxZ = Math::floorFloat($bb->maxZ + 1);

		$collides = [];
		$v = $this->temporalVector;

		for($v->z = $minZ; $v->z < $maxZ; ++$v->z){
			for($v->x = $minX; $v->x < $maxX; ++$v->x){
				for($v->y = $minY - 1; $v->y < $maxY; ++$v->y){
					$block = $this->getBlock($v);
					if($block->getId() !== 0){
						$block->collidesWithBB($bb, $collides);
					}
				}
			}
		}

		if($entities){
			foreach($this->getCollidingEntities($bb->grow(0.25, 0.25, 0.25), $entity) as $ent){
				$collides[] = clone $ent->boundingBox;
			}
		}

		return $collides;
	}

	/*
	public function rayTraceBlocks(Vector3 $pos1, Vector3 $pos2, $flag = false, $flag1 = false, $flag2 = false){
		if(!is_nan($pos1->x) and !is_nan($pos1->y) and !is_nan($pos1->z)){
			if(!is_nan($pos2->x) and !is_nan($pos2->y) and !is_nan($pos2->z)){
				$x1 = (int) $pos1->x;
				$y1 = (int) $pos1->y;
				$z1 = (int) $pos1->z;
				$x2 = (int) $pos2->x;
				$y2 = (int) $pos2->y;
				$z2 = (int) $pos2->z;

				$block = $this->getBlock(Vector3::createVector($x1, $y1, $z1));

				if(!$flag1 or $block->getBoundingBox() !== null){
					$ob = $block->calculateIntercept($pos1, $pos2);
					if($ob !== null){
						return $ob;
					}
				}

				$movingObjectPosition = null;

				$k = 200;

				while($k-- >= 0){
					if(is_nan($pos1->x) or is_nan($pos1->y) or is_nan($pos1->z)){
						return null;
					}

					if($x1 === $x2 and $y1 === $y2 and $z1 === $z2){
						return $flag2 ? $movingObjectPosition : null;
					}

					$flag3 = true;
					$flag4 = true;
					$flag5 = true;

					$i = 999;
					$j = 999;
					$k = 999;

					if($x1 > $x2){
						$i = $x2 + 1;
					}elseif($x1 < $x2){
						$i = $x2;
					}else{
						$flag3 = false;
					}

					if($y1 > $y2){
						$j = $y2 + 1;
					}elseif($y1 < $y2){
						$j = $y2;
					}else{
						$flag4 = false;
					}

					if($z1 > $z2){
						$k = $z2 + 1;
					}elseif($z1 < $z2){
						$k = $z2;
					}else{
						$flag5 = false;
					}

					//TODO
				}
			}
		}
	}
	*/

	public function getFullLight(Vector3 $pos){
		$chunk = $this->getChunk($pos->x >> 4, $pos->z >> 4, false);
		$level = 0;
		if($chunk instanceof FullChunk){
			$level = $chunk->getBlockSkyLight($pos->x & 0x0f, $pos->y & $this->getYMask(), $pos->z & 0x0f);
			//TODO: decrease light level by time of day
			if($level < 15){
				$level = max($chunk->getBlockLight($pos->x & 0x0f, $pos->y & $this->getYMask(), $pos->z & 0x0f));
			}
		}

		return $level;
	}

	/**
	 * @param $x
	 * @param $y
	 * @param $z
	 *
	 * @return int bitmap, (id << 4) | data
	 */
	public function getFullBlock($x, $y, $z){
		return $this->getChunk($x >> 4, $z >> 4, false)->getFullBlock($x & 0x0f, $y & $this->getYMask(), $z & 0x0f);
	}

	/**
	 * Gets the Block object on the Vector3 location
	 *
	 * @param Vector3 $pos
	 * @param boolean $cached
	 *
	 * @return Block
	 */
	public function getBlock(Vector3 $pos, $cached = true){
		$index = self::blockHash($pos->x, $pos->y, $pos->z);
		if($cached === true && isset($this->blockCache[$index])){
			return $this->blockCache[$index];
		}elseif($pos->y >= 0 && $pos->y < $this->getMaxY() && isset($this->chunks[$chunkIndex = self::chunkHash ($pos->x >> 4, $pos->z >> 4)])){
			$fullState = $this->chunks[$chunkIndex]->getFullBlock($pos->x & 0x0f, $pos->y & $this->getYMask(), $pos->z & 0x0f);
		}else{
			$fullState = 0;
		}
		$block = clone $this->blockStates[$fullState & 0xfff];
		$block->x = $pos->x;
		$block->y = $pos->y;
		$block->z = $pos->z;
		$block->level = $this;

		return $this->blockCache[$index] = $block;
	}

	public function getBlockAt($x, $y, $z, $cached = true){
		$pos = new Vector3($x, $y, $z);
		$index = self::blockHash($pos->x, $pos->y, $pos->z);
		if($cached === true && isset($this->blockCache[$index])){
			return $this->blockCache[$index];
		}elseif($pos->y >= 0 && $pos->y < $this->getMaxY() && isset($this->chunks[$chunkIndex = self::chunkHash ($pos->x >> 4, $pos->z >> 4)])){
			$fullState = $this->chunks[$chunkIndex]->getFullBlock($pos->x & 0x0f, $pos->y & $this->getYMask(), $pos->z & 0x0f);
		}else{
			$fullState = 0;
		}
		$block = clone $this->blockStates[$fullState & 0xfff];
		$block->x = $pos->x;
		$block->y = $pos->y;
		$block->z = $pos->z;
		$block->level = $this;

		return $this->blockCache[$index] = $block;
	}

	public function updateAllLight(Vector3 $pos){
		$this->updateBlockSkyLight($pos->x, $pos->y, $pos->z);
		$this->updateBlockLight($pos->x, $pos->y, $pos->z);
	}

	public function updateBlockSkyLight($x, $y, $z){
		//TODO
	}

	public function updateBlockLight($x, $y, $z){
        $this->timings->doBlockLightUpdates->startTiming();
		$lightPropagationQueue = new \SplQueue();
		$lightRemovalQueue = new \SplQueue();
		$visited = [];
		$removalVisited = [];

		$oldLevel = $this->getChunk($x >> 4,  $z >> 4, true)->getBlockLight($x & 0x0f,  $y & $this->getYMask(),  $z & 0x0f);
		$newLevel = (int) Block::$light[$this->getChunk($x >> 4,  $z >> 4, true)->getBlockId($x & 0x0f,  $y & $this->getYMask(),  $z & 0x0f)];

		if($oldLevel !== $newLevel){
			$this->getChunk($x >> 4,  $z >> 4, true)->setBlockLight($x & 0x0f,  $y & $this->getYMask(),  $z & 0x0f,  $newLevel & 0x0f);

			if($newLevel < $oldLevel){
				$removalVisited[self::blockHash($x, $y, $z)] = true;
				$lightRemovalQueue->enqueue([new Vector3($x, $y, $z), $oldLevel]);
			}else{
				$visited[self::blockHash($x, $y, $z)] = true;
				$lightPropagationQueue->enqueue(new Vector3($x, $y, $z));
			}
		}

		while(!$lightRemovalQueue->isEmpty()){
			/** @var Vector3 $node */
			$val = $lightRemovalQueue->dequeue();
			$node = $val[0];
			$lightLevel = $val[1];

			$this->computeRemoveBlockLight($node->x - 1, $node->y, $node->z, $lightLevel, $lightRemovalQueue, $lightPropagationQueue, $removalVisited, $visited);
			$this->computeRemoveBlockLight($node->x + 1, $node->y, $node->z, $lightLevel, $lightRemovalQueue, $lightPropagationQueue, $removalVisited, $visited);
			$this->computeRemoveBlockLight($node->x, $node->y - 1, $node->z, $lightLevel, $lightRemovalQueue, $lightPropagationQueue, $removalVisited, $visited);
			$this->computeRemoveBlockLight($node->x, $node->y + 1, $node->z, $lightLevel, $lightRemovalQueue, $lightPropagationQueue, $removalVisited, $visited);
			$this->computeRemoveBlockLight($node->x, $node->y, $node->z - 1, $lightLevel, $lightRemovalQueue, $lightPropagationQueue, $removalVisited, $visited);
			$this->computeRemoveBlockLight($node->x, $node->y, $node->z + 1, $lightLevel, $lightRemovalQueue, $lightPropagationQueue, $removalVisited, $visited);
		}

		while(!$lightPropagationQueue->isEmpty()){
			/** @var Vector3 $node */
			$node = $lightPropagationQueue->dequeue();

			$lightLevel = $this->getChunk($node->x >> 4,  $node->z >> 4, true)->getBlockLight($node->x & 0x0f,  $node->y & $this->getYMask(),  $node->z & 0x0f) - (int) Block::$lightFilter[$this->getChunk($node->x >> 4,  $node->z >> 4, true)->getBlockId($node->x & 0x0f,  $node->y & $this->getYMask(),  $node->z & 0x0f)];

			if($lightLevel >= 1){
				$this->computeSpreadBlockLight($node->x - 1, $node->y, $node->z, $lightLevel, $lightPropagationQueue, $visited);
				$this->computeSpreadBlockLight($node->x + 1, $node->y, $node->z, $lightLevel, $lightPropagationQueue, $visited);
				$this->computeSpreadBlockLight($node->x, $node->y - 1, $node->z, $lightLevel, $lightPropagationQueue, $visited);
				$this->computeSpreadBlockLight($node->x, $node->y + 1, $node->z, $lightLevel, $lightPropagationQueue, $visited);
				$this->computeSpreadBlockLight($node->x, $node->y, $node->z - 1, $lightLevel, $lightPropagationQueue, $visited);
				$this->computeSpreadBlockLight($node->x, $node->y, $node->z + 1, $lightLevel, $lightPropagationQueue, $visited);
			}
		}
        $this->timings->doBlockLightUpdates->stopTiming();
	}

	private function computeRemoveBlockLight($x, $y, $z, $currentLight, \SplQueue $queue, \SplQueue $spreadQueue, array &$visited, array &$spreadVisited){
		$current = $this->getChunk($x >> 4,  $z >> 4, true)->getBlockLight($x & 0x0f,  $y & $this->getYMask(),  $z & 0x0f);

		if($current !== 0 and $current < $currentLight){
			$this->getChunk($x >> 4,  $z >> 4, true)->setBlockLight($x & 0x0f,  $y & $this->getYMask(),  $z & 0x0f,  0 & 0x0f);

			if(!isset($visited[$index = self::blockHash($x, $y, $z)])){
				$visited[$index] = true;
				if($current > 1){
					$queue->enqueue([new Vector3($x, $y, $z), $current]);
				}
			}
		}elseif($current >= $currentLight){
			if(!isset($spreadVisited[$index = self::blockHash($x, $y, $z)])){
				$spreadVisited[$index] = true;
				$spreadQueue->enqueue(new Vector3($x, $y, $z));
			}
		}
	}

	private function computeSpreadBlockLight($x, $y, $z, $currentLight, \SplQueue $queue, array &$visited){
		$current = $this->getChunk($x >> 4,  $z >> 4, true)->getBlockLight($x & 0x0f,  $y & $this->getYMask(),  $z & 0x0f);

		if($current < $currentLight){
			$this->getChunk($x >> 4,  $z >> 4, true)->setBlockLight($x & 0x0f,  $y & $this->getYMask(),  $z & 0x0f,  $currentLight & 0x0f);

			if(!isset($visited[$index = self::blockHash($x, $y, $z)])){
				$visited[$index] = true;
				if($currentLight > 1){
					$queue->enqueue(new Vector3($x, $y, $z));
				}
			}
		}
	}

	/**
	 * Sets on Vector3 the data from a Block object,
	 * does block updates and puts the changes to the send queue.
	 *
	 * If $direct is true, it'll send changes directly to players. if false, it'll be queued
	 * and the best way to send queued changes will be done in the next tick.
	 * This way big changes can be sent on a single chunk update packet instead of thousands of packets.
	 *
	 * If $update is true, it'll get the neighbour blocks (6 sides) and update them.
	 * If you are doing big changes, you might want to set this to false, then update manually.
	 *
	 * @param Vector3 $pos
	 * @param Block   $block
	 * @param bool    $direct
	 * @param bool    $update
	 *
	 * @return bool Whether the block has been updated or not
	 */
	public function setBlock(Vector3 $pos, Block $block, $direct = false, $update = true, $deep = 0){
		if($pos->y < 0 or $pos->y >= $this->getMaxY()){
			return false;
		}

        $this->timings->setBlock->startTiming();

		unset($this->blockCache[$index = self::blockHash($pos->x, $pos->y, $pos->z)]);

		if($this->getChunk($pos->x >> 4, $pos->z >> 4, true)->setBlock($pos->x & 0x0f, $pos->y & $this->getYMask(), $pos->z & 0x0f, $block->getId(), $block->getDamage())){
			if(!($pos instanceof Position)){
				$pos = $this->temporalPosition->setComponents($pos->x, $pos->y, $pos->z);
			}
			$block->position($pos);
			$chX = $pos->x >> 4;
			$chZ = $pos->z >> 4;
			$index = self::chunkHash($chX, $chZ);
			$this->chunkCacheClear($chX, $chZ);

			if($direct === true){
								$this->sendBlocks($this->getUsingChunk($block->x >> 4, $block->z >> 4), [$block]);
			}else{
				if(!($pos instanceof Position)){
					$pos = $this->temporalPosition->setComponents($pos->x, $pos->y, $pos->z);
				}
				$block->position($pos);
				if(!isset($this->changedBlocks[$index])){
					$this->changedBlocks[$index] = [];
					$this->changedCount[$index] = 0;
				}
				$Y = $pos->y >> 4;
				if(!isset($this->changedBlocks[$index][$Y])){
					$this->changedBlocks[$index][$Y] = [];
					$this->changedCount[$index] |= 1 << $Y;
				}
				$this->changedBlocks[$index][$Y][] = clone $block;
			}

			if($update === true){
				$this->updateAllLight($block);

				$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($block));
				if(!$ev->isCancelled()){
					$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL, $deep);
					$ev->getBlock()->onUpdate2(self::BLOCK_UPDATE_NORMAL);
					foreach($this->getNearbyEntities(new AxisAlignedBB($block->x - 1, $block->y - 1, $block->z - 1, $block->x + 2, $block->y + 2, $block->z + 2)) as $entity){
						$entity->scheduleUpdate();
					}
				}

				$this->updateAround($pos, $deep);
			}

            $this->timings->setBlock->stopTiming();
			return true;
		}

        $this->timings->setBlock->stopTiming();

		return false;
	}

	/**
	 * @param Vector3 $source
	 * @param Item    $item
	 * @param Vector3 $motion
	 * @param int     $delay
	 */
	public function dropItem(Vector3 $source, Item $item, Vector3 $motion = null, $delay = 10){
		$motion = $motion === null ? new Vector3(lcg_value() * 0.2 - 0.1, 0.2, lcg_value() * 0.2 - 0.1) : $motion;
		if($item->getId() > 0 and $item->getCount() > 0){
			$chunk = $this->getChunk($source->getX() >> 4, $source->getZ() >> 4);
			if(is_null($chunk)){
				return;
			}
			$itemEntity = Entity::createEntity("Item", $chunk, new Compound("", [
				"Pos" => new Enum("Pos", [
					new DoubleTag("", $source->getX()),
					new DoubleTag("", $source->getY()),
					new DoubleTag("", $source->getZ())
				]),

				"Motion" => new Enum("Motion", [
					new DoubleTag("", $motion->x),
					new DoubleTag("", $motion->y),
					new DoubleTag("", $motion->z)
				]),
				"Rotation" => new Enum("Rotation", [
					new FloatTag("", lcg_value() * 360),
					new FloatTag("", 0)
				]),
				"Health" => new ShortTag("Health", 5),
				"Item" => NBT::putItemHelper($item),
				"PickupDelay" => new ShortTag("PickupDelay", $delay)
			]));

			$itemEntity->spawnToAll();
		}
	}

	/**
	 * Tries to break a block using a item, including Player time checks if available
	 * It'll try to lower the durability if Item is a tool, and set it to Air if broken.
	 *
	 * @param Vector3 $vector
	 * @param Item    &$item (if null, can break anything)
	 * @param Player  $player
	 *
	 * @return boolean
	 */
	public function useBreakOn(Vector3 $vector, Item &$item = null, Player $player = null) {
		if ($item === null) {
			$item = Item::get(Item::AIR, 0, 0);
		}
		$target = $this->getBlock($vector);
		$drops = $target->getDrops($item);
		if ($player instanceof Player) {
			if ($player->isSpectator() || !$player->canBreakBlocks()) {
				return false;
			}
			$ev = new BlockBreakEvent($player, $target, $item, ($player->getGamemode() & 0x01) === 1 ? true : false, $drops);

			if ($player->isSurvival() && $item instanceof Item && !$target->isBreakable($item)) {
				$ev->setCancelled();
			}

			if (!$player->isOp() && ($distance = $this->server->getConfigInt("spawn-protection", 16)) > -1) {
				$t = new Vector2($target->x, $target->z);
				$s = new Vector2($this->getSpawnLocation()->x, $this->getSpawnLocation()->z);
				if ($t->distance($s) <= $distance) { //set it to cancelled so plugins can bypass this
					$ev->setCancelled();
				}
			}

			$breakTime = $player->isCreative() ? 0.15 : $player->getBreakTime($target, $item);
			if (!$ev->getInstaBreak() && ($player->lastBreak + $breakTime) >= microtime(true)) {
				return false;
			}

			if (!$ev->isCancelled()) {
				$this->server->getPluginManager()->callEvent($ev);
			}
			if ($ev->isCancelled()) {
				$player->lastBreak = microtime(true);
				return false;
			}

			$drops = $ev->getDrops();
			$player->lastBreak = microtime(true);

			$this->addParticle(new DestroyBlockParticle($target->add(0.5, 0.5, 0.5), $target));
		} else if ($item instanceof Item && !$target->isBreakable($item)) {
			return false;
		}

		$level = $target->getLevel();

		if ($level instanceof Level) {
			$above = $level->getBlock(new Vector3($target->x, $target->y + 1, $target->z));
			if ($above instanceof Block) {
				if ($above->getId() === Item::FIRE) {
					$level->setBlock($above, new Air(), true);
				}
			}
		}
		$target->onBreak($item);
		$tile = $this->getTile($target);
		if ($tile instanceof Tile) {
			if ($tile instanceof InventoryHolder) {
				if ($tile instanceof Chest) {
					$tile->unpair();
				}
				foreach ($tile->getInventory()->getContents() as $chestItem) {
					$this->dropItem($target, $chestItem);
				}
			}
			$tile->close();
		}

		if ($item instanceof Item) {
			$item->useOn($target);
			if ($item->isTool() && $item->getDamage() >= $item->getMaxDurability()) {
				$item = Item::get(Item::AIR, 0, 0);
			}
		}

		if (!($player instanceof Player) || $player->isSurvival()) {
			foreach ($drops as $drop) {
				if ($drop[2] > 0) {
				    if($player instanceof Player){
				        if($player->getInventory()->canAddItem(Item::get($drop[0], $drop[1], $drop[2]))){
					        $player->getInventory()->addItem(Item::get($drop[0], $drop[1], $drop[2]));
				        } else {
					        $this->dropItem($vector->add(0.5, 0.5, 0.5), Item::get(...$drop));
				        }
				    } else {
					    $this->dropItem($vector->add(0.5, 0.5, 0.5), Item::get(...$drop));
				    }
				}
			}
		}

		return true;
	}

	/**
	 * Uses a item on a position and face, placing it or activating the block
	 *
	 * @param Vector3 $vector
	 * @param Item    $item
	 * @param int     $face
	 * @param float   $fx     default 0.0
	 * @param float   $fy     default 0.0
	 * @param float   $fz     default 0.0
	 * @param Player  $player default null
	 *
	 * @return boolean
	 */
	public function useItemOn(Vector3 $vector, Item &$item, $face, $fx = 0.0, $fy = 0.0, $fz = 0.0, Player $player = null) {
		$target = $this->getBlock($vector);
		if ($target->getId() === Item::AIR) {
			return false;
		}

		$block = $target->getSide($face);
		if ($block->y >= $this->getMaxY() || $block->y < 0) {
			return false;
		}

		if ($player instanceof Player) {
			$ev = new PlayerInteractEvent($player, $item, $target, $face);
			$this->server->getPluginManager()->callEvent($ev);
			if ($player->isSpectator()) {
				$ev->setCancelled(true);
			}
			if (!$ev->isCancelled()) {
				$target->onUpdate(self::BLOCK_UPDATE_TOUCH, 0);
				$target->onUpdate2(self::BLOCK_UPDATE_TOUCH);
				if (!$player->isSneaking()) {
					if ($target->canBeActivated() === true && $target->onActivate($item, $player) === true) {
						return true;
					}
				}
				if ($item->canBeActivated() && $item->onActivate($this, $player, $block, $target, $face, $fx, $fy, $fz)) {
					if ($item->getCount() <= 0) {
						$item = Item::get(Item::AIR, 0, 0);
					}
					return true;
				}
			} else {
			    return false;
			}
		} else if ($target->canBeActivated() === true && $target->onActivate($item, $player) === true) {
			return true;
		}

		if ($item->isPlaceable()) {
			$hand = $item->getBlock();
			$hand->position($block);
		} else if ($block->getId() === Item::FIRE) {
			$block->onUpdate(self::BLOCK_UPDATE_TOUCH, 0);
			$block->onUpdate2(self::BLOCK_UPDATE_TOUCH);
			return false;
		} else {
			return false;
		}

		if (!($block->canBeReplaced() === true || ($hand->getId() === $block->getId() && ($block->getId() === Item::SLAB || $block->getId() === Item::STONE_SLAB2)))) {
			return false;
		}

		if ($target->canBeReplaced() === true) {
			$block = $target;
			$hand->position($block);
			//$face = -1;
		}

		if ($hand->isSolid() === true && $hand->getBoundingBox() !== null) {
			$entities = $this->getCollidingEntities($hand->getBoundingBox());
			$realCount = 0;
			foreach ($entities as $e) {
				if ($e instanceof Arrow or $e instanceof DroppedItem) {
					continue;
				}
				if ($e instanceof Player && $e->isSpectator()) {
					continue;
				}
				if ($e === $player) {
					if ($player->onGround) {
						$dy = $player->getY() - $hand->getY();
						if ($dy > 0.75 || $dy < - 1.5) {
							continue;
						}
					} else {
						$bb = clone $hand->getBoundingBox();
						$bb->contract(0.2, 0.25, 0.2);
						if (!$e->boundingBox->intersectsWith($bb)) {
							continue;
						}
					}
				}
				++$realCount;
			}
			if ($realCount > 0) {
				return false; //Entity in block
			}
		}

		if ($player instanceof Player) {
			if ($player->isSpectator() || !$player->canBuildBlocks()) {
				return false;
			}
			$ev = new BlockPlaceEvent($player, $hand, $block, $target, $item);
			if (!$player->isOp() && ($distance = $this->server->getConfigInt("spawn-protection", 16)) > -1) {
				$t = new Vector2($target->x, $target->z);
				$s = new Vector2($this->getSpawnLocation()->x, $this->getSpawnLocation()->z);
				if ($t->distance($s) <= $distance) { //set it to cancelled so plugins can bypass this
					$ev->setCancelled();
				}
			}
			$this->server->getPluginManager()->callEvent($ev);
			if($ev->isCancelled()){
				return false;
			}
		}

		if ($hand->place($item, $block, $target, $face, $fx, $fy, $fz, $player) === false) {
			return false;
		}
		$position = [ 'x' => $target->x, 'y' => $target->y, 'z' => $target->z ];
		$blockId = $hand->getId();
		$viewers = $player->getViewers();
		$viewers[] = $player;
		$player->sendSound(LevelSoundEventPacket::SOUND_PLACE, $position, MultiversionEntity::ID_NONE, $blockId, $viewers);

		if ($hand->getId() === Item::SIGN_POST or $hand->getId() === Item::WALL_SIGN) {
			$tile = Tile::createTile("Sign", $this->getChunk($block->x >> 4, $block->z >> 4), new Compound(false, [
				"id" => new StringTag("id", Tile::SIGN),
				"x" => new IntTag("x", $block->x),
				"y" => new IntTag("y", $block->y),
				"z" => new IntTag("z", $block->z),
				"Text1" => new StringTag("Text1", ""),
				"Text2" => new StringTag("Text2", ""),
				"Text3" => new StringTag("Text3", ""),
				"Text4" => new StringTag("Text4", "")
			]));
			if ($player instanceof Player) {
				$tile->namedtag->Creator = new StringTag("Creator", $player->getName());
			}
		}
		$item->setCount($item->getCount() - 1);
		if ($item->getCount() <= 0) {
			$item = Item::get(Item::AIR, 0, 0);
		}

		return true;
	}

	/**
	 * @param int $entityId
	 *
	 * @return Entity
	 */
	public function getEntity($entityId){
		return isset($this->entities[$entityId]) ? $this->entities[$entityId] : null;
	}

	/**
	 * Gets the list of all the entities in this level
	 *
	 * @return Entity[]
	 */
	public function getEntities(){
		return $this->entities;
	}

	/**
	 * Returns the entities colliding the current one inside the AxisAlignedBB
	 *
	 * @param AxisAlignedBB $bb
	 * @param Entity        $entity
	 *
	 * @return Entity[]
	 */
	public function getCollidingEntities(AxisAlignedBB $bb, Entity $entity = null){
		$nearby = [];

		if($entity === null or $entity->canCollide){
			$minX = Math::floorFloat(($bb->minX - 2) / 16);
			$maxX = Math::floorFloat(($bb->maxX + 2) / 16);
			$minZ = Math::floorFloat(($bb->minZ - 2) / 16);
			$maxZ = Math::floorFloat(($bb->maxZ + 2) / 16);

			for($x = $minX; $x <= $maxX; ++$x){
				for($z = $minZ; $z <= $maxZ; ++$z){
					foreach((($______chunk = $this->getChunk($x,  $z)) !== null ? $______chunk->getEntities() : []) as $ent){
						if($ent !== $entity and ($entity === null or $entity->canCollideWith($ent)) and $ent->boundingBox->intersectsWith($bb)){
							$nearby[] = $ent;
						}
					}
				}
			}
		}

		return $nearby;
	}

	/**
	 * Returns the entities near the current one inside the AxisAlignedBB
	 *
	 * @param AxisAlignedBB $bb
	 * @param Entity        $entity
	 *
	 * @return Entity[]
	 */
	public function getNearbyEntities(AxisAlignedBB $bb, Entity $entity = null){
		$nearby = [];

		$minX = Math::floorFloat(($bb->minX - 2) / 16);
		$maxX = Math::floorFloat(($bb->maxX + 2) / 16);
		$minZ = Math::floorFloat(($bb->minZ - 2) / 16);
		$maxZ = Math::floorFloat(($bb->maxZ + 2) / 16);

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				foreach((($______chunk = $this->getChunk($x,  $z)) !== null ? $______chunk->getEntities() : []) as $ent){
					if($ent !== $entity and $ent->boundingBox->intersectsWith($bb)){
						$nearby[] = $ent;
					}
				}
			}
		}

		return $nearby;
	}

	/**
	 * Returns a list of the Tile entities in this level
	 *
	 * @return Tile[]
	 */
	public function getTiles(){
		return $this->tiles;
	}

	/**
	 * @param $tileId
	 *
	 * @return Tile
	 */
	public function getTileById($tileId){
		return isset($this->tiles[$tileId]) ? $this->tiles[$tileId] : null;
	}

	/**
	 * Returns a list of the players in this level
	 *
	 * @return Player[]
	 */
	public function getPlayers(){
		return $this->players;
	}

	/**
	 * Returns the Tile in a position, or null if not found
	 *
	 * @param Vector3 $pos
	 *
	 * @return Tile
	 */
	public function getTile(Vector3 $pos){
		$chunk = $this->getChunk($pos->x >> 4, $pos->z >> 4, false);

		if($chunk !== null){
			return $chunk->getTile($pos->x & 0x0f, $pos->y & 0xff, $pos->z & 0x0f);
		}

		return null;
	}

	/**
	 * Returns a list of the entities on a given chunk
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Entity[]
	 */
	public function getChunkEntities($X, $Z){
		return ($chunk = $this->getChunk($X, $Z)) !== null ? $chunk->getEntities() : [];
	}

	/**
	 * Gives a list of the Tile entities on a given chunk
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Tile[]
	 */
	public function getChunkTiles($X, $Z){
		return ($chunk = $this->getChunk($X, $Z)) !== null ? $chunk->getTiles() : [];
	}

	/**
	 * Gets the raw block id.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int 0-255
	 */
	public function getBlockIdAt($x, $y, $z){
		return $y > $this->getYMask() ? 0 : $this->getChunk($x >> 4, $z >> 4, true)->getBlockId($x & 0x0f, $y & $this->getYMask(), $z & 0x0f);
	}

	/**
	 * Sets the raw block id.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $id 0-255
	 */
	public function setBlockIdAt($x, $y, $z, $id){
		unset($this->blockCache[self::blockHash($x, $y, $z)]);
		$this->getChunk($x >> 4, $z >> 4, true)->setBlockId($x & 0x0f, $y & $this->getYMask(), $z & 0x0f, $id & 0xff);
	}

	/**
	 * Gets the raw block metadata
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int 0-15
	 */
	public function getBlockDataAt($x, $y, $z){
		return $y > $this->getYMask() ? 0 : $this->getChunk($x >> 4, $z >> 4, true)->getBlockData($x & 0x0f, $y & $this->getYMask(), $z & 0x0f);
	}

	/**
	 * Sets the raw block metadata.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $data 0-15
	 */
	public function setBlockDataAt($x, $y, $z, $data){
		unset($this->blockCache[self::blockHash($x, $y, $z)]);
		$this->getChunk($x >> 4, $z >> 4, true)->setBlockData($x & 0x0f, $y & $this->getYMask(), $z & 0x0f, $data & 0x0f);
	}

	/**
	 * Gets the raw block skylight level
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int 0-15
	 */
	public function getBlockSkyLightAt($x, $y, $z){
		return $this->getChunk($x >> 4, $z >> 4, true)->getBlockSkyLight($x & 0x0f, $y & $this->getYMask(), $z & 0x0f);
	}

	/**
	 * Sets the raw block skylight level.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $level 0-15
	 */
	public function setBlockSkyLightAt($x, $y, $z, $level){
		$this->getChunk($x >> 4, $z >> 4, true)->setBlockSkyLight($x & 0x0f, $y & $this->getYMask(), $z & 0x0f, $level & 0x0f);
	}

	/**
	 * Gets the raw block light level
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int 0-15
	 */
	public function getBlockLightAt($x, $y, $z){
		return $this->getChunk($x >> 4, $z >> 4, true)->getBlockLight($x & 0x0f, $y & $this->getYMask(), $z & 0x0f);
	}

	/**
	 * Sets the raw block light level.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $level 0-15
	 */
	public function setBlockLightAt($x, $y, $z, $level){
		$this->getChunk($x >> 4, $z >> 4, true)->setBlockLight($x & 0x0f, $y & $this->getYMask(), $z & 0x0f, $level & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int
	 */
	public function getBiomeId($x, $z){
		return $this->getChunk($x >> 4, $z >> 4, true)->getBiomeId($x & 0x0f, $z & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int[]
	 */
	public function getBiomeColor($x, $z){
		return $this->getChunk($x >> 4, $z >> 4, true)->getBiomeColor($x & 0x0f, $z & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int
	 */
	public function getHeightMap($x, $z){
		return $this->getChunk($x >> 4, $z >> 4, true)->getHeightMap($x & 0x0f, $z & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $biomeId
	 */
	public function setBiomeId($x, $z, $biomeId){
		$this->getChunk($x >> 4, $z >> 4, true)->setBiomeId($x & 0x0f, $z & 0x0f, $biomeId);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $R
	 * @param int $G
	 * @param int $B
	 */
	public function setBiomeColor($x, $z, $R, $G, $B){
		$this->getChunk($x >> 4, $z >> 4, true)->setBiomeColor($x & 0x0f, $z & 0x0f, $R, $G, $B);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $value
	 */
	public function setHeightMap($x, $z, $value){
		$this->getChunk($x >> 4, $z >> 4, true)->setHeightMap($x & 0x0f, $z & 0x0f, $value);
	}

	/**
	 * @return FullChunk[]|Chunk[]
	 */
	public function getChunks(){
		return $this->chunks;
	}

	/**
	 * Gets the Chunk object
	 *
	 * @param int  $x
	 * @param int  $z
	 * @param bool $create Whether to generate the chunk if it does not exist
	 *
	 * @return FullChunk|Chunk
	 */
	public function getChunk($x, $z, $create = false){
		if(isset($this->chunks[$index = self::chunkHash($x, $z)])){
			return $this->chunks[$index];
		}elseif($this->loadChunk($x, $z, $create) and $this->chunks[$index] !== null){
			return $this->chunks[$index];
		}

		return null;
	}

	/**
	 * @param int  $x
	 * @param int  $z
	 * @param bool $create
	 *
	 * @return FullChunk|Chunk
	 *
	 * @deprecated
	 */
	public function getChunkAt($x, $z, $create = false){
		return $this->getChunk($x, $z, $create);
	}

	public function getChunkLoaders(int $chunkX, int $chunkZ){
		return isset($this->chunkLoaders[$index = Level::chunkHash($chunkX, $chunkZ)]) ? $this->chunkLoaders[$index] : [];
	}

    /**
     * Returns the chunks adjacent to the specified chunk.
     *
     * @return (Chunk|null)[]
     */
    public function getAdjacentChunks($x, $z) {
        $result = [];
        for ($xx = 0; $xx <= 2; ++$xx) {
            for ($zz = 0; $zz <= 2; ++$zz) {
                $i = $zz * 3 + $xx;
                if ($i === 4) {
                    continue; //center chunk
                }
                $result[$i] = $this->getChunk($x + $xx - 1, $z + $zz - 1, false);
            }
        }

        return $result;
    }

	public function generateChunkCallback($x, $z, FullChunk $chunk){
		if ($this->closed || is_null($this->generator)) {
			return;
		}

        Timings::$generationCallbackTimer->startTiming();

		$oldChunk = $this->getChunk($x, $z, false);
		$index = Level::chunkHash($x, $z);

		for($xx = -1; $xx <= 1; ++$xx){
			for($zz = -1; $zz <= 1; ++$zz){
				unset($this->chunkPopulationLock[Level::chunkHash($x + $xx, $z + $zz)]);
			}
		}
		unset($this->chunkPopulationQueue[$index]);
		unset($this->chunkGenerationQueue[$index]);

		$chunk->setProvider($this->provider);
		$this->setChunk($x, $z, $chunk);
		$chunk = $this->getChunk($x, $z, false);
		if($chunk !== null and ($oldChunk === null or $oldChunk->isPopulated() === false) and $chunk->isPopulated()){
			$this->server->getPluginManager()->callEvent(new ChunkPopulateEvent($chunk));
		}

        Timings::$generationCallbackTimer->stopTiming();
	}

	public function setChunk($x, $z, FullChunk $chunk, $unload = true){
		$index = self::chunkHash($x, $z);
		if($unload){
			foreach($this->getUsingChunk($x, $z) as $player){
				$player->unloadChunk($x, $z);
			}
			$this->provider->setChunk($x, $z, $chunk);
			$this->chunks[$index] = $chunk;
		}else{
			$this->provider->setChunk($x, $z, $chunk);
			$this->chunks[$index] = $chunk;
		}
		$this->chunkCacheClear($x, $z);
		$chunk->setChanged();
	}

	/**
	 * Gets the highest block Y value at a specific $x and $z
	 *
	 * @param int $x
	 * @param int $z
	 *
	 * @return int 0-127
	 */
	public function getHighestBlockAt($x, $z){
		return $this->getChunk($x >> 4, $z >> 4, true)->getHighestBlockAt($x & 0x0f, $z & 0x0f);
	}

    public function canBlockSeeSky(Vector3 $pos){
        return $this->getHighestBlockAt($pos->getFloorX(), $pos->getFloorZ()) < $pos->getY();
    }
	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkLoaded($x, $z){
		return isset($this->chunks[self::chunkHash($x, $z)]) or $this->provider->isChunkLoaded($x, $z);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkGenerated($x, $z){
		$chunk = $this->getChunk($x, $z);
		return $chunk !== null ? $chunk->isGenerated() : false;
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkPopulated($x, $z){
		$chunk = $this->getChunk($x, $z);
		return $chunk !== null ? $chunk->isPopulated() : false;
	}

	/**
	 * Returns a Position pointing to the spawn
	 *
	 * @return Position
	 */
	public function getSpawnLocation(){
		return Position::fromObject($this->provider->getSpawn(), $this);
	}

	/**
	 * Sets the level spawn location
	 *
	 * @param Vector3 $pos
	 */
	public function setSpawnLocation(Vector3 $pos){
		$previousSpawn = $this->getSpawnLocation();
		$this->provider->setSpawn($pos);
		$this->server->getPluginManager()->callEvent(new SpawnChangeEvent($this, $previousSpawn));
	}

	public function requestChunk($x, $z, $player) {
        $this->timings->syncChunkSendTimer->startTiming();

		$protocol = Network::getChunkPacketProtocol($player->getPlayerProtocol());
		$chunkIndex = Level::chunkHash($x, $z);

		if (isset($this->chunkCache[$chunkIndex][$protocol])) {
		    $player->useChunk($x, $z);
		    $player->dataPacket($this->chunkCache[$chunkIndex][$protocol]);
		    return;
		}

		$data = $this->provider->requestChunkTask($x, $z);
		$player->useChunk($x, $z);
		$chunk = $this->doChunk($player, $x, $z, $protocol, $data);
		$this->chunkCache[$chunkIndex][$protocol] = $chunk;

        $this->timings->syncChunkSendTimer->stopTiming();
	}

	private function sortData($data) {
		$result = str_repeat("\x00", 4096);
		if ($data !== $result) {
			$i = 0;
			for ($x = 0; $x < 16; ++$x) {
				$zM = $x + 256;
				for ($z = $x; $z < $zM; $z += 16) {
					$yM = $z + 4096;
					for ($y = $z; $y < $yM; $y += 256) {
						$result[$i] = $data[$y];
						++$i;
					}
				}
			}
		}
		return $result;
	}

	private function sortHalfData($data) {
		$result = str_repeat("\x00", 2048);
		if ($data !== $result) {
			$i = 0;
			for ($x = 0; $x < 8; ++$x) {
				for ($z = 0; $z < 16; ++$z) {
					$zx = (($z << 3) | $x);
					for ($y = 0; $y < 8; ++$y) {
						$j = (($y << 8) | $zx);
						$j80 = ($j | 0x80);
						$i1 = ord($data[$j]);
						$i2 = ord($data[$j80]);
						$result[$i] = chr(($i2 << 4) | ($i1 & 0x0f));
						$result[$i | 0x80] = chr(($i1 >> 4) | ($i2 & 0xf0));
						$i++;
					}
				}
				$i += 128;
			}
		}
		return $result;
	}

	public function chunkCacheClear($x, $z){
		$chunkIndex = Level::chunkHash($x, $z);
		unset($this->chunkCache[$chunkIndex]);
	}

	/**
	 * Removes the entity from the level index
	 *
	 * @param Entity $entity
	 *
	 * @throws LevelException
	 */
	public function removeEntity(Entity $entity){
		if($entity->getLevel() !== $this){
			throw new LevelException("Invalid Entity level");
		}

		if($entity instanceof Player){
			unset($this->players[$entity->getId()]);
			//$this->everyoneSleeping();
		}

		unset($this->entities[$entity->getId()]);
		unset($this->updateEntities[$entity->getId()]);
	}

	/**
	 * @param Entity $entity
	 *
	 * @throws LevelException
	 */
	public function addEntity(Entity $entity){
		if($entity->getLevel() !== $this){
			throw new LevelException("Invalid Entity level");
		}
		if($entity instanceof Player){
			$this->players[$entity->getId()] = $entity;
		}
		$this->entities[$entity->getId()] = $entity;
	}

	/**
	 * @param Tile $tile
	 *
	 * @throws LevelException
	 */
	public function addTile(Tile $tile){
		if($tile->getLevel() !== $this){
			throw new LevelException("Invalid Tile level");
		}
		$this->tiles[$tile->getId()] = $tile;
	}

	/**
	 * @param Tile $tile
	 *
	 * @throws LevelException
	 */
	public function removeTile(Tile $tile){
		if($tile->getLevel() !== $this){
			throw new LevelException("Invalid Tile level");
		}

		unset($this->tiles[$tile->getId()]);
		unset($this->updateTiles[$tile->getId()]);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkInUse($x, $z){
		$someIndex = self::chunkHash($x, $z);
		return isset($this->usedChunks[$someIndex]) && count($this->usedChunks[$someIndex]) > 0;
	}

    public function regenerateChunk($x, $z){
        $this->unloadChunk($x, $z, false);

        $this->cancelUnloadChunkRequest($x, $z);

        $this->generateChunk($x, $z);
        //TODO: generate & refresh chunk from the generator object
    }

	/**
	 * @param int  $x
	 * @param int  $z
	 * @param bool $generate
	 *
	 * @return bool
	 */
	public function loadChunk($x, $z, $generate = true){
		if(isset($this->chunks[$index = self::chunkHash($x, $z)])){
			return true;
		}

        $this->timings->syncChunkLoadTimer->startTiming();

		$this->cancelUnloadChunkRequest($x, $z);

		$chunk = $this->provider->getChunk($x, $z, $generate);
		if($chunk !== null){
			$this->chunks[$index] = $chunk;
			$chunk->initChunk();
		}else{
			$this->provider->loadChunk($x, $z, $generate);

			if(($chunk = $this->provider->getChunk($x, $z)) !== null){
				$this->chunks[$index] = $chunk;
				$chunk->initChunk();
			}else{
			    $this->timings->syncChunkLoadTimer->stopTiming();
				return false;
			}
		}

		$this->server->getPluginManager()->callEvent(new ChunkLoadEvent($chunk, !$chunk->isGenerated()));

        $this->timings->syncChunkLoadTimer->stopTiming();

		return true;
	}

	protected function queueUnloadChunk($x, $z){
		$this->unloadQueue[$index = self::chunkHash($x, $z)] = microtime(true);
		unset($this->chunkTickList[$index]);
	}

	public function unloadChunkRequest($x, $z, $safe = true){
		if(($safe === true and $this->isChunkInUse($x, $z)) or $this->isSpawnChunk($x, $z)){
			return false;
		}

		$this->queueUnloadChunk($x, $z);

		return true;
	}

	public function cancelUnloadChunkRequest($x, $z){
		unset($this->unloadQueue[self::chunkHash($x, $z)]);
	}

	public function unloadChunk($x, $z, $safe = true){
		if($this->isFrozen || ($safe === true and $this->isChunkInUse($x, $z))){
			return false;
		}

		$this->timings->doChunkUnload->startTiming();

		$index = self::chunkHash($x, $z);
		if (isset($this->chunks[$index])) {
			$chunk = $this->chunks[$index];
		} else {
			unset($this->chunks[$index]);
			unset($this->usedChunks[$index]);
			unset($this->chunkTickList[$index]);
			$this->chunkCacheClear($x, $z);
			$this->timings->doChunkUnload->stopTiming();
			return true;
		}

		if($chunk !== null){
			/* @var BaseFullChunk $chunk */
			if(!$chunk->isAllowUnload() && !$this->closed) {
				$this->timings->doChunkUnload->stopTiming();
				return false;
			}

			$this->server->getPluginManager()->callEvent($ev = new ChunkUnloadEvent($chunk, $safe));
			if($ev->isCancelled()){
				$this->timings->doChunkUnload->stopTiming();
				return false;
			}

			try{
				if ($ev->shouldSave()) {
					foreach ($chunk->getEntities() as $entity) {
						if ($entity instanceof Player) {
							continue;
						}
						if (!$entity->isNeedSaveOnChunkUnload()) {
							$entity->close();
						}
					}
					$this->timings->syncChunkSaveTimer->startTiming();
					if ($this->getAutoSave()) {
						$this->provider->setChunk($x, $z, $chunk);
						$this->provider->saveChunk($x, $z);
					}
					$this->timings->syncChunkSaveTimer->stopTiming();
				}
				$this->provider->unloadChunk($x, $z, $safe);
			}catch(\Exception $e){
				$logger = $this->server->getLogger();
				$logger->error("Error when unloading a chunk: " . $e->getMessage());
				if($logger instanceof MainLogger){
					$logger->logException($e);
				}
			}
		}

		unset($this->chunks[$index]);
		unset($this->usedChunks[$index]);
		unset($this->chunkTickList[$index]);
		$this->chunkCacheClear($x, $z);

		$this->timings->doChunkUnload->stopTiming();

		return true;
	}

	/**
	 * Returns true if the spawn is part of the spawn
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return bool
	 */
	public function isSpawnChunk($X, $Z){
		$spawnX = $this->provider->getSpawn()->getX() >> 4;
		$spawnZ = $this->provider->getSpawn()->getZ() >> 4;

		return abs($X - $spawnX) <= 1 and abs($Z - $spawnZ) <= 1;
	}

	/**
	 * Returns the raw spawnpoint
	 *
	 * @deprecated
	 * @return Position
	 */
	public function getSpawn(){
		return $this->getSpawnLocation();
	}

	/**
	 * @param Vector3 $spawn default null
	 *
	 * @return bool|Position
	 */
	public function getSafeSpawn($spawn = null){
		if(!($spawn instanceof Vector3) || $spawn->y < 1){
			$spawn = $this->getSpawnLocation();
		}
		if($spawn instanceof Vector3){
			$v = $spawn->floor();
			$chunk = $this->getChunk($v->x >> 4, $v->z >> 4, false);
			$x = $v->x & 0x0f;
			$z = $v->z & 0x0f;
			if($chunk !== null){
				for(; $v->y > 0; --$v->y){
					if($v->y < ($this->getMaxY() - 1) and Block::$solid[$chunk->getBlockId($x, $v->y & $this->getYMask(), $z)]){
						$v->y++;
						break;
					}
				}
				for(; $v->y < $this->getMaxY(); ++$v->y){
					if(!Block::$solid[$chunk->getBlockId($x, $v->y + 1, $z)]){
						if(!Block::$solid[$chunk->getBlockId($x, $v->y, $z)]){
							return new Position($spawn->x, $v->y === Math::floorFloat($spawn->y) ? $spawn->y : $v->y, $spawn->z, $this);
						}
					}else{
						++$v->y;
					}
				}
			}

			return new Position($spawn->x, $v->y, $spawn->z, $this);
		}

		return false;
	}

	/**
	 * Sets the spawnpoint
	 *
	 * @param Vector3 $pos
	 *
	 * @deprecated
	 */
	public function setSpawn(Vector3 $pos){
		$this->setSpawnLocation($pos);
	}

	/**
	 * Gets the current time
	 *
	 * @return int
	 */
	public function getTime(){
		return (int) $this->time;
	}

	/**
	 * Returns the Level name
	 *
	 * @return string
	 */
	public function getName(){
		return $this->provider->getName();
	}

	/**
	 * Returns the Level folder name
	 *
	 * @return string
	 */
	public function getFolderName(){
		return $this->folderName;
	}

	/**
	 * Sets the current time on the level
	 *
	 * @param int $time
	 */
	public function setTime($time){
		$this->time = (int) $time;
		$this->sendTime();
	}

	/**
	 * Stops the time for the level, will not save the lock state to disk
	 */
	public function stopTime(){
		$this->stopTime = true;
		$this->sendTime();
		foreach ($this->players as $player) {
			$player->setDaylightCycle(!$this->stopTime);
		}
	}

	/**
	 * Start the time again, if it was stopped
	 */
	public function startTime(){
		$this->stopTime = false;
		$this->sendTime();
		foreach ($this->players as $player) {
			$player->setDaylightCycle(!$this->stopTime);
		}
	}

	/**
	 * Gets the level seed
	 *
	 * @return int
	 */
	public function getSeed(){
		return $this->provider->getSeed();
	}

	/**
	 * Sets the seed for the level
	 *
	 * @param int $seed
	 */
	public function setSeed($seed){
		$this->provider->setSeed($seed);
	}



	public function generateChunk(int $x, int $z, bool $force = false){
		if (is_null($this->generator)) {
			return;
		}
		if(count($this->chunkGenerationQueue) >= $this->chunkGenerationQueueSize and !$force){
			return;
		}
		if(!isset($this->chunkGenerationQueue[$index = Level::chunkHash($x, $z)])){
			$this->chunkGenerationQueue[$index] = true;
			$task = new GenerationTask($this, $this->getChunk($x, $z, true));
			$this->server->getScheduler()->scheduleAsyncTask($task);
		}
	}

	public function registerGenerator(){
		if (!is_null($this->generator)) {
			$size = $this->server->getScheduler()->getAsyncTaskPoolSize();
			for($i = 0; $i < $size; ++$i){
				$this->server->getScheduler()->scheduleAsyncTaskToWorker(new GeneratorRegisterTask($this,  $this->generatorInstance), $i);
			}
		}
	}

	public function unregisterGenerator(){
		if (!is_null($this->generator)) {
			$size = $this->server->getScheduler()->getAsyncTaskPoolSize();
			for($i = 0; $i < $size; ++$i){
				$this->server->getScheduler()->scheduleAsyncTaskToWorker(new GeneratorUnregisterTask($this,  $this->generatorInstance), $i);
			}
		}
	}

	public function doChunkGarbageCollection(){
		if(!$this->isFrozen) {
			$this->timings->doChunkGC->startTiming();

			$X = null;
			$Z = null;

			foreach($this->chunks as $index => $chunk){
				if(!isset($this->unloadQueue[$index]) and (!isset($this->usedChunks[$index]) or count($this->usedChunks[$index]) === 0)){
					self::getXZ($index, $X, $Z);
					if(!$this->isSpawnChunk($X, $Z)){
						$this->unloadChunkRequest($X, $Z, true);
					}
				}
			}

			foreach($this->provider->getLoadedChunks() as $chunk){
				if(!isset($this->chunks[self::chunkHash($chunk->getX(), $chunk->getZ())])){
					$this->provider->unloadChunk($chunk->getX(), $chunk->getZ(), false);
				}
			}

			$this->provider->doGarbageCollection();

			$this->timings->doChunkGC->stopTiming();
		}
	}

	public function unloadChunks($force = false){
        if(count($this->unloadQueue) > 0 && !$this->isFrozen){
            $maxUnload = 96;
            $now = microtime(true);
            foreach($this->unloadQueue as $index => $time){
                Level::getXZ($index, $X, $Z);

                if(!$force){
                    if($maxUnload <= 0){
                        break;
                    }elseif($time > ($now - 30)){
                        continue;
                    }
                }

                //If the chunk can't be unloaded, it stays on the queue
                if ($this->unloadChunk($X, $Z, true)) {
                    unset($this->unloadQueue[$index]);
                    --$maxUnload;
                }
            }
        }
	}

	public function freezeMap(){
		$this->isFrozen = true;
	}

	public function unfreezeMap(){
		$this->isFrozen = false;
	}

	public function setMetadata($metadataKey, MetadataValue $metadataValue){
		$this->server->getLevelMetadata()->setMetadata($this, $metadataKey, $metadataValue);
	}

	public function getMetadata($metadataKey){
		return $this->server->getLevelMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata($metadataKey){
		return $this->server->getLevelMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata($metadataKey, Plugin $plugin){
		$this->server->getLevelMetadata()->removeMetadata($this, $metadataKey, $plugin);
	}

	public function addEntityMotion($viewers, $entityId, $x, $y, $z){
		if (empty($viewers)) {
			return;
		}

		$singleMotionData = [$entityId, $x, $y, $z];

		$pk = new SetEntityMotionPacket();
		$pk->entities = [$singleMotionData];

		$this->server->broadcastPacket($viewers, $pk);
	}

	public function addEntityMovement($viewers, $entityId, $x, $y, $z, $yaw, $pitch, $headYaw = null, $isPlayer = false){
		if (empty($viewers)) {
			return;
		}

		$singleMoveData = [$entityId, $x, $y, $z, $yaw, $headYaw === null ? $yaw : $headYaw, $pitch];

		if ($isPlayer) {
			$pk = new MovePlayerPacket();
			$pk->eid = $singleMoveData[0];
			$pk->x = $singleMoveData[1];
			$pk->y = $singleMoveData[2];
			$pk->z = $singleMoveData[3];
			$pk->pitch = $singleMoveData[6];
			$pk->yaw = $singleMoveData[5];
			$pk->bodyYaw = $singleMoveData[4];
			$this->server->broadcastPacket($viewers, $pk);
		} else {
		    foreach ($viewers as $p) {
		    	$pk = new MoveEntityPacket();
		    	$pk->entities = [$singleMoveData];
			    $p->dataPacket($pk);
		    }
		}
	}

	public function addPlayerHandItem($sender, $recipient){
		if(!isset($this->playerHandItemQueue[$sender->getId()])){
			$this->playerHandItemQueue[$sender->getId()] = [];
		}
		$this->playerHandItemQueue[$sender->getId()][$recipient->getId()] = array(
			'sender' => $sender,
			'recipient' => $recipient,
			'time' => microtime(true)
		);

	}

	public function mayAddPlayerHandItem($sender, $recipient){
		if(isset($this->playerHandItemQueue[$sender->getId()][$recipient->getId()])){
			return false;
		}
		return true;
	}


	public function populateChunk(int $x, int $z, bool $force = false){
		if(isset($this->chunkPopulationQueue[$index = Level::chunkHash($x, $z)]) or (count($this->chunkPopulationQueue) >= $this->chunkPopulationQueueSize and !$force)){
			return false;
		}

        for($xx = -1; $xx <= 1; ++$xx){
			for($zz = -1; $zz <= 1; ++$zz){
				if(isset($this->chunkPopulationLock[Level::chunkHash($x + $xx, $z + $zz)])){
					return false;
				}
			}
		}

		$chunk = $this->getChunk($x, $z, true);
		if(!$chunk->isPopulated()){
            Timings::$populationTimer->startTiming();

			$this->chunkPopulationQueue[$index] = true;
			for($xx = -1; $xx <= 1; ++$xx){
				for($zz = -1; $zz <= 1; ++$zz){
					$this->chunkPopulationLock[Level::chunkHash($x + $xx, $z + $zz)] = true;
				}
			}

			$task = new PopulationTask($this, $chunk);
			$this->server->getScheduler()->scheduleAsyncTask($task);

            Timings::$populationTimer->stopTiming();

			return false;
		}

		return true;
	}

	public function getYMask() {
		return $this->yMask;
	}

	public function getMaxY() {
		return $this->maxY;
	}

	public function getUpdateQueue() {
		return $this->updateQueue;
	}

	public function isClosed() {
		return $this->closed;
	}

	public function isNight(){
		if($this->time >= Level::TIME_NIGHT and $this->time < Level::TIME_SUNRISE){
			return true;
		}
		return false;
	}

	protected function doChunk($p, $chunkX, $chunkZ, $protocol, $data){
		$isAnvil = isset($data['isAnvil']) && $data['isAnvil'] == true;

		$subChunkCount = $isAnvil ? count($data['chunk']['sections']) : 8;

        if($protocol >= Info::PROTOCOL_360){
	    	$chunkData = "";
        }

		if($protocol >= Info::PROTOCOL_475){
			//TODO: HACK! fill in fake subchunks to make up for the new negative space client-side
			for($y = 0; $y < 4; $y++){
				$subChunkCount++;
				$chunkData .= chr(8); //subchunk version 8
				$chunkData .= chr(0); //0 layers - client will treat this as all-air
			}
		}

		if ($isAnvil) {
		    if($protocol < Info::PROTOCOL_360){
		    	$chunkData = chr(count($data['chunk']['sections']));
		    }

			foreach ($data['chunk']['sections'] as $y => $sections) {
				if ($sections['empty'] == true) {
				    $chunkData .= $protocol >= Info::PROTOCOL_120 ? "\x00" . str_repeat("\x00", 6144) : "\x00" . str_repeat("\x00", 10240);
				} else {
					if ($protocol >= Info::PROTOCOL_120) {
						$chunkData .= isset($data['isSorted']) && $data['isSorted'] == true ? "\x00" . $sections['blocks'] . $sections['data'] : "\x00" . $this->sortData($sections['blocks']) . $this->sortHalfData($sections['data']);
					} else {
						$chunkData .= isset($data['isSorted']) && $data['isSorted'] == true ? "\x00" . $sections['blocks'] . $sections['data'] . $sections['skyLight'] . $sections['blockLight'] : "\x00" . $this->sortData($sections['blocks']) . $this->sortHalfData($sections['data']) . $this->sortHalfData($sections['skyLight']) . $this->sortHalfData($sections['blockLight']);
					}
				}
			}

			if ($protocol < Info::PROTOCOL_360) {
				$chunkData .= $data['chunk']['heightMap'];
			}

			$biomes = $protocol >= Info::PROTOCOL_360 ? $data["chunk"]["biomeColor"] : $data['chunk']['biomeColor'] . Binary::writeByte(0) . Binary::writeSignedVarInt(0) . implode('', $data['tiles']);
		} else {
			$blockIdArray = $data['blocks'];
			$blockDataArray = $data['data'];
			$countBlocksInChunk = 8;
			if ($protocol >= Info::PROTOCOL_120) {
				if($protocol < Info::PROTOCOL_360){
				    $chunkData = chr($countBlocksInChunk);
				}

				for ($blockIndex = 0; $blockIndex < $countBlocksInChunk; $blockIndex++) {
					$blockIdData = '';
					$blockDataData = '';
					for ($i = 0; $i < 256; $i++) {
						$startIndex = ($blockIndex + ($i << 3)) << 3;
						$blockIdData .= substr($blockIdArray, $startIndex << 1, 16);
						$blockDataData .= substr($blockDataArray, $startIndex, 8);
					}
					$blockData = "\x00" . $blockIdData . $blockDataData;
					$chunkData .= $blockData;
				}
			} else {
				$skyLightArray = $data['skyLight'];
				$blockLightArray = $data['blockLight'];
				$chunkData = chr($countBlocksInChunk);
				for ($blockIndex = 0; $blockIndex < $countBlocksInChunk; $blockIndex++) {
					$blockIdData = '';
					$blockDataData = '';
					$skyLightData = '';
					$blockLightData = '';
					for ($i = 0; $i < 256; $i++) {
						$startIndex = ($blockIndex + ($i << 3)) << 3;
						$blockIdData .= substr($blockIdArray, $startIndex << 1, 16);
						$blockDataData .= substr($blockDataArray, $startIndex, 8);
						$skyLightData .= substr($skyLightArray, $startIndex, 8);
						$blockLightData .= substr($blockLightArray, $startIndex, 8);
					}
					$chunkData .= "\x00" . $blockIdData . $blockDataData . $skyLightData . $blockLightData;
				}
			}

			$biomes = $protocol >= Info::PROTOCOL_360 ? $data["biomeColor"] : $data['heightMap'] . $data['biomeColor'] . Binary::writeLInt(0) . implode('', $data['tiles']);
		}

		if($protocol >= Info::PROTOCOL_475){
			for($i = 0; $i < 25; ++$i){
				$chunkData .= chr(0); //fake biome palette - 0 bpb, non-persistent
				$chunkData .= Binary::writeVarInt($this->getBiomeId($chunkX, $chunkZ) << 1); //fill plains for now
			}
		}else{
			$chunkData .= $biomes;
		}

		if($protocol >= Info::PROTOCOL_360){
	    	$chunkData .= Binary::writeByte(0) . implode('', $data['tiles']);
		}

		$pk = new FullChunkDataPacket();
		$pk->chunkX = $chunkX;
		$pk->chunkZ = $chunkZ;
		$pk->subChunkCount = $subChunkCount;
		$pk->data = $chunkData;

		$pk->encode($protocol);

		$batch = new BatchPacket();
		$batch->payload = zlib_encode(Binary::writeVarInt(strlen($pk->getBuffer())) . $pk->getBuffer(), Player::getCompressAlg($protocol), 7);
		$p->dataPacket($batch);

        return $batch;
	}
}
