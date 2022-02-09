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

use const M_SQRT3;
use function max;
use function mt_rand;

use function rand;
use function random_int;
use pocketmine\network\protocol\{v370\StructureEditorData, TileEntityDataPacket, v403\EmotePacket};
use pocketmine\event\player\PlayerUseFishingRodEvent;
use pocketmine\entity\FishingHook;
use pocketmine\block\Block;
use pocketmine\command\CommandSender;
use pocketmine\entity\Arrow;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\event\server\{DataPacketReceiveEvent, DataPacketSendEvent};
use pocketmine\entity\BottleOEnchanting;
use pocketmine\entity\projectile\Egg;
use pocketmine\entity\projectile\FireBall;
use pocketmine\entity\projectile\Snowball;
use pocketmine\entity\Human;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\entity\Living;
use pocketmine\entity\Projectile;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerAnimationEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerReceiptsReceivedEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerRespawnAfterEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\TextContainer;
use pocketmine\event\Timings;
use pocketmine\inventory\BaseTransaction;
use pocketmine\inventory\BigShapedRecipe;
use pocketmine\inventory\BigShapelessRecipe;
use pocketmine\inventory\EnchantInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\inventory\SimpleTransactionGroup;
use pocketmine\inventory\transactions\SimpleTransactionData;
use pocketmine\item\{Item, Firework, FireworksData, FireworksExplosion};
use pocketmine\item\Armor;
use pocketmine\item\Tool;
use pocketmine\item\Potion;
use pocketmine\level\format\FullChunk;
use pocketmine\level\format\LevelProvider;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\level\sound\LaunchSound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Tag;
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
use pocketmine\network\protocol\AdventureSettingsPacket;
use pocketmine\network\protocol\AnimatePacket;
use pocketmine\network\protocol\BatchPacket;
use pocketmine\network\protocol\ContainerClosePacket;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\DisconnectPacket;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\network\protocol\FullChunkDataPacket;
use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\PEPacket;
use pocketmine\network\protocol\PlayerActionPacket;
use pocketmine\network\protocol\PlayStatusPacket;
use pocketmine\network\protocol\PlayerListPacket;
use pocketmine\network\protocol\RespawnPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\StrangePacket;
use pocketmine\network\protocol\TextPacket;
use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\network\protocol\SetDifficultyPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\network\protocol\SetSpawnPositionPacket;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\network\protocol\StartGamePacket;
use pocketmine\network\protocol\TakeItemEntityPacket;
use pocketmine\network\protocol\TransferPacket;
use pocketmine\network\protocol\UpdateAttributesPacket;
use pocketmine\network\protocol\SetHealthPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\protocol\ChunkRadiusUpdatePacket;
use pocketmine\network\protocol\InteractPacket;
use pocketmine\network\protocol\ResourcePackChunkDataPacket;
use pocketmine\network\protocol\ChangeDimensionPacket;
use pocketmine\network\SourceInterface;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissionAttachment;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\CallbackTask;
use pocketmine\tile\Sign;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;
use pocketmine\network\protocol\SetPlayerGameTypePacket;
use pocketmine\block\Liquid;
use pocketmine\network\protocol\SetCommandsEnabledPacket;
use pocketmine\network\protocol\AvailableCommandsPacket;
use pocketmine\network\protocol\ResourcePackDataInfoPacket;
use pocketmine\network\protocol\ResourcePacksInfoPacket;
use pocketmine\network\protocol\ResourcePackStackPacket;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Elytra;
use pocketmine\network\protocol\SetTitlePacket;
use pocketmine\network\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\protocol\LevelSoundEventPacket;

use pocketmine\network\protocol\v120\InventoryTransactionPacket;
use pocketmine\network\protocol\v120\Protocol120;
use pocketmine\inventory\PlayerInventory120;
use pocketmine\network\multiversion\Multiversion;
use pocketmine\network\multiversion\MultiversionEnums;
use pocketmine\network\protocol\LevelEventPacket;

use pocketmine\inventory\win10\Win10InvLogic;
use pocketmine\network\protocol\v120\ServerSettingsResponsetPacket;
use pocketmine\network\protocol\v120\PlayerSkinPacket;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\utils\{Binary, Utils};
use pocketmine\network\protocol\v310\NetworkChunkPublisherUpdatePacket;
use pocketmine\network\multiversion\Entity as MultiversionEntity;
use pocketmine\{event\block\ItemFrameDropItemEvent, network\protocol\ItemFrameDropItemPacket};
use pocketmine\entity\Vehicle;
use pocketmine\network\protocol\GameRulesChangedPacket;
use pocketmine\player\PlayerSettingsTrait;
use pocketmine\tile\ItemFrame;
use pocketmine\network\protocol\v331\BiomeDefinitionListPacket;
use pocketmine\network\protocol\v310\AvailableEntityIdentifiersPacket;
use pocketmine\network\protocol\ItemComponentPacket;
use pocketmine\network\protocol\v392\CreativeContentPacket;
use pocketmine\resourcepacks\ResourcePack;
/**
 * Main class that handles networking, recovery, and packet sending to the server part
 */
class Player extends Human implements CommandSender, InventoryHolder, IPlayer {

	use PlayerSettingsTrait;

    const OS_ANDROID = 1;
    const OS_IOS = 2;
    const OS_OSX = 3;
    const OS_FIREOS = 4;
    const OS_GEARVR = 5;
    const OS_HOLOLENS = 6;
    const OS_WIN10 = 7;
    const OS_WIN32 = 8;
    const OS_DEDICATED = 9;
    const OS_TVOS = 10;
    const OS_ORBIS = 11;
    const OS_NX = 12;
    const OS_UNKNOWN = -1;

    const INVENTORY_CLASSIC = 0;
    const INVENTORY_POCKET = 1;

	const SURVIVAL = 0;
	const CREATIVE = 1;
	const ADVENTURE = 2;
	const SPECTATOR = 3;
	const VIEW = Player::SPECTATOR;

	const CRAFTING_DEFAULT = 0;
	const CRAFTING_WORKBENCH = 1;
	const CRAFTING_ANVIL = 2;
	const CRAFTING_ENCHANT = 3;

	const SURVIVAL_SLOTS = 36;
	const CREATIVE_SLOTS = 112;

	const DEFAULT_SPEED = 0.1;
	const MAXIMUM_SPEED = 0.5;

	const FOOD_LEVEL_MAX = 20;
	const EXHAUSTION_NEEDS_FOR_ACTION = 4;

	const MAX_EXPERIENCE = 1.0; // experience is percents
	const MAX_EXPERIENCE_LEVEL = PHP_INT_MAX;

	const MIN_WINDOW_ID = 2;

	const RESOURCE_PACK_CHUNK_SIZE = 128 * 1024; //128KB

	/** @var SourceInterface */
	protected $interface;

	/** @var bool */
	public $spawned = false;
	public $loggedIn = false;
	public $gamemode;

	protected $currentWindowId = -1;

	/** @var Inventory */
	public $currentWindow = null;

	protected $messageCounter = 2;

	public $achievements = [];

    public $craftingType = self::CRAFTING_DEFAULT;

	public $creationTime = 0;

	protected $randomClientId;

	protected $originalProtocol = ProtocolInfo::CURRENT_PROTOCOL;
	public $protocol = ProtocolInfo::CURRENT_PROTOCOL;

	protected $connected = true;
	protected $ip;
	/** @var bool */
	protected $removeFormat = false;
	protected $port;
	protected $username;
	protected $iusername;
	protected $displayName;
	protected $languageCode = "en_UK";
	protected $clientVersion = "";
	protected $platformChatId = "";
    protected $xuid = "";
	protected $startAction = -1;
	/** @var Vector3|null */
	protected $sleeping = null;

	protected $deviceModel;
    protected $deviceType = self::OS_UNKNOWN;

	public $usedChunks = [];
	protected $loadQueue = [];
	/** @var int */
	protected $nextChunkOrderRun = 5;

	/** @var Player[] */
	protected $hiddenPlayers = [];
	protected $hiddenEntity = [];

	private $ping = 0;

	protected $viewDistance = -1;
	protected $chunksPerTick;
	protected $spawnThreshold;
	/** @var int */
	protected $chunkLoadCount = 0;
	/** @var null|Position */
	private $spawnPosition = null;

	protected $inAirTicks = 0;

	//TODO: Abilities
	protected $autoJump = true;
	protected $allowFlight = false;
	protected $isFlying = false;

	protected $checkMovement = false;

	/** @var PermissibleBase */
	private $perm = null;

	public $weatherData = [0, 0, 0];

	/** @var Vector3 */
	public $fromPos = null;
	private $portalTime = 0;
	/** @var  Position */
	private $shouldResPos;

	/** @var FishingHook */
	public $fishingHook = null;

	/** @var int */
	protected $lastEnderPearlUse = 0;

	public $dead = false;

	public $lastBreak;

	public $timestamp = false;

	private $clientSecret;

	/** @var Vector3 */

	public $speed = null;

	/**
	 * @deprecated
	 * @var array
	 */

	/** @var Vector3 */
	public $newPosition;

	protected $startAirTicks = 5;

	protected $identifier;

	public $movementSpeed = self::DEFAULT_SPEED;

	private $exp = 0;

	private $expLevel = 0;

	private $elytraIsActivated = false;

    /** @IMPORTANT don't change the scope */
    private $inventoryType = self::INVENTORY_CLASSIC;

	private $actionsNum = [];

	protected $isTeleporting = false;

	/** @var float value for player food bar*/
	private $foodLevel = 20.0;
	/** @var float */
	private $saturation = 5.0;
	/** @var float */
	private $exhaustion = 0.0;

	/** @var integer */
	protected $foodTick = 0;
	/** @var boolean */
	protected $hungerEnabled = true;

	protected $currentVehicle = null;

	protected $interactButtonText= '';

	protected $lastInteractTick = 0;

	private $lastInteractCoordsHash = -1;

	protected $lastMoveBuffer = '';

	protected $countMovePacketInLastTick = 0;

	protected $titleData = [];

	protected $doDaylightCycle = true;

	private $lastQuickCraftTransactionGroup = [];

	protected $additionalSkinData = [];

	/** @var gcode */
	public $antibot;

	public function linkHookToPlayer(FishingHook $entity){
		if($entity->isAlive()){
			$this->setFishingHook($entity);
			$pk = new EntityEventPacket();
			$pk->eid = $this->getFishingHook()->getId();
			$pk->event = EntityEventPacket::FISH_HOOK_POSITION;
			$this->server->broadcastPacket($this->level->getPlayers(), $pk);
			return true;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function unlinkHookFromPlayer(){
		if($this->fishingHook instanceof FishingHook){
			$pk = new EntityEventPacket();
			$pk->eid = $this->fishingHook->getId();
			$pk->event = EntityEventPacket::FISH_HOOK_TEASE;
			$this->server->broadcastPacket($this->level->getPlayers(), $pk);
			$this->setFishingHook();
			return true;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function isFishing(){
		return ($this->fishingHook instanceof FishingHook);
	}

	/**
	 * @return FishingHook
	 */
	public function getFishingHook(){
		return $this->fishingHook;
	}

	/**
	 * @param FishingHook|null $entity
	 */
	public function setFishingHook(FishingHook $entity = null){
		if($entity === null and $this->fishingHook instanceof FishingHook){
			$this->fishingHook->close();
		}

		if($entity !== null){
		    $entity->shootingEntity = $this;
        }

		$this->fishingHook = $entity;
	}


	public function getLeaveMessage(){
		return "";
	}

	/**
	 * This might disappear in the future.
	 * Please use getUniqueId() instead (IP + clientId + name combo, in the future it'll change to real UUID for online auth)
	 *
	 * @deprecated
	 *
	 */
	public function getClientId(){
		return $this->randomClientId;
	}

	public function getClientSecret(){
		return $this->clientSecret;
	}

	public function isBanned(){
		if($this->server->bans->query("SELECT * FROM bans WHERE name = '$this->iusername'")->fetchArray(SQLITE3_ASSOC)){
			return true;
		}
	}

	public function setBanned($value){
		if($value === true){
			$player = $this->getName();
			$this->server->bans->query("INSERT INTO bans(name, due, bannedby, reason) VALUES ('$player', 'null', 'null', 'null');");
			$this->kick("§l§aВы были §cзабанены §aна §bсервере");
		}else{
			$this->server->bans->query("DELETE FROM bans WHERE name = '$player'");
		}
	}


    private function chat($message){
		foreach(explode("\n", $message) as $messagePart){
			if(trim($messagePart) != "" and strlen($messagePart) <= 255 and $this->messageCounter-- > 0){
				if(substr($messagePart, 0, 2) === "./"){ //Command (./ = fast hack for old plugins post 0.16 and hack for version 1.9+)
					$messagePart = substr($messagePart, 1);
				}

				$ev = new PlayerCommandPreprocessEvent($this, $messagePart);

				$this->server->getPluginManager()->callEvent($ev);

				if($ev->isCancelled()){
					break;
				}

				if(substr($ev->getMessage(), 0, 1) === "/"){
					Timings::$playerCommandTimer->startTiming();
					$this->server->dispatchCommand($this, substr($ev->getMessage(), 1));
					Timings::$playerCommandTimer->stopTiming();
				}else{

					$this->server->getPluginManager()->callEvent($ev = new PlayerChatEvent($this, $messagePart));
					if($ev->isCancelled()){
					    break;
					}

					if($ev->getFormat() == "<%s> %s"){
						$this->server->broadcastMessage($ev->getPlayer()->getDisplayName().": ".$ev->getMessage(), $ev->getRecipients());
					} else {
						$this->server->broadcastMessage($ev->getFormat(), $ev->getRecipients()); //поддержка для пурчата, в симплекскоре и в сф2 ее почему то не добавили, вот я и добавил
					}
				}
			}
		}
		return true;
    }

	public function isWhitelisted(){
		return $this->server->isWhitelisted(strtolower($this->getName()));
	}

	public function isCountable($value){
        return is_array($value) || (is_object($value) && $value instanceof Countable);
	}

	public function addTitle($text, $subtext = '', $time = 5) {
		$pk = new SetTitlePacket();
		$pk->type = SetTitlePacket::TITLE_TYPE_TIMES;
		$pk->text = "";
		$pk->fadeInTime = 5;
		$pk->fadeOutTime = 5;
		$pk->stayTime = 20 * $time;
		$this->dataPacket($pk);

		if (!empty($subtext)) {
			$pk = new SetTitlePacket();
			$pk->type = SetTitlePacket::TITLE_TYPE_SUBTITLE;
			$pk->text = $subtext;
			$this->dataPacket($pk);
		}

		$pk = new SetTitlePacket();
		$pk->type = SetTitlePacket::TITLE_TYPE_TITLE;
		$pk->text = $text;
		$this->dataPacket($pk);
	}

	public function setWhitelisted($value){
		if($value === true){
			$this->server->addWhitelist(strtolower($this->getName()));
		}else $this->server->removeWhitelist(strtolower($this->getName()));
	}

	public function getPlayer(){
		return $this;
	}

	public function getFirstPlayed(){
		return $this->namedtag instanceof Compound ? $this->namedtag["firstPlayed"] : null;
	}

	public function getLastPlayed(){
		return $this->namedtag instanceof Compound ? $this->namedtag["lastPlayed"] : null;
	}

	public function hasPlayedBefore(){
		return $this->namedtag instanceof Compound;
	}

	public function setAllowFlight($value){
		$this->allowFlight = (bool) $value;
		$this->sendSettings();
	}

	public function getAllowFlight(){
		return $this->allowFlight;
	}

	public function setAutoJump($value){
		$this->autoJump = $value;
		$this->sendSettings();
	}

	public function setTimestamp($value){
	    $this->timestamp = $value;
	}

	public function hasAutoJump(){
		return $this->autoJump;
	}

	/**
	 * @param Player $player
	 */
	public function getProtocol(){
	    return $this->protocol;
	}

	public function spawnTo(Player $player){
		if($this->spawned === true and $player->spawned === true and $this->dead !== true and $player->dead !== true and $player->getLevel() === $this->level and $player->canSee($this) and !$this->isSpectator()) parent::spawnTo($player);
	}

	/**
	 * @return Server
	 */
	public function getServer(){
		return $this->server;
	}

	/**
	 * @return bool
	 */
	public function getRemoveFormat(){
		return $this->removeFormat;
	}

	/**
	 * @param bool $remove
	 */
	public function setRemoveFormat($remove = true){
		$this->removeFormat = (bool) $remove;
	}

	/**
	 * @param Player $player
	 *
	 * @return bool
	 */
	public function canSee(Player $player){
		return !isset($this->hiddenPlayers[$player->getName()]);
	}

	/**
	 * @param Player $player
	 */
	public function hidePlayer(Player $player){
		if($player === $this) return;
		$this->hiddenPlayers[$player->getName()] = $player;
		$player->despawnFrom($this);
	}

	/**
	 * @param Player $player
	 */
	public function showPlayer(Player $player){
		if($player === $this) return;
		unset($this->hiddenPlayers[$player->getName()]);
		if($player->isOnline()) $player->spawnTo($this);
	}

	public function canCollideWith(Entity $entity){
		return false;
	}

	public function resetFallDistance(){
		parent::resetFallDistance();
		if($this->inAirTicks !== 0) $this->startAirTicks = 5;
		$this->inAirTicks = 0;
	}

	/**
	 * @return bool
	 */
	public function isOnline(){
		return $this->connected === true and $this->loggedIn === true;
	}

	/**
	 * @return bool
	 */
	public function isOp(){
		return $this->server->isOp($this->getName());
	}

	/**
	 * @param bool $value
	 */
	public function setOp($value){
		if($value === $this->isOp()) return;
		if($value === true){
		    $this->server->addOp($this->getName());
		} else $this->server->removeOp($this->getName());
		$this->recalculatePermissions();
	}

	/**
	 * @param permission\Permission|string $name
	 *
	 * @return bool
	 */
	public function isPermissionSet($name){
		return $this->perm->isPermissionSet($name);
	}

	/**
	 * @param permission\Permission|string $name
	 *
	 * @return bool
	 */
	public function hasPermission($name){
		return $this->perm->hasPermission($name);
	}

	/**
	 * @param Plugin $plugin
	 * @param string $name
	 * @param bool   $value
	 *
	 * @return permission\PermissionAttachment
	 */
	public function addAttachment(Plugin $plugin, $name = null, $value = null){
		return $this->perm->addAttachment($plugin, $name, $value);
	}

	/**
	 * @param PermissionAttachment $attachment
	 */
	public function removeAttachment(PermissionAttachment $attachment){
		$this->perm->removeAttachment($attachment);
	}

	public function recalculatePermissions(){
		$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
		if($this->perm === null) return;
		$this->perm->recalculatePermissions();
		if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)) $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)) $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
	}

	/**
	 * @return permission\PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions(){
		return $this->perm->getEffectivePermissions();
	}


	/**
	 * @param SourceInterface $interface
	 * @param null            $clientID
	 * @param string          $ip
	 * @param integer         $port
	 */
	public function __construct(SourceInterface $interface, $clientID, $ip, $port){
		$this->interface = $interface;
		$this->perm = new PermissibleBase($this);
		$this->namedtag = new Compound();
		$this->server = Server::getInstance();
		$this->lastBreak = 0;
		$this->ip = $ip;
		$this->port = $port;
		$this->spawnPosition = null;
		$this->gamemode = $this->server->getGamemode();
		$this->setLevel($this->server->getDefaultLevel(), true);
		$this->newPosition = null;
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-sending.per-tick", 4);
		$this->spawnThreshold = (int) (($this->server->getProperty("chunk-sending.spawn-radius", 4) ** 2) * M_PI);
		$this->checkMovement = (bool) $this->server->getAdvancedProperty("main.check-movement", true);
		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);

		$this->uuid = null;
		$this->rawUUID = null;

		$this->creationTime = microtime(true);

		$this->inventory = new PlayerInventory($this); // hack for not null getInventory
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_HAS_COLLISION, true, self::DATA_TYPE_LONG, false);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_AFFECTED_BY_GRAVITY, true, self::DATA_TYPE_LONG, false);
	}

	public function sendCommandData(){
		$data = [];
		foreach($this->server->getCommandMap()->getCommands() as $command){

			if(count($cmdData = $command->generateCustomCommandData($this)) > 0){
				$data[$command->getName()]["versions"][0] = $cmdData;
			}

		}
		return $data;
	}

	/**
	 * @return int
	 */
	public function getViewDistance(){
		return $this->viewDistance;
	}

	/**
	 * @return void
	 */
	public function setViewDistance($distance){
		$this->viewDistance = $this->server->getAllowedViewDistance($distance);

		$this->spawnThreshold = (int) (min($this->viewDistance, $this->server->getProperty("chunk-sending.spawn-radius", 4)) ** 2 * M_PI);

		$this->nextChunkOrderRun = 0;

		$pk = new ChunkRadiusUpdatePacket();
		$pk->radius = $this->viewDistance;
		$this->dataPacket($pk);

		$this->server->getLogger()->debug("Setting view distance for " . $this->getName() . " to " . $this->viewDistance . " (requested " . $distance . ")");
	}

	/**
	 * @return bool
	 */
	public function isConnected(){
		return $this->connected === true;
	}

	/**
	 * Gets the "friendly" name to display of this player to use in the chat.
	 *
	 * @return string
	 */
	public function getDisplayName(){
		return $this->displayName;
	}

	/**
	 * @param string $name
	 */
	public function setDisplayName($name){
		$this->displayName = $name;
		
		if ($this->spawned) {
		    $this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getDisplayName(), $this->getSkinName(), $this->getSkinData(), $this->getSkinGeometryName(), $this->getSkinGeometryData(), $this->getCapeData(), $this->getOriginalProtocol() >= Info::PROTOCOL_140 ? $this->getXUID() : "", $this->getDeviceOS(), $this->getAdditionalSkinData());
		}
	}

	/**
	 * Gets the player IP address
	 *
	 * @return string
	 */

	public function getAddress(){
		return $this->ip;
	}

	public function isLiving(){
		return ($this->isSurvival() || $this->isAdventure());
	}

	/**
	 * @return int
	 */
	public function getPort(){
		return $this->port;
	}

	/**
	 * @return bool
	 */
	public function isSleeping(){
		return $this->sleeping !== null;
	}

	public function unloadChunk($x, $z, Level $level = null){
		$level = $level ?? $this->level;
		$index = Level::chunkHash($x, $z);
		if(isset($this->usedChunks[$index])){
			foreach($this->level->getChunkEntities($x, $z) as $entity){
				if($entity !== $this){
					$entity->despawnFrom($this);
				}
			}

			unset($this->usedChunks[$index]);
		}
		$level->freeChunk($x, $z, $this);
		unset($this->loadQueue[$index]);
	}

	/**
	 * @return Position
	 */
	public function getSpawn(){
		if($this->spawnPosition instanceof Position and $this->spawnPosition->getLevel() instanceof Level){
			return $this->spawnPosition;
		}else{
			$level = $this->server->getDefaultLevel();
			$lol = $level->getSpawnLocation();
			$pos = new Position($lol->getX(), $lol->getY(), $lol->getZ(), $level);
			return $pos;
		}
	}

	public function useChunk($x, $z){
        $this->usedChunks[Level::chunkHash($x, $z)] = true;
	    
		$this->chunkLoadCount++;

		if($this->spawned){
			foreach($this->level->getChunkEntities($x, $z) as $entity){
				if($entity !== $this and !$entity->closed and !$entity->dead and $this->canSeeEntity($entity)){
					$entity->spawnTo($this);
				}
			}
		}

		if($this->chunkLoadCount !== -1 and ++$this->chunkLoadCount >= $this->spawnThreshold){
		    $this->doFirstSpawn();
		}
	}


	protected function sendNextChunk(){
		if(!$this->isConnected()){
			return;
		}

		Timings::$playerChunkSendTimer->startTiming();

		$count = 0;
		foreach($this->loadQueue as $index => $distance){
			if($count >= $this->chunksPerTick){
				break;
			}
			
			$X = null;
			$Z = null;
			Level::getXZ($index, $X, $Z);
			assert(is_int($X) and is_int($Z));

			++$count;

			$this->level->useChunk($X, $Z, $this, false);
			$this->useChunk($X, $Z);
			
			if(!$this->level->populateChunk($X, $Z, true)){
				continue;
			}
			
			unset($this->loadQueue[$index]);
			$this->level->requestChunk($X, $Z, $this);
		}

		Timings::$playerChunkSendTimer->stopTiming();
	}

	protected function orderChunks() {
		if(!$this->isConnected() or $this->viewDistance === -1){
			return;
		}

		Timings::$playerChunkOrderTimer->startTiming();

		$radius = $this->server->getAllowedViewDistance($this->viewDistance);
		$radiusSquared = $radius ** 2;

		$newOrder = [];
		$unloadChunks = $this->usedChunks;

        $centerX = $this->getFloorX() >> 4;
        $centerZ = $this->getFloorZ() >> 4;

		for($x = 0; $x < $radius; ++$x){
			for($z = 0; $z <= $x; ++$z){
				if(($x ** 2 + $z ** 2) > $radiusSquared){
					break; //skip to next band
				}

				//If the chunk is in the radius, others at the same offsets in different quadrants are also guaranteed to be.

				/* Top right quadrant */
				if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $x, $centerZ + $z)]) or $this->usedChunks[$index] === false){
					$newOrder[$index] = true;
				}
				unset($unloadChunks[$index]);

				/* Top left quadrant */
				if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $x - 1, $centerZ + $z)]) or $this->usedChunks[$index] === false){
					$newOrder[$index] = true;
				}
				unset($unloadChunks[$index]);

				/* Bottom right quadrant */
				if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $x, $centerZ - $z - 1)]) or $this->usedChunks[$index] === false){
					$newOrder[$index] = true;
				}
				unset($unloadChunks[$index]);

				/* Bottom left quadrant */
				if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $x - 1, $centerZ - $z - 1)]) or $this->usedChunks[$index] === false){
					$newOrder[$index] = true;
				}
				unset($unloadChunks[$index]);

				if($x !== $z){
					/* Top right quadrant mirror */
					if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $z, $centerZ + $x)]) or $this->usedChunks[$index] === false){
						$newOrder[$index] = true;
					}
					unset($unloadChunks[$index]);

					/* Top left quadrant mirror */
					if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $z - 1, $centerZ + $x)]) or $this->usedChunks[$index] === false){
						$newOrder[$index] = true;
					}
					unset($unloadChunks[$index]);

					/* Bottom right quadrant mirror */
					if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $z, $centerZ - $x - 1)]) or $this->usedChunks[$index] === false){
						$newOrder[$index] = true;
					}
					unset($unloadChunks[$index]);

					/* Bottom left quadrant mirror */
					if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $z - 1, $centerZ - $x - 1)]) or $this->usedChunks[$index] === false){
						$newOrder[$index] = true;
					}
					unset($unloadChunks[$index]);
				}
			}
		}

		foreach($unloadChunks as $index => $bool){
			Level::getXZ($index, $X, $Z);
			$this->unloadChunk($X, $Z);
		}

		$this->loadQueue = $newOrder;

		if(count($this->loadQueue) > 0 or count($unloadChunks) > 0){
			$pk = new NetworkChunkPublisherUpdatePacket();
			$pk->x = $this->getFloorX();
			$pk->y = $this->getFloorY();
			$pk->z = $this->getFloorZ();
			$pk->radius = $this->viewDistance * 16; //blocks, not chunks >.>
			$this->dataPacket($pk);
		}

		Timings::$playerChunkOrderTimer->stopTiming();

		return true;
	}

    protected function doFirstSpawn(){
		if($this->spawned){
			return; //avoid player spawning twice (this can only happen on 3.x with a custom malicious client)
		}
		
		$this->spawned = true;
		$this->sendSettings();
		$this->sendPotionEffects($this);
		$this->sendData($this);
		$this->inventory->sendContents($this);
		$this->inventory->sendArmorContents($this);

		$pk = new SetTimePacket();
		$pk->time = $this->level->getTime();
		$pk->started = $this->level->stopTime == false;
		$this->dataPacket($pk);
		$this->setDaylightCycle(!$this->level->stopTime);
		
		$this->level->getWeather()->sendWeather($this);

		$this->noDamageTicks = 60;
		$chunkX = $chunkZ = null;
		foreach ($this->usedChunks as $index => $c) {
			Level::getXZ($index, $chunkX, $chunkZ);
			foreach ($this->level->getChunkEntities($chunkX, $chunkZ) as $entity) {
				if ($entity !== $this && !$entity->closed && !$entity->dead && $this->canSeeEntity($entity)) {
					$entity->spawnTo($this);
				}
			}
		}
		$this->setInteractButtonText('', true);

	    $this->server->getPluginManager()->callEvent($ev = new PlayerJoinEvent($this, ""));
	    
		$this->server->onPlayerLogin($this);
    }

    public function sendCommandsData(){
		$availableCommands = $this->sendCommandData();
		AvailableCommandsPacket::prepareCommands($availableCommands);

		$pk = new AvailableCommandsPacket();
		$this->directDataPacket($pk); // хз...
    }

	/**
	 * Sends an ordered DataPacket to the send buffer
	 *
	 * @param DataPacket $packet
	 *
	 * @return int|bool
	 */
	public function dataPacket(DataPacket $packet){
		if($this->connected === false){
			return false;
		}

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();

        try {
            $this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
	    	if($ev->isCancelled()){
	    	    $timings->stopTiming();
		    	return false;
	    	}

	    	if($packet->pname() == "BATCH_PACKET"){
		    	$packet->encode($this->protocol);
		    	$this->interface->putReadyPacket($this, $packet->getBuffer());
			   	return true;
	    	}

	    	$packet->setDeviceId($this->getDeviceOS());
	    	$packet->encode($this->protocol);
	    	$buffer = $packet->getBuffer();
	    	$this->interface->putPacket($this, Binary::writeVarInt(strlen($buffer)) . $buffer);
	    	return true;
        }finally{
	    	$timings->stopTiming();
        }
	}

	/**
	 * @param DataPacket $packet
	 *
	 * @return bool|int
	 */
	public function directDataPacket(DataPacket $packet){
		if($this->connected === false){
			return false;
		}

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();

		try {
            $this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
	    	if($ev->isCancelled()){
	    	    $timings->stopTiming();
		    	return false;
	    	}

	    	$packet->encode($this->protocol);
	    	$buffer = $packet->getBuffer();
	    	$this->interface->putPacket($this, Binary::writeVarInt(strlen($buffer)) . $buffer);
	    	return true;
		}finally{
	    	$timings->stopTiming();
		}
	}

	public function sendStructureBlock($vector, $vector2){
		$block = Block::get(Block::STRUCTURE_BLOCK);
		$block->x = $vector->x;
		$block->y = $vector->y;
		$block->z = $vector->z;
		$block->level = $this->getLevel();
		$block->level->sendBlocks([$this], [$block]);
		$nbt = new Compound();
		$nbt->id = new StringTag("id", "StructureBlock");
		$nbt->data = new IntTag("data", StructureEditorData::TYPE_EXPORT);
		$nbt->dataField = new StringTag("dataField", "");
		$nbt->ignoreEntities = new ByteTag("ignoreEntities", 0);
		$nbt->includePlayers = new ByteTag("includePlayers", 1);
		$nbt->integrity = new FloatTag("integrity", 1.0);
		$nbt->isMovable = new ByteTag("isMovable", 0);
		$nbt->isPowered = new ByteTag("isPowered", 1);
		$nbt->mirror = new ByteTag("mirror", 0);
		$nbt->removeBlocks = new ByteTag("removeBlocks", 0);
		$nbt->rotation = new ByteTag("rotation", 0);
		$nbt->seed = new LongTag("seed", 0);
		$nbt->showBoundingBox = new ByteTag("showBoundingBox", 1);
		$nbt->structureName = new StringTag("structureName", "Structure Block");
		$nbt->x = new IntTag("x", $vector->x);
		$nbt->y = new IntTag("y", $vector->y);
		$nbt->z = new IntTag("z", $vector->z);
		$size = $this->calculateSize();
		$offset = $this->calculateOffset($vector);
		$nbt->xStructureOffset = new IntTag("xStructureOffset", 0);
		$nbt->yStructureOffset = new IntTag("yStructureOffset", 0);
		$nbt->zStructureOffset = new IntTag("zStructureOffset", 0);
		$nbt->xStructureSize = new IntTag("xStructureSize", 0);
		$nbt->yStructureSize = new IntTag("yStructureSize", 0);
		$nbt->zStructureSize = new IntTag("zStructureSize", 0);
		$nbt2 = new NBT(NBT::LITTLE_ENDIAN);
		$nbt2->setData($nbt);
		$pk = new TileEntityDataPacket();
		$pk->x = $x1;
		$pk->y = $y1;
		$pk->z = $z1;
		$pk->namedtag = $nbt2->write();
		$this->dataPacket($pk);
	}
	/**
	 * @param Vector3 $pos
	 *
	 * @return boolean
	 */
	public function sleepOn(Vector3 $pos){
		foreach($this->level->getNearbyEntities($this->boundingBox->grow(2, 1, 2), $this) as $p){
			if($p instanceof Player){
				if($p->sleeping !== null and $pos->distance($p->sleeping) <= 0.1){
					return false;
				}
			}
		}

		$this->server->getPluginManager()->callEvent($ev = new PlayerBedEnterEvent($this, $this->level->getBlock($pos)));
		if($ev->isCancelled()){
			return false;
		}

		$this->sleeping = clone $pos;

		$this->setDataProperty(self::DATA_PLAYER_BED_POSITION, self::DATA_TYPE_POS, [$pos->x, $pos->y, $pos->z]);
		$this->setDataFlag(self::DATA_PLAYER_FLAGS, self::DATA_PLAYER_FLAG_SLEEP, true, self::DATA_TYPE_BYTE);

		$this->setSpawn($pos);
		return true;
	}

	/**
	 * Sets the spawnpoint of the player (and the compass direction) to a Vector3, or set it on another world with a Position object
	 *
	 * @param Vector3|Position $pos
	 */
	public function setSpawn(Vector3 $pos){
		if(!($pos instanceof Position)){
			$level = $this->level;
		}else{
			$level = $pos->getLevel();
		}
		$this->spawnPosition = new Position($pos->x, $pos->y, $pos->z, $level);
		$pk = new SetSpawnPositionPacket();
		$pk->x = (int) $this->spawnPosition->x;
		$pk->y = (int) $this->spawnPosition->y;
		$pk->z = (int) $this->spawnPosition->z;
		$pk->dimension = (int) $this->spawnPosition->getLevel()->getDimension();
		$this->dataPacket($pk);
	}

	public function stopSleep(){
		if($this->sleeping instanceof Vector3){
			$this->server->getPluginManager()->callEvent($ev = new PlayerBedLeaveEvent($this, $this->level->getBlock($this->sleeping)));

			$this->sleeping = null;
			$this->setDataFlag(self::DATA_PLAYER_FLAGS, self::DATA_PLAYER_FLAG_SLEEP, false, self::DATA_TYPE_BYTE);
			$this->setDataProperty(self::DATA_PLAYER_BED_POSITION, self::DATA_TYPE_POS, [0, 0, 0]);

			$this->level->sleepTicks = 0;

			$pk = new AnimatePacket();
			$pk->eid = $this->id;
			$pk->action = 3; //Wake up
			$this->dataPacket($pk);
		}

	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function checkSleep(){
		if($this->sleeping instanceof Vector3){
		    if(!$this->level->isNight()){
				foreach($this->level->getPlayers() as $p){
					if($p->sleeping !== null){
				    	$p->stopSleep();
					}
				}
			}
		}
	}

	/**
	 * @return int
	 */
	public function getGamemode() {
		return $this->gamemode;
	}

	/**
	 * Sets the gamemode, and if needed, kicks the Player.
	 *
	 * @param int $gm
	 *
	 * @return bool
	 */
	public function setGamemode($gm) {
		if ($gm < 0 || $gm > 3 || $this->gamemode === $gm) {
			return false;
		}

		$this->server->getPluginManager()->callEvent($ev = new PlayerGameModeChangeEvent($this, (int) $gm));
		if ($ev->isCancelled()) {
			return false;
		}

		$this->gamemode = $gm;
		$this->allowFlight = $this->isCreative();

		if ($this->isSpectator()) {
			$this->despawnFromAll();
		}

		$this->namedtag->playerGameType = new IntTag("playerGameType", $this->gamemode);
		$pk = new SetPlayerGameTypePacket();
		$pk->gamemode = $this->gamemode == 3 ? 1 : $this->gamemode;
		$this->dataPacket($pk);
		$this->sendSettings();

        if($this->gamemode === 3){
            $this->sendCreativeInventory([]);
        } else {
            $this->sendCreativeInventory($this->getCreativeItems());
        }

		$this->inventory->sendContents($this);
		$this->inventory->sendContents($this->getViewers());
		$this->inventory->sendHeldItem($this->hasSpawned);

		return true;
	}

	/**
	 * Sends all the option flags
	 */
	public function sendSettings() {
		$flags = AdventureSettingsPacket::FLAG_NO_PVM | AdventureSettingsPacket::FLAG_NO_MVP;
		if ($this->autoJump) {
			$flags |= AdventureSettingsPacket::FLAG_AUTO_JUMP;
		}
		if ($this->allowFlight) {
			$flags |= AdventureSettingsPacket::FLAG_PLAYER_MAY_FLY;
		}
		if ($this->isSpectator()) {
			$flags |= AdventureSettingsPacket::FLAG_WORLD_IMMUTABLE;
			$flags |= AdventureSettingsPacket::FLAG_PLAYER_NO_CLIP;
		}

		$pk = new AdventureSettingsPacket();
		$pk->flags = $flags;
		$pk->userId = $this->getId();
		$pk->commandPermissions = AdventureSettingsPacket::COMMAND_PERMISSION_LEVEL_ANY;
		if($this->isOp()) $pk->permissionLevel = AdventureSettingsPacket::PERMISSION_LEVEL_OPERATOR;
		else $pk->permissionLevel = AdventureSettingsPacket::PERMISSION_LEVEL_MEMBER;
		$pk->actionPermissions = $this->getActionFlags();
		$this->dataPacket($pk);
	}

	public static function isValidUserName($name){
		if($name === null){
			return false;
		}

		$lname = strtolower($name);
		$len = strlen($name);
		return $lname !== "rcon" and $lname !== "console" and $len >= 1 and $len <= 16 and preg_match("/[^A-Za-z0-9_]/", $name) === 0;
	}

	public function isSurvival(){
		return ($this->gamemode & 0x01) === 0;
	}

	public function isCreative(){
		return ($this->gamemode & 0x01) > 0;
	}

	public function isSpectator(){
		return $this->gamemode === 3;
	}

	public function isAdventure(){
		return ($this->gamemode & 0x02) > 0;
	}

	public function getDrops(){
		if(!$this->isCreative()){
			return parent::getDrops();
		}

		return [];
	}

	/**
	 * @deprecated
	 */
	public function addEntityMotion($entityId, $x, $y, $z){

	}

	/**
	 * @deprecated
	 */
	public function addEntityMovement($entityId, $x, $y, $z, $yaw, $pitch, $headYaw = null){

	}

//	public function setDataProperty($id, $type, $value){
//		if(parent::setDataProperty($id, $type, $value)){
//			$this->sendData([$this], [$id => $this->dataProperties[$id]]);
//			return true;
//		}
//
//		return false;
//	}

	protected function checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz){
		/*
		if(!$this->onGround or $movY != 0){
			$bb = clone $this->boundingBox;
			$bb->maxY = $bb->minY + 0.5;
			$bb->minY -= 1;
			if(count($this->level->getCollisionBlocks($bb, true)) > 0){
				$this->onGround = true;
			}else{
				$this->onGround = false;
			}
		}
		$this->isCollided = $this->onGround;
		*/
	}

	protected function checkNearEntities($tickDiff){
		foreach($this->level->getNearbyEntities($this->boundingBox->grow(1, 0.5, 1), $this) as $entity){
			$entity->scheduleUpdate();

			if(!$entity->isAlive()){
				continue;
			}

			if($entity instanceof Arrow && $entity->hadCollision){
				$item = Item::get(Item::ARROW, 0, 1);
				if($this->isSurvival() and !$this->inventory->canAddItem($item)){
					continue;
				}

				$this->server->getPluginManager()->callEvent($ev = new InventoryPickupArrowEvent($this->inventory, $entity));
				if($ev->isCancelled()){
					continue;
				}

				$pk = new TakeItemEntityPacket();
				$pk->eid = $this->getId();
				$pk->target = $entity->getId();
				Server::broadcastPacket($entity->getViewers(), $pk);

				$this->inventory->addItem(clone $item);
				$entity->close();
			}elseif($entity instanceof DroppedItem){
				if($entity->getPickupDelay() <= 0){
					$item = $entity->getItem();

					if($item instanceof Item){
						if($this->isSurvival() and !$this->inventory->canAddItem($item)){
							continue;
						}

						$this->server->getPluginManager()->callEvent($ev = new InventoryPickupItemEvent($this->inventory, $entity));
						if($ev->isCancelled()){
							continue;
						}

						$pk = new TakeItemEntityPacket();
						$pk->eid = $this->getId();
						$pk->target = $entity->getId();
						Server::broadcastPacket($entity->getViewers(), $pk);

						$this->inventory->addItem(clone $item);
						$entity->kill();

						$this->inventory->sendContents($this);

						/*if ($this->inventoryType == self::INVENTORY_CLASSIC && $this->protocol < ProtocolInfo::PROTOCOL_120) {
							Win10InvLogic::playerPickUpItem($this, $item);
						}*/
					}
				}
			}
		}
	}

	protected $moving = false;

	public function setMoving($moving) {
		$this->moving = $moving;
	}

	public function isMoving(){
		return $this->moving;
	}

	public function setMotion(Vector3 $mot){
		if(parent::setMotion($mot)){
			if($this->chunk !== null){
				$pk = new SetEntityMotionPacket();
				$pk->entities[] = [$this->id, $mot->x, $mot->y, $mot->z];
				$this->dataPacket($pk);
				$viewers = $this->getViewers();
				$viewers[$this->getId()] = $this;
				Server::broadcastPacket($viewers, $pk);
			}

			if($this->motionY > 0){
				$this->startAirTicks = (-(log($this->gravity / ($this->gravity + $this->drag * $this->motionY))) / $this->drag) * 2 + 5;
			}

			return true;
		}
		return false;
	}

	/**
	 * @return void
	 */
	public function checkNetwork(){
		if(!$this->isOnline()){
			return;
		}

		if($this->nextChunkOrderRun !== PHP_INT_MAX and $this->nextChunkOrderRun-- <= 0){
			$this->nextChunkOrderRun = PHP_INT_MAX;
			$this->orderChunks();
		}

		if(count($this->loadQueue) > 0){
			$this->sendNextChunk();
		}
	}

	public function onUpdate($currentTick){
		if(!$this->loggedIn){
			return false;
		}

		$tickDiff = $currentTick - $this->lastUpdate;

		if($tickDiff <= 0){
			return true;
		}

		$this->messageCounter = 2;
		$this->lastUpdate = $currentTick;

		if($this->dead === true and $this->spawned){
			++$this->deadTicks;
			if($this->deadTicks >= 10){
				$this->despawnFromAll();
			}
			return $this->deadTicks < 10;
		}

		$this->timings->startTiming();

		if($this->spawned){
			if($this->server->netherEnabled){
				if(($this->server->getTick() - $this->portalTime >= 80) and $this->portalTime > 0){
					$netherLevel = null;
					if($this->server->isLevelLoaded($this->server->netherName) or $this->server->loadLevel($this->server->netherName)){
						$netherLevel = $this->server->getLevelByName($this->server->netherName);
					}

					if($netherLevel instanceof Level){
						if($this->getLevel() !== $netherLevel){
							$this->fromPos = $this->getPosition();
							$this->fromPos->x = ((int) $this->fromPos->x) + 0.5;
							$this->fromPos->z = ((int) $this->fromPos->z) + 0.5;
							$this->teleport($this->shouldResPos = $netherLevel->getSafeSpawn());
						}elseif($this->fromPos instanceof Position){
							if(!($this->getLevel()->isChunkLoaded($this->fromPos->x, $this->fromPos->z))){
								$this->getLevel()->loadChunk($this->fromPos->x, $this->fromPos->z);
							}
							$add = [1, 0, -1, 0, 0, 1, 0, -1];
							$tempos = null;
							for($j = 2; $j < 5; $j++){
								for($i = 0; $i < 4; $i++){
								    if($this->temporalVector !== NULL){
								    	if($this->fromPos->getLevel()->getBlock($this->temporalVector->fromObjectAdd($this->fromPos, $add[$i] * $j, 0, $add[$i + 4] * $j))->getId() === Block::AIR){
									    	if($this->fromPos->getLevel()->getBlock($this->temporalVector->fromObjectAdd($this->fromPos, $add[$i] * $j, 1, $add[$i + 4] * $j))->getId() === Block::AIR){
										    	$tempos = $this->fromPos->add($add[$i] * $j, 0, $add[$i + 4] * $j);
										    	//$this->getLevel()->getServer()->getLogger()->debug($tempos);
										    	break;
									    	}
										}
									}
								}
								if($tempos != null){
									break;
								}
							}
							if($tempos === null){
								$tempos = $this->fromPos->add(mt_rand(-2, 2), 0, mt_rand(-2, 2));
							}
							$this->teleport($this->shouldResPos = $tempos);
							$add = null;
							$tempos = null;
							$this->fromPos = null;
						}else{
							$this->teleport($this->shouldResPos = $this->server->getDefaultLevel()->getSafeSpawn());
						}
						$this->portalTime = 0;
					}
				}
			}
			if(!$this->isSleeping()){
				$this->processMovement($tickDiff);
			}

			$this->entityBaseTick($tickDiff);

			if($this->isOnFire() or $this->lastUpdate % 10 == 0){
				if($this->isCreative() and !$this->isInsideOfFire()){
					$this->extinguish();
				}elseif($this->getLevel()->getWeather()->isRainy()){
					if($this->getLevel()->canBlockSeeSky($this)){
						$this->extinguish();
					}
				}
			}

			if(!$this->isSpectator() and $this->speed !== null){
				if($this->onGround){
					if($this->inAirTicks !== 0){
						$this->startAirTicks = 5;
					}
					$this->inAirTicks = 0;
					if ($this->elytraIsActivated) {
						$this->setFlyingFlag(false);
						$this->setElytraActivated(false);
					}
				}else{
					if($this->needAntihackCheck() && !$this->isUseElytra() && !$this->allowFlight && !$this->isSleeping() && !$this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_NOT_MOVE)){
						$expectedVelocity = (-$this->gravity) / $this->drag - ((-$this->gravity) / $this->drag) * exp(-$this->drag * ($this->inAirTicks - $this->startAirTicks));
						$diff = ($this->speed->y - $expectedVelocity) ** 2;
						++$this->inAirTicks;
					}
				}
			}

			if($this->isSurvival()) $this->doFood();
		}

		if (!empty($this->titleData)) {
			$this->titleData['holdTickCount']--;
			if ($this->titleData['holdTickCount'] <= 0) {
				$this->sendTitle($this->titleData['text'], $this->titleData['subtext'], $this->titleData['time']);
				$this->titleData = [];
			}
		}

		$this->timings->stopTiming();

		if (count($this->messageQueue) > 0) {
			$pk = new TextPacket();
			$pk->type = TextPacket::TYPE_RAW;
			if($this->timestamp){
                date_default_timezone_set("Europe/Moscow");
                $time = date("d.m.y H:i:s");
			    $pk->message = $this->server->colorTS . "[" . $time . "]" . " " . "§r§f" . implode("\n", $this->messageQueue);
			} else {
			    $pk->message = implode("\n", $this->messageQueue);
			}
			$this->dataPacket($pk);
			$this->messageQueue = [];
		}

		return true;
	}

	public function doFood()
    {
        if ($this->getFoodEnabled()) {
            $foodLevel = $this->foodLevel;
             $this->foodTick++;
            if($this->foodTick >= 80){
                $this->foodTick = 0;
            }
             if ($this->exhaustion >= self::EXHAUSTION_NEEDS_FOR_ACTION) {
                $this->exhaustion = 0;
                 if($this->saturation > 0){
                    $this->saturation--;
                }
                 if ($this->saturation <= 0 && $this->foodLevel > 0){
                    $this->foodLevel--;
                }
            }
             if($this->getHealth() < $this->getMaxHealth()){
                if($this->saturation > 0 && $this->getFood() > 18){
                    if($this->foodTick % 10 === 0){
                        $ev = new EntityRegainHealthEvent($this, 1, EntityRegainHealthEvent::CAUSE_EATING);
                        $this->heal(1, $ev);
                        if (!$ev->isCancelled()) {
                            $this->saturation -= 6;
                        }
                    }
                } elseif($this->foodTick % 80 === 0){
                    if($this->getFood() > 17){
                        $ev = new EntityRegainHealthEvent($this, 1, EntityRegainHealthEvent::CAUSE_EATING);
                        $this->heal(1, $ev);
                        if (!$ev->isCancelled()) {
                            $this->saturation -= 6;
                        }
                    } elseif($this->getFood() === 0){
                        $ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_HUNGER, 1);
                        $this->attack(1, $ev);
                    }
                }
                //todo: difficulty support?
            }
             if($this->foodLevel !== $foodLevel){ //fixes packet spam
                $this->setFood($this->foodLevel);
                 if($this->foodLevel < 6){
                    $this->setSprinting(false);
                }
            }
        }
    }

	protected static $foodData = [
		Item::APPLE => ['food' => 4, 'saturation' => 2.4],
		Item::BAKED_POTATO => ['food' => 5, 'saturation' => 6],
		Item::BEETROOT => ['food' => 1, 'saturation' => 1.2],
		Item::BEETROOT_SOUP => ['food' => 6, 'saturation' => 7.2],
		Item::BREAD => ['food' => 5, 'saturation' => 6],
		/** @todo cake slice and whole */
		Item::CARROT => ['food' => 3, 'saturation' => 3.6],
		Item::CHORUS_FRUIT => ['food' => 4, 'saturation' => 2.4],
		Item::COOKED_CHICKEN => ['food' => 6, 'saturation' => 7.2],
		Item::COOKED_FISH => ['food' => 5, 'saturation' => 6],
		Item::COOKED_MUTTON => ['food' => 6, 'saturation' => 9.6],
		Item::COOKED_PORKCHOP => ['food' => 8, 'saturation' => 12.8],
		Item::COOKED_RABBIT => ['food' => 5, 'saturation' => 6],
		Item::COOKED_SALMON => ['food' => 6, 'saturation' => 9.6],
		Item::COOKIE => ['food' => 2, 'saturation' => 0.4],
		Item::GOLDEN_APPLE => ['food' => 4, 'saturation' => 9.6],
		Item::ENCHANTED_GOLDEN_APPLE => ['food' => 4, 'saturation' => 9.6],
		Item::GOLDEN_CARROT => ['food' => 6, 'saturation' => 14.4],
		Item::MELON => ['food' => 2, 'saturation' => 1.2],
		Item::MUSHROOM_STEW => ['food' => 6, 'saturation' => 7.2],
		Item::POISONOUS_POTATO => ['food' => 2, 'saturation' => 1.2],
		Item::POTATO => ['food' => 1, 'saturation' => 0.6],
		Item::PUMPKIN_PIE => ['food' => 8, 'saturation' => 4.8],
		Item::RABBIT_STEW => ['food' => 10, 'saturation' => 12],
		Item::RAW_BEEF => ['food' => 3, 'saturation' => 1.8],
		Item::RAW_CHICKEN => ['food' => 2, 'saturation' => 1.2],
		Item::RAW_FISH => [
			0 => ['food' => 2, 'saturation' => 0.4], // raw fish
			1 => ['food' => 2, 'saturation' => 0.4], // raw salmon
			2 => ['food' => 1, 'saturation' => 0.2], // clownfish
			3 => ['food' => 1, 'saturation' => 0.2], // pufferfish
		],
		Item::RAW_MUTTON => ['food' => 2, 'saturation' => 1.2],
		Item::RAW_PORKCHOP => ['food' => 3, 'saturation' => 1.8],
		Item::RAW_RABBIT => ['food' => 3, 'saturation' => 1.8],
		Item::ROTTEN_FLESH => ['food' => 4, 'saturation' => 0.8],
		Item::SPIDER_EYE => ['food' => 2, 'saturation' => 3.2],
		Item::STEAK => ['food' => 8, 'saturation' => 12.8],
	];
	public function eatFoodInHand() {
		if (!$this->spawned) {
			return;
		}

		$slot = $this->inventory->getItemInHand();
		if (isset(self::$foodData[$slot->getId()])) {
			$this->server->getPluginManager()->callEvent($ev = new PlayerItemConsumeEvent($this, $slot));
			if ($ev->isCancelled()) {
				$this->inventory->sendContents($this);
				$this->setFood($this->foodLevel);
				return;
			}

			$pk = new EntityEventPacket();
			$pk->eid = $this->getId();
			$pk->event = EntityEventPacket::USE_ITEM;
			$viewers = $this->getViewers();
			$viewers[] = $this;
			Server::broadcastPacket($viewers, $pk);

			--$slot->count;
			$this->inventory->setItemInHand($slot);

			// get food data
			$foodId = $slot->getId();
			$foodData = self::$foodData[$foodId];
			if (!isset($foodData['food'])) { // is food data is array by meta
				$foodMeta = $slot->getDamage();
				if (isset($foodData[$foodMeta])) {
					$foodData = $foodData[$foodMeta];
				} else {
					$this->setFood($this->foodLevel);
					return;
				}
			}
			// food logic
			$this->foodLevel = min(self::FOOD_LEVEL_MAX, $this->foodLevel + $foodData['food']);
			$this->saturation = min ($this->foodLevel, $this->saturation + $foodData['saturation']);
			$this->setFood($this->foodLevel);

			switch ($slot->getId()) {
				case Item::BEETROOT_SOUP:
				case Item::MUSHROOM_STEW:
				case Item::RABBIT_STEW:
					$this->inventory->addItem(Item::get(Item::BOWL, 0, 1));
					break;
				case Item::GOLDEN_APPLE:
					$this->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(1)->setDuration(5 * 20));
					$this->addEffect(Effect::getEffect(Effect::ABSORPTION)->setAmplifier(0)->setDuration(120 * 20));
					break;
				case Item::ENCHANTED_GOLDEN_APPLE:
					$this->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(4)->setDuration(30 * 20));
					$this->addEffect(Effect::getEffect(Effect::ABSORPTION)->setAmplifier(0)->setDuration(120 * 20));
					$this->addEffect(Effect::getEffect(Effect::DAMAGE_RESISTANCE)->setAmplifier(0)->setDuration(300 * 20));
					$this->addEffect(Effect::getEffect(Effect::FIRE_RESISTANCE)->setAmplifier(0)->setDuration(300 * 20));
					break;
			}
		}
	}

	/**
	 * Handles a Minecraft packet
	 * TODO: Separate all of this in handlers
	 *
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param DataPacket $packet
	 */
	public function handleDataPacket(DataPacket $packet){
		if($this->connected === false){
			return;
		}

		$timings = Timings::getReceiveDataPacketTimings($packet);
		$timings->startTiming();

	    $this->server->getPluginManager()->callEvent($ev = new DataPacketReceiveEvent($this, $packet));
		if($ev->isCancelled()){
		    $timings->stopTiming();
			return;
		}

		$beforeLoginAvailablePackets = ['LOGIN_PACKET', 'REQUEST_CHUNK_RADIUS_PACKET', 'RESOURCE_PACKS_CLIENT_RESPONSE_PACKET', 'CLIENT_TO_SERVER_HANDSHAKE_PACKET', 'RESOURCE_PACK_CHUNK_REQUEST_PACKET'];
		if (!$this->isOnline() && !in_array($packet->pname(), $beforeLoginAvailablePackets)) {
		    $timings->stopTiming();
			return;
		}

		switch($packet->pname()){
            case 'ADVENTURE_SETTINGS_PACKET':
				if ($this->allowFlight === false && (($packet->flags >> 9) & 0x01 === 1)) { // flying hack
                    file_put_contents("./logs/possible_hacks.log", date('m/d/Y h:i:s a', time()) . " ADVENTURE_SETTINGS_PACKET FLY" . $this->username . PHP_EOL, FILE_APPEND | LOCK_EX);
//                    $this->kick("Sorry, hack mods are not permitted on Steadfast... at all.");
					// it may be not safe
					$this->setAllowFlight(false);
                }
				if (!$this->isSpectator() && (($packet->flags >> 7) & 0x01 === 1)) { // spectator hack
                    file_put_contents("./logs/possible_hacks.log", date('m/d/Y h:i:s a', time()) . " ADVENTURE_SETTINGS_PACKET SPC" . $this->username . PHP_EOL, FILE_APPEND | LOCK_EX);
                    $this->kick("§dЧиты недоступны тут.");
                }
				$isFlying = ($packet->flags >> 9) & 0x01 === 1;
				if ($this->isFlying != $isFlying) {
					if ($isFlying) {
						$this->onStartFly();
					} else {
						$this->onStopFly();
					}
					$this->isFlying = $isFlying;
				}
                break;
	    	case 'LOGIN_PACKET':
				if($this->loggedIn){
					break;
				}

				if($packet->isValidProtocol === false) {
					$this->close("", $this->getNonValidProtocolMessage($this->protocol));
					break;
				}

                if(!self::isValidUserName($packet->username)){
                    $this->close("", "Смените свой ник.");
                    break;
                }

	        	static $allowedSkinSize = [
		        	8192, // argb 64x32
		        	16384, // argb 64x64
		        	32768, // argb 128x64
		        	65536, // argb 128x128
	        	];

	        	if (!in_array(strlen($packet->skin), $allowedSkinSize)) {
		        	$this->close("", "Смените скин.");
		        	break;
	        	}

	        	if (count($this->server->getOnlinePlayers()) >= $this->server->getMaxPlayers() && $this->kickOnFullServer()) {
			        $this->close("", "Сервер полон.");
			        break;
	        	}

				$this->protocol = $packet->protocol1; // we need protocol for correct encoding DisconnectPacket
				$this->uuid = $packet->clientUUID;
				$this->username = TextFormat::clean($packet->username);

				if (is_null($this->uuid)) {
					$this->close("", "§cВерсия Вашего клиента сломана.");
					break;
				}

				if(!$this->checkUUID($this->uuid)){
				    break;
				}

				$this->inventory = Multiversion::getPlayerInventory($this); // 2-5% нагрузки в таймингах
				$this->displayName = $this->username;
				$this->setNameTag($this->username);
				$this->iusername = strtolower($this->username);
				$this->randomClientId = $packet->clientId;
				$this->rawUUID = $this->uuid->toBinary();
				$this->clientSecret = $packet->clientSecret;
				$this->setSkin($packet->skin, $packet->skinName, $packet->skinGeometryName, $packet->skinGeometryData, $packet->capeData, $packet->premiumSkin);
                if ($packet->osType > 0) {
                    $this->deviceType = $packet->osType;
                }
                if ($packet->inventoryType >= 0) {
                    $this->inventoryType = $packet->inventoryType;
                }
                $this->deviceModel = $packet->deviceModel;
                $this->xuid = $packet->xuid;
				$this->languageCode = $packet->languageCode;
				$this->clientVersion = $packet->clientVersion;
				$this->originalProtocol = $packet->originalProtocol;

				$this->platformChatId = $packet->platformChatId;
				$this->additionalSkinData = $packet->additionalSkinData;

	        	$this->server->getPluginManager()->callEvent($ev = new PlayerPreLoginEvent($this, "§fПожалуйста, перезайдите!"));
	        	if ($ev->isCancelled()) {
		        	$this->close("", $ev->getKickMessage());
		        	break;
	        	}

				$this->processLogin();
				break;
			case 'MOVE_PLAYER_PACKET':
				if ($this->dead !== true && $this->spawned === true) {
					$newPos = new Vector3($packet->x, $packet->y - $this->getEyeHeight(), $packet->z);
					if ($this->isTeleporting && $newPos->distanceSquared($this) > 2) {
						$this->isTeleporting = false;
						return;
					} else {
						if (!is_null($this->newPosition)) {
							$distanceSquared = ($newPos->x - $this->newPosition->x) ** 2 + ($newPos->z - $this->newPosition->z) ** 2;
						} else {
							$distanceSquared = ($newPos->x - $this->x) ** 2 + ($newPos->z - $this->z) ** 2;
						}
						if ($distanceSquared > $this->movementSpeed * 200 * ($this->countMovePacketInLastTick > 0 ? $this->countMovePacketInLastTick : 1)) {
							$this->revertMovement($this, $this->yaw, $this->pitch);
							$this->isTeleporting = true;
							return;
						}

						$this->isTeleporting = false;

						$packet->yaw %= 360;
						$packet->pitch %= 360;

						if ($packet->yaw < 0) {
							$packet->yaw += 360;
						}

						$this->setRotation($packet->yaw, $packet->pitch);
						$this->newPosition = $newPos;
					}
				}
				break;
			case 'MOVE_ENTITY_PACKET':
				if (!is_null($this->currentVehicle) && $this->currentVehicle->getId() == $packet->eid) {
					$this->currentVehicle->updateByOwner($packet->x, $packet->y, $packet->z, $packet->yaw, $packet->pitch);
				}
				break;
			case 'MOB_EQUIPMENT_PACKET':
				if($this->spawned === false or $this->dead === true){
					break;
				}

				/*if ($packet->windowId == Win10InvLogic::WINDOW_ID_PLAYER_OFFHAND) {
					if ($this->protocol >= ProtocolInfo::PROTOCOL_120) {
						break;
					}
					if ($this->inventoryType == self::INVENTORY_CLASSIC) {
						Win10InvLogic::packetHandler($packet, $this);
						break;
					} else {
						$slot = PlayerInventory::OFFHAND_ARMOR_SLOT_ID;
						$currentArmor = $this->inventory->getArmorItem($slot);
						$slot += $this->inventory->getSize();
						$transaction = new BaseTransaction($this->inventory, $slot, $currentArmor, $packet->item);
						$oldItem = $transaction->getSourceItem();
						$newItem = $transaction->getTargetItem();
						if ($oldItem->deepEquals($newItem) && $oldItem->getCount() === $newItem->getCount()) {
							break;
						}
						$this->addTransaction($transaction);
						break;
					}
				}*/

				if ($this->protocol < ProtocolInfo::PROTOCOL_200) {
					if($packet->slot === 0 or $packet->slot === 255){ //0 for 0.8.0 compatibility
						$packet->slot = -1; //Air
					}else{
						$packet->slot -= 9; //Get real block slot
					}
				}

				// not so good solution
				/*if ($this->inventoryType == self::INVENTORY_CLASSIC && $this->protocol < ProtocolInfo::PROTOCOL_120) {
					Win10InvLogic::packetHandler($packet, $this);
					break;
				}*/
				$item = $this->inventory->getItem($packet->slot);
				$slot = $packet->slot;

				if($packet->slot === -1){ //Air
					if ($packet->selectedSlot >= 0 and $packet->selectedSlot < 9) {
						$this->changeHeldItem($packet->item, $packet->selectedSlot, $packet->slot);
						break;
					} else {
						$this->inventory->sendContents($this);
						break;
					}
				}elseif($item === null || $slot === -1 || ($item->getId() != Item::FILLED_MAP && !$item->deepEquals($packet->item) || !$item->deepEquals($packet->item, true, false))){ // packet error or not implemented
					// hack for map was added because type of map_uuid is different in various versions
					$this->inventory->sendContents($this);
					break;
				}else{
					if ($packet->selectedSlot >= 0 and $packet->selectedSlot < 9) {
						$this->changeHeldItem($packet->item, $packet->selectedSlot, $slot);
						break;
					} else {
						$this->inventory->sendContents($this);
						break;
					}
				}

				$this->inventory->sendHeldItem($this->hasSpawned);

				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
				break;
			case 'LEVEL_SOUND_EVENT_PACKET':
				if ($packet->eventId == LevelSoundEventPacket::SOUND_UNDEFINED) {
					break;
				}
				$viewers = $this->getViewers();
				$viewers[] = $this;
				Server::broadcastPacket($viewers, $packet);
				break;
			case 'USE_ITEM_PACKET':
				if($this->spawned === false or $this->dead === true){
					break;
				}
				$blockPosition = [ 'x' => $packet->x, 'y' => $packet->y, 'z' => $packet->z ];
				$clickPosition = [ 'x' => $packet->fx, 'y' => $packet->fy, 'z' => $packet->fz ];
				$this->useItem($packet->item, $packet->hotbarSlot, $packet->face, $blockPosition, $clickPosition);
				break;
			case 'PLAYER_ACTION_PACKET':
				if($this->spawned === false){
					break;
				}

				$this->craftingType = self::CRAFTING_DEFAULT;
				$action = MultiversionEnums::getPlayerAction($this->protocol, $packet->action);
				switch ($action) {
				    case 'CHANGE_DEMENSION_ACK':
				        break;
					case 'START_JUMP':
						if ($this->foodLevel > 0 && $this->getFoodEnabled() && $this->isSurvival()) {
							$this->exhaustion += $this->isSprinting() ? 0.2 : 0.05;
						}
						$this->onJump();
						break;
					case 'START_DESTROY_BLOCK':
						$this->actionsNum['CRACK_BLOCK'] = 0;
						$block = $this->level->getBlock(new Vector3($packet->x, $packet->y, $packet->z));
						$fireBlock = $block->getSide($packet->face);
						if ($fireBlock->getId() === Block::FIRE) {
							$fireBlock->onUpdateBlock(Level::BLOCK_UPDATE_TOUCH);
						}
						if (!$this->isCreative()) {
							$breakTime = ceil($this->getBreakTime($block) * 20);
							if ($breakTime > 0) {
								$pk = new LevelEventPacket();
								$pk->evid = LevelEventPacket::EVENT_START_BLOCK_CRACKING;
								$pk->x = $packet->x;
								$pk->y = $packet->y;
								$pk->z = $packet->z;
								$pk->data = (int) (65535 / $breakTime); // ????
								$viewers = $this->getViewers();
								$viewers[] = $this;
								Server::broadcastPacket($viewers, $pk);
							}
						}
						break;
					case 'ABORT_DESTROY_BLOCK':
					case 'STOP_DESTROY_BLOCK':
						$this->actionsNum['CRACK_BLOCK'] = 0;
						$pk = new LevelEventPacket();
						$pk->evid = LevelEventPacket::EVENT_STOP_BLOCK_CRACKING;
						$pk->x = $packet->x;
						$pk->y = $packet->y;
						$pk->z = $packet->z;
						$viewers = $this->getViewers();
						$viewers[] = $this;
						Server::broadcastPacket($viewers, $pk);
						break;
					case 'RELEASE_USE_ITEM':
						$this->releaseUseItem();
						$this->startAction = -1;
						break;
					case 'STOP_SLEEPING':
						$this->stopSleep();
						break;
					case 'CHANGE_DEMENSION':
					case 'RESPAWN':
			        	if($this->isAlive()){
				        	break;
			        	}
						if ($this->server->isHardcore()) {
							$this->setBanned(true);
							break;
						}
						$this->respawn();
						break;
					case 'START_SPRINTING':
						$this->setSprinting(true);
						break;
					case 'STOP_STRINTING':
						$this->setSprinting(false);
						break;
					case 'START_SNEAKING':
						$ev = new PlayerToggleSneakEvent($this, true);
						$this->server->getPluginManager()->callEvent($ev);
						if($ev->isCancelled()){
							$this->sendData($this);
						}else{
							$this->setSneaking(true);
						}
						break;
					case 'STOP_SNEAKING':
						$ev = new PlayerToggleSneakEvent($this, false);
						$this->server->getPluginManager()->callEvent($ev);
						if($ev->isCancelled()){
							$this->sendData($this);
						}else{
							$this->setSneaking(false);
						}
						break;
					case 'START_GLIDING':
						if ($this->isHaveElytra()) {
							$this->setFlyingFlag(true);
							$this->setElytraActivated(false);
						}
						break;
					case 'STOP_GLIDING':
						$this->setFlyingFlag(false);
						$this->elytraIsActivated = false;
						break;
					case 'CRACK_BLOCK':
						$this->crackBlock($packet);
						break;
					case 'INTERACT_WITH_BLOCK':
						//TODO:
						break;
				}

				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
				break;
			case 'REMOVE_BLOCK_PACKET':
				$this->breakBlock([ 'x' => $packet->x, 'y' => $packet->y, 'z' => $packet->z ]);
				break;
			case 'MOB_ARMOR_EQUIPMENT_PACKET':
				break;
		case 'INTERACT_PACKET':
				if ($packet->action === InteractPacket::ACTION_OPEN_INVENTORY && $this->getPlayerProtocol() >= ProtocolInfo::PROTOCOL_392) {
					$this->addWindow($this->getInventory());
				} elseif ($packet->action === InteractPacket::ACTION_DAMAGE) {
					$this->attackByTargetId($packet->target);
					if ($target instanceof Vehicle) {
							if(!$target instanceof Player){
								$target->mount($this);
							}
						}

				} elseif ($packet->action === InteractPacket::ACTION_SEE) {
					$target = $this->getLevel()->getEntity($packet->target);
					if ($target instanceof Vehicle) {
						$target->onNearPlayer($this);
					}
				} else {
					if ($packet->action === 3) {
						$target = $this->getLevel()->getEntity($packet->target);
						if ($target instanceof Vehicle) {
							$target->dissMount();
						}
					}
					$this->customInteract($packet);
				}
				if($packet->action === InteractPacket::ACTION_RIGHT_CLICK){
					$target = $this->getLevel()->getEntity($packet->target);
						if ($target instanceof Vehicle) {
							if(!$target instanceof Player){
								$target->mount($this);
							}
						}
				}
				break;
			case 'ANIMATE_PACKET':
				if ($this->spawned === false or $this->dead === true) {
					break;
				}

				if($packet->eid !== $this->getId()){
					$this->close("", "Недействительный сеанс. Причина: Не удалось проверить подпись.");
					$this->server->getNetwork()->blockAddress($this->getAddress(), 1200);
					break;
				}

				$this->server->getPluginManager()->callEvent($ev = new PlayerAnimationEvent($this, $packet->action));
				if ($ev->isCancelled()) {
					break;
				}

				$pk = new AnimatePacket();
				$pk->eid = $packet->eid;
				$pk->action = $ev->getAnimationType();
				$pk->data = $packet->data;
				Server::broadcastPacket($this->getViewers(), $pk);
				break;
			case 'ENTITY_EVENT_PACKET':
				if($this->spawned === false or $this->dead === true){
					break;
				}

				$this->craftingType = self::CRAFTING_DEFAULT;

				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false); //TODO: check if this should be true

				switch($packet->event){
					case EntityEventPacket::USE_ITEM: //Eating
						$slot = $this->inventory->getItemInHand();
						if($slot instanceof Potion && $slot->canBeConsumed()){
							$ev = new PlayerItemConsumeEvent($this, $slot);
							$this->server->getPluginManager()->callEvent($ev);
							if(!$ev->isCancelled()){
								$slot->onConsume($this);
							}else{
								$this->inventory->sendContents($this);
							}
						} else {
							$this->eatFoodInHand();
						}
						break;
					case EntityEventPacket::ENCHANT:
						if ($this->currentWindow instanceof EnchantInventory) {
							$enchantLevel = abs($packet->theThing);
								if ($this->expLevel >= $enchantLevel) {
								    $this->removeExperience(0, $enchantLevel);
								    if ($this->protocol >= ProtocolInfo::PROTOCOL_120) {
									    $this->currentWindow->setEnchantingLevel($enchantLevel);
									    return;
								    }
								    $items = $this->inventory->getContents();
								    foreach ($items as $slot => $item) {
									    if ($item->getId() === Item::DYE && $item->getDamage() === 4 && $item->getCount() >= $enchantLevel) {
										    break 2;
							            }
								    }
							    }

							    $this->currentWindow->setItem(0, Item::get(Item::AIR));
							    $this->currentWindow->setEnchantingLevel(0);
							    $this->currentWindow->sendContents($this);
							    $this->inventory->sendContents($this);
						}
						break;
					case EntityEventPacket::FEED:
						$position = [ 'x' => $this->x, 'y' => $this->y, 'z' => $this->z ];
						$this->sendSound(LevelSoundEventPacket::SOUND_EAT, $position);
						break;
				}
				break;
			case 'DROP_ITEM_PACKET':
				if($this->spawned === false or $this->dead === true){
					break;
				}

				/*if ($this->inventoryType == self::INVENTORY_CLASSIC && $this->protocol < ProtocolInfo::PROTOCOL_120 && !$this->isCreative()) {
					Win10InvLogic::packetHandler($packet, $this);
				}*/

				$slot = $this->inventory->first($packet->item);
				if ($slot == -1) {
					$this->inventory->sendContents($this);
					break;
				}
				if ($this->isSpectator()) {
					$this->inventory->sendSlot($slot, $this);
					break;
				}
				$item = $this->inventory->getItem($slot);
				$ev = new PlayerDropItemEvent($this, $packet->item);
				$this->server->getPluginManager()->callEvent($ev);
				if($ev->isCancelled()){
					$this->inventory->sendSlot($slot, $this);
					$this->inventory->setHotbarSlotIndex($slot, $slot);
					$this->inventory->sendContents($this);
					break;
				}

				$remainingCount = $item->getCount() - $packet->item->getCount();
				if ($remainingCount > 0) {
					$item->setCount($remainingCount);
					$this->inventory->setItem($slot, $item);
				} else {
					$this->inventory->setItem($slot, Item::get(Item::AIR));
				}

				$motion = $this->getDirectionVector()->multiply(0.4);
				$this->level->dropItem($this->add(0, 1.3, 0), $packet->item, $motion, 40);
				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
				$this->inventory->sendContents($this);
				break;
			case 'TEXT_PACKET':
				if($this->spawned === false or $this->dead === true){
					break;
				}

				$this->craftingType = self::CRAFTING_DEFAULT;

				if($packet->type === TextPacket::TYPE_CHAT){
					if(!$this->checkStrlen($packet->message)){
						//фикс от атак с отправлением большого сообщения в TextPacket,е
						break;
					}

					$this->chat(TextFormat::clean($packet->message, $this->removeFormat));
				}
				break;
			case 'CONTAINER_CLOSE_PACKET':
				if ($this->spawned === false || $packet->windowid === 0){
					break;
				}
				$this->craftingType = self::CRAFTING_DEFAULT;
				$this->currentTransaction = null;
				// @todo добавить обычный инвентарь и броню
				if ($packet->windowid === $this->currentWindowId && $this->currentWindow != null) {
					$this->server->getPluginManager()->callEvent(new InventoryCloseEvent($this->currentWindow, $this));
					$this->removeWindow($this->currentWindow);
				}
				if ($this->protocol >= Info::PROTOCOL_120) {
					// duck tape
					if ($packet->windowid == 0xff) { // player inventory and workbench
						$this->onCloseSelfInventory();
						$this->inventory->close($this);
					}
				}
				break;
			case 'CRAFTING_EVENT_PACKET':
				if ($this->spawned === false or $this->dead) {
					break;
				}
				if ($packet->windowId > 0 && $packet->windowId !== $this->currentWindowId) {
					$this->inventory->sendContents($this);
					$pk = new ContainerClosePacket();
					$pk->windowid = $packet->windowId;
					$this->dataPacket($pk);
					break;
				}

				$recipe = $this->server->getCraftingManager()->getRecipe($packet->id);
				$result = $packet->output[0];

				if (!($result instanceof Item)) {
					$this->inventory->sendContents($this);
					break;
				}

				if (is_null($recipe) || !$result->deepEquals($recipe->getResult(), true, false) ) { //hack for win10
					$newRecipe = $this->server->getCraftingManager()->getRecipeByHash($result->getId() . ":" . $result->getDamage());
					if (!is_null($newRecipe)) {
						$recipe = $newRecipe;
					}
				}

				if ($this->protocol >= ProtocolInfo::PROTOCOL_120) {
					try {
						$scale = floor($packet->output[0]->getCount() / $recipe->getResult()->getCount());
						if ($scale > 1) {
							$recipe = clone $recipe;
							$recipe->scale($scale);
						}
						if ($this->inventory->isQuickCraftEnabled()) {
							$craftSlots = $this->inventory->getQuckCraftContents();
							$this->tryApplyQuickCraft($craftSlots, $recipe);
							$this->inventory->setItem(PlayerInventory120::CRAFT_RESULT_INDEX, $recipe->getResult());
							foreach ($craftSlots as $slot => $item) {
								$this->inventory->setItem(PlayerInventory120::QUICK_CRAFT_INDEX_OFFSET - $slot, $item);
							}
						    $this->inventory->setQuickCraftMode(false);
						} else {
							$craftSlots = $this->inventory->getCraftContents();
							$this->tryApplyCraft($craftSlots, $recipe);
							$this->inventory->setItem(PlayerInventory120::CRAFT_RESULT_INDEX, $recipe->getResult());
							foreach ($craftSlots as $slot => $item) {
								$this->inventory->setItem(PlayerInventory120::CRAFT_INDEX_0 - $slot, $item);
							}
						}
				    	if (!empty($this->lastQuickCraftTransactionGroup)) {
					    	foreach ($this->lastQuickCraftTransactionGroup as $trGroup) {
						    	if (!$trGroup->execute()) {
							    	$trGroup->sendInventories();
						    	}
					    	}
					    	$this->lastQuickCraftTransactionGroup = [];
				    	}
					} catch (\Exception $e) {
						$pk = new ContainerClosePacket();
						$pk->windowid = ContainerSetContentPacket::SPECIAL_INVENTORY;
						$this->dataPacket($pk);
				    	$this->lastQuickCraftTransactionGroup = [];
					}
					return;
				}

				// переделать эту проверку
				if ($recipe === null || (($recipe instanceof BigShapelessRecipe || $recipe instanceof BigShapedRecipe) && $this->craftingType === self::CRAFTING_DEFAULT)) {
					$this->inventory->sendContents($this);
					break;
				}

//				foreach($packet->input as $i => $item){
//					if($item->getDamage() === -1 or $item->getDamage() === 0x7fff){
//						$item->setDamage(null);
//					}
//
//					if($i < 9 and $item->getId() > 0){
//						$item->setCount(1);
//					}
//				}

				$canCraft = true;


				/** @var Item[] $ingredients */
				$ingredients = [];
				if ($recipe instanceof ShapedRecipe) {
					$ingredientMap = $recipe->getIngredientMap();
					foreach ($ingredientMap as $row) {
						$ingredients = array_merge($ingredients, $row);
					}
				} else if ($recipe instanceof ShapelessRecipe) {
					$ingredients = $recipe->getIngredientList();
				} else {
					$canCraft = false;
				}

				if(!$canCraft || !$result->deepEquals($recipe->getResult(), true, false)){
					$this->server->getLogger()->debug("Unmatched recipe ". $recipe->getId() ." from player ". $this->getName() .": expected " . $recipe->getResult() . ", got ". $result .", using: " . implode(", ", $ingredients));
					$this->inventory->sendContents($this);
					break;
				}

				$used = array_fill(0, $this->inventory->getSize() + 5, 0);

				$playerInventoryItems = $this->inventory->getContents();
				foreach ($ingredients as $ingredient) {
					$slot = -1;
					foreach ($playerInventoryItems as $index => $i) {
						if ($ingredient->getId() !== Item::AIR && $ingredient->deepEquals($i, (!is_null($ingredient->getDamage()) && $ingredient->getDamage() != 0x7fff), false) && ($i->getCount() - $used[$index]) >= 1) {
							$slot = $index;
							$used[$index]++;
							break;
						}
					}

					if($ingredient->getId() !== Item::AIR and $slot === -1){
						$canCraft = false;
						break;
					}
				}

				if(!$canCraft){
					$this->server->getLogger()->debug("Unmatched recipe ". $recipe->getId() ." from player ". $this->getName() .": client does not have enough items, using: " . implode(", ", $ingredients));
					$this->inventory->sendContents($this);
					break;
				}
				$this->server->getPluginManager()->callEvent($ev = new CraftItemEvent($ingredients, $recipe, $this));

				if($ev->isCancelled()){
					$this->inventory->sendContents($this);
					break;
				}

				foreach($used as $slot => $count){
					if($count === 0){
						continue;
					}

					$item = $playerInventoryItems[$slot];

					if($item->getCount() > $count){
						$newItem = clone $item;
						$newItem->setCount($item->getCount() - $count);
					}else{
						$newItem = Item::get(Item::AIR, 0, 0);
					}

					$this->inventory->setItem($slot, $newItem);
				}

				$extraItem = $this->inventory->addItem($recipe->getResult());
				if(count($extraItem) > 0){
					foreach($extraItem as $item){
						$this->level->dropItem($this, $item);
					}
				}
				$this->inventory->sendContents($this);

				break;

			case 'CONTAINER_SET_SLOT_PACKET':
				$isPlayerNotNormal = $this->spawned === false || !$this->isAlive();
				if ($isPlayerNotNormal || $packet->slot < 0) {
					break;
				}

				/*if ($this->inventoryType == self::INVENTORY_CLASSIC && $this->protocol < ProtocolInfo::PROTOCOL_120 && !$this->isCreative()) {
					Win10InvLogic::packetHandler($packet, $this);
					break;
				}*/

				if ($packet->windowid === 0) { //Our inventory
					if ($packet->slot >= $this->inventory->getSize()) {
						break;
					}
					if ($this->isCreative() && !$this->isSpectator() && Item::getCreativeItemIndex($packet->item) !== -1) {
						$this->inventory->setItem($packet->slot, $packet->item);
						$this->inventory->setHotbarSlotIndex($packet->slot, $packet->slot); //links $hotbar[$packet->slot] to $slots[$packet->slot]
					}
					$transaction = new BaseTransaction($this->inventory, $packet->slot, $this->inventory->getItem($packet->slot), $packet->item);
				} else if ($packet->windowid === ContainerSetContentPacket::SPECIAL_ARMOR) { //Our armor
					if ($packet->slot >= 4) {
						break;
					}

					$currentArmor = $this->inventory->getArmorItem($packet->slot);
					$slot = $packet->slot + $this->inventory->getSize();
					$transaction = new BaseTransaction($this->inventory, $slot, $currentArmor, $packet->item);
				} else if ($packet->windowid === $this->currentWindowId) {
					$this->craftingType = self::CRAFTING_DEFAULT;
					$inv = $this->currentWindow;
					$transaction = new BaseTransaction($inv, $packet->slot, $inv->getItem($packet->slot), $packet->item);
				}else{
					break;
				}

				$oldItem = $transaction->getSourceItem();
				$newItem = $transaction->getTargetItem();
				if ($oldItem->deepEquals($newItem) && $oldItem->getCount() === $newItem->getCount()) { //No changes!
					//No changes, just a local inventory update sent by the server
					break;
				}

				if ($this->craftingType === self::CRAFTING_ENCHANT) {
					if ($this->currentWindow instanceof EnchantInventory) {
						$this->enchantTransaction($transaction);
					}
				} else {
					$this->addTransaction($transaction);
				}
				break;
		    case 'TILE_ENTITY_DATA_PACKET':
				if($this->spawned === false or $this->dead === true){
					break;
				}

				$this->craftingType = self::CRAFTING_DEFAULT;

				$pos = new Vector3($packet->x, $packet->y, $packet->z);
				if($pos->distanceSquared($this) > 10000){
					break;
				}

				$t = $this->level->getTile($pos);
				if ($t instanceof Sign) {
					$nbt = new NBT(NBT::LITTLE_ENDIAN);
					$nbt->read($packet->namedtag, false, true);
					$nbt = $nbt->getData();
					if(!$t->updateCompound($nbt, $this)){
						$t->spawnTo($this);
					}
				}
		        break;
			case 'REQUEST_CHUNK_RADIUS_PACKET':
			    if (($radius = $packet->radius) >= 101) {
				    $this->close("", "Слишком большой радиус прогрузки чанка.");
				    $this->server->getNetwork()->blockAddress($this->getAddress(), 3000);
				    break;
			    }
			    
			    $this->setViewDistance($radius);
				break;
			case 'COMMAND_STEP_PACKET':
				if($this->spawned === false or !$this->isAlive()){
					break;
				}

				$this->craftingType = 0;

				$commandText = $packet->command;
				if($packet->inputJson !== null){
					if ($this->isCountable($packet->inputJson)) {
					    if(count($packet->inputJson) > 15){
						    $this->close("", "Недействительный сеанс. Причина: Не удалось проверить подпись.");
						    $this->server->getNetwork()->blockAddress($this->getAddress(), 1200);
						    break;
						}
					}

					foreach($packet->inputJson as $arg){ //command ordering will be an issue
						if(!is_object($arg)) //anti bot
							$commandText .= " " . $arg;
					}
				}

                $this->chat("/" . $commandText);
				break;
			case 'RESOURCE_PACKS_CLIENT_RESPONSE_PACKET':
				switch ($packet->status) {
					case ResourcePackClientResponsePacket::STATUS_REFUSED:
						$this->close("", "Загрузите ресурспаки!");
						break;
					case ResourcePackClientResponsePacket::STATUS_SEND_PACKS:
						$manager = $this->server->getResourcePackManager();
						foreach($packet->packIds as $uuid){
							//dirty hack for mojang's dirty hack for versions
					        /*$splitPos = strpos($uuid, "_");
					        if($splitPos !== false){
						        $uuid = substr($uuid, 0, $splitPos);
					        }*/

							$pack = $manager->getPackById($uuid);
							if(!($pack instanceof ResourcePack)){
								//Client requested a resource pack but we don't have it available on the server
								$this->close("", $this->server->getLanguage()->translateString("disconnectionScreen.unavailableResourcePack"));
								$this->server->getLogger()->debug("Got a resource pack request for unknown pack with UUID " . $uuid . ", available packs: " . implode(", ", $manager->getPackIdList()));

								return false;
							}

							$pk = new ResourcePackDataInfoPacket();
							$pk->packId = $pack->getPackId();
							$pk->maxChunkSize = self::RESOURCE_PACK_CHUNK_SIZE;
							$pk->chunkCount = (int) ceil($pack->getPackSize() / $pk->maxChunkSize);
							$pk->compressedPackSize = $pack->getPackSize();
							$pk->sha256 = $pack->getSha256();
							$this->dataPacket($pk);
						}
						break;
					case ResourcePackClientResponsePacket::STATUS_HAVE_ALL_PACKS:
					    $this->antibot["HAVE_ALL_PACKS"] = true;
						$pk = new ResourcePackStackPacket();
						$manager = $this->server->getResourcePackManager();
						$pk->resourcePackStack = $manager->getResourceStack();
						//we don't force here, because it doesn't have user-facing effects
				        //but it does have an annoying side-effect when true: it makes
				        //the client remove its own non-server-supplied resource packs.
				        $pk->mustAccept = false;
						$this->dataPacket($pk);
						break;
					case ResourcePackClientResponsePacket::STATUS_COMPLETED:
					    if (isset($this->antibot["HAVE_ALL_PACKS"])) {
					    	$this->completeLoginSequence();
					    }
						break;
					default:
						break;
				}
				break;
			case "RESOURCE_PACK_CHUNK_REQUEST_PACKET":
				$manager = $this->server->getResourcePackManager();
				$pack = $manager->getPackById($packet->packId);
				if(!($pack instanceof ResourcePack)){
					$this->close("", "disconnectionScreen.resourcePack");
					return true;
				}

				$pk = new ResourcePackChunkDataPacket();
				$pk->packId = $pack->getPackId();
				$pk->chunkIndex = $packet->chunkIndex;
				$pk->data = $pack->getPackChunk(self::RESOURCE_PACK_CHUNK_SIZE * $packet->chunkIndex, self::RESOURCE_PACK_CHUNK_SIZE);
		        $pk->progress = (self::RESOURCE_PACK_CHUNK_SIZE * $packet->chunkIndex);
				$this->dataPacket($pk);
				break;
			/** @minProtocol 120 */
			case 'INVENTORY_TRANSACTION_PACKET':
				switch ($packet->transactionType) {
					case InventoryTransactionPacket::TRANSACTION_TYPE_INVENTORY_MISMATCH:
					    $this->sendAllInventories();
						break;
					case InventoryTransactionPacket::TRANSACTION_TYPE_NORMAL:
						$this->normalTransactionLogic($packet);
						break;
					case InventoryTransactionPacket::TRANSACTION_TYPE_ITEM_USE_ON_ENTITY:
						switch ($packet->actionType) {
							case InventoryTransactionPacket::ITEM_USE_ON_ENTITY_ACTION_ATTACK:
								$this->attackByTargetId($packet->entityId);
								break;
							case InventoryTransactionPacket::ITEM_USE_ON_ENTITY_ACTION_INTERACT:
								$target = $this->getLevel()->getEntity($packet->entityId);
								if ($target instanceof Vehicle) {
									$target->onPlayerInteract($this);
								} elseif (!is_null($target)) {
									$target->interact($this);
								}
								break;
						}
						break;
					case InventoryTransactionPacket::TRANSACTION_TYPE_ITEM_USE:
						switch ($packet->actionType) {
							case InventoryTransactionPacket::ITEM_USE_ACTION_PLACE:
								$blockHash = $packet->position['x'] . ':' . $packet->position['y'] . ':' . $packet->position['z']. ':' . $packet->face;
								if ($this->lastUpdate - $this->lastInteractTick < 3 && $this->lastInteractCoordsHash == $blockHash) {
									break;
								}
								$this->lastInteractTick = $this->lastUpdate;
								$this->lastInteractCoordsHash = $blockHash;
							case InventoryTransactionPacket::ITEM_USE_ACTION_USE:
								$this->useItem($packet->item, $packet->slot, $packet->face, $packet->position, $packet->clickPosition);
								break;
							case InventoryTransactionPacket::ITEM_USE_ACTION_DESTROY:
								$this->breakBlock($packet->position);
								break;
							default:
								error_log('[TRANSACTION_TYPE_ITEM_USE] Wrong actionType ' . $packet->actionType);
								break;
						}
						break;
					case InventoryTransactionPacket::TRANSACTION_TYPE_ITEM_RELEASE:
						switch ($packet->actionType) {
							case InventoryTransactionPacket::ITEM_RELEASE_ACTION_RELEASE:
								$this->releaseUseItem();
								$this->startAction = -1;
								break;
							case InventoryTransactionPacket::ITEM_RELEASE_ACTION_USE:
								$this->useItem120();
								$this->startAction = -1;
								break;
							default:
								error_log('[TRANSACTION_TYPE_ITEM_RELEASE] Wrong actionType ' . $packet->actionType);
								break;
						}
						break;
					default:
						error_log('Wrong transactionType ' . $packet->transactionType);
						break;
				}
				break;
			/** @minProtocol 120 */
			case 'COMMAND_REQUEST_PACKET':
			    if(!$this->checkStrlen($packet->command)){
			        break;
			    }
			    $this->chat($packet->command);
				break;
			/** @minProtocol 120 */
			case 'PLAYER_SKIN_PACKET':
				if ($this->setSkin($packet->newSkinByteData, $packet->newSkinId, $packet->newSkinGeometryName, $packet->newSkinGeometryData, $packet->newCapeByteData, $packet->isPremiumSkin, true)) {
					$this->additionalSkinData = $packet->additionalSkinData;
					
	            	$pk = new RemoveEntityPacket();
	            	$pk->eid = $this->getId();
	            	
	            	$pk2 = new PlayerListPacket();
	            	$pk2->type = PlayerListPacket::TYPE_REMOVE;
	            	$pk2->entries[] = [$this->getUniqueId()];
	            	
	            	$pk3 = new AddPlayerPacket();
	            	$pk3->uuid = $this->getUniqueId();
	            	$pk3->username = $this->getName();
	            	$pk3->eid = $this->getId();
	            	$pk3->x = $this->x;
	            	$pk3->y = $this->y;
	            	$pk3->z = $this->z;
	            	$pk3->speedX = $this->motionX;
	            	$pk3->speedY = $this->motionY;
	            	$pk3->speedZ = $this->motionZ;
	            	$pk3->yaw = $this->yaw;
	            	$pk3->pitch = $this->pitch;
	            	$pk3->metadata = $this->dataProperties;
	            	
	            	$oldViewers = [];
	            	$recipients = $this->server->getOnlinePlayers();
	     	        foreach ($recipients as $viewer) {
		            	if ($viewer->getPlayerProtocol() < ProtocolInfo::PROTOCOL_120) {
			            	$oldViewers[] = $viewer;
		            	}
	     	        }
	     	        
	            	if (!empty($oldViewers)) {
		            	$this->server->batchPackets($oldViewers, [$pk, $pk2, $pk3]);
	            	}
	            	
					$this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getDisplayName(), $this->getSkinName(), $this->getSkinData(), $this->getSkinGeometryName(), $this->getSkinGeometryData(), $this->getCapeData(), $this->getOriginalProtocol() >= Info::PROTOCOL_140 ? $this->getXUID() : "", $this->getDeviceOS(), $this->getAdditionalSkinData());
				}
				break;

			/** @minProtocol 120 */
			case 'PURCHASE_RECEIPT_PACKET':
				$event = new PlayerReceiptsReceivedEvent($this, $packet->receipts);
				$this->server->getPluginManager()->callEvent($event);
				break;
			case 'CLIENT_TO_SERVER_HANDSHAKE_PACKET':
				$this->continueLoginProcess();
				break;
			case 'PLAYER_INPUT_PACKET':
				if (!is_null($this->currentVehicle)) {
					$this->currentVehicle->playerMoveVehicle($packet->forward, $packet->sideway);
				} else {
					$this->onPlayerInput($packet->forward, $packet->sideway, $packet->jump, $packet->sneak);
				}
				break;
			case 'MAP_INFO_REQUEST_PACKET':
				$this->onPlayerRequestMap($packet->mapId);
				break;
			case 'EMOTE_PACKET':
			    $pk = new EmotePacket();
			    $pk->eid = $packet->eid;
			    $pk->emoteId = $packet->emoteId;
			    $pk->flags = $packet->flags;
			    foreach($this->getViewers() as $viewer){
			        if($viewer->getOriginalProtocol() >= ProtocolInfo::PROTOCOL_403){
			            $viewer->dataPacket($pk);
			        }
			    }
			    break;
            case 'ITEM_FRAME_DROP_ITEM_PACKET':
                $tile = null;
                $tile = $this->getLevel()->getTile(new Vector3($packet->x, $packet->y, $packet->z));
                if($tile instanceof ItemFrame){
                    if($tile->getItem()->getId() !== Item::AIR){
                        $this->server->getPluginManager()->callEvent($ev = new ItemFrameDropItemEvent($this, $this->getLevel()->getBlock($tile), $tile, $tile->getItem()));
                        if(!$ev->isCancelled()){
                            $tile->getLevel()->dropItem($tile, $tile->getItem());
                            $tile->setItem(Item::get(Item::AIR));
                            $tile->setItemRotation(0);
                        }
                    }
                }
                break;
			case "RESPAWN_PACKET":
				$pk = new RespawnPacket();
				$pos = $this->getSpawn();
				$pk->x = $pos->x;
				$pk->y = $pos->y +  $this->getEyeHeight();
				$pk->z = $pos->z;
				$this->dataPacket($pk);
				break;
			default:
				break;
		}

		$timings->stopTiming();
	}

	protected function respawn() {
		$this->craftingType = self::CRAFTING_DEFAULT;

		$this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $this->getSpawn()));

		$this->teleport($ev->getRespawnPosition(), $ev->getPitch(), $ev->getYaw());

		$this->setSprinting(false, true);
		$this->setSneaking(false);

		$this->extinguish();
		$this->blocksAround = null;
		$this->dataProperties[self::DATA_AIR] = [self::DATA_TYPE_SHORT, 300];
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_NOT_IN_WATER, true, self::DATA_TYPE_LONG, false);
		$this->deadTicks = 0;
		$this->despawnFromAll();
		$this->dead = false;
		$this->isTeleporting = true;
		$this->noDamageTicks = 60;

		$this->setHealth($this->getMaxHealth());
		$this->setFood(20);

		$this->foodTick = 0;
		$this->exhaustion = 0;
		$this->saturation = 5;
		$this->lastSentVitals = 10;

		$this->removeAllEffects();
		$this->sendSelfData();

		$this->sendSettings();
		$this->inventory->sendContents($this);
		$this->inventory->sendArmorContents($this);

		$this->scheduleUpdate();

		$this->server->getPluginManager()->callEvent(new PlayerRespawnAfterEvent($this));

	}

	/**
	 * Kicks a player from the server
	 *
	 * @param string $reason
	 *
	 * @return bool
	 */
	public function kick($reason = "Disconnected from server."){
		$this->server->getPluginManager()->callEvent($ev = new PlayerKickEvent($this, $reason, TextFormat::YELLOW . $this->username . " has left the game"));
		if(!$ev->isCancelled()){
			$this->close($ev->getQuitMessage(), $reason);
			return true;
		}

		return false;
	}

	/** @var string[] */
	private $messageQueue = [];

	/**
	 * Sends a direct chat message to a player
	 *
	 * @param string|TextContainer $message
	 */
	public function sendMessage($message){
		$mes = explode("\n", $message);
		foreach($mes as $m){
			if($m !== ""){
				$this->messageQueue[] = $m;
			}
		}
	}


	public function sendChatMessage($senderName, $message) {
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_CHAT;
		$pk->message = $message;
		$pk->source = $senderName;
		$sender = $this->server->getPlayer($senderName);
		if ($sender !== null && $sender->getOriginalProtocol() >= ProtocolInfo::PROTOCOL_140) {
			$pk->xuid = $sender->getXUID();
		}
		$this->dataPacket($pk);
	}

	public function sendTranslation($message, array $parameters = []){
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_RAW;
		$pk->message = $message;
		$this->dataPacket($pk);
	}

	public function sendPopup($message){
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_POPUP;
		$pk->message = $message;
		$this->dataPacket($pk);
	}

	public function sendTip($message){
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_TIP;
		$pk->message = $message;
		$this->dataPacket($pk);
	}

    private function checkStrlen($message){
        if(mb_strlen($message) > 320){
			$this->close("", "Слишком большое сообщение.");
			$this->server->getNetwork()->blockAddress($this->getAddress(), 100);
            return false;
        }
        return true;
    }

	/**
	 * @param string $message Message to be broadcasted
	 * @param string $reason  Reason showed in console
	 */
	public function close($message = "", $reason = "generic reason"){
        //Win10InvLogic::removeData($this);
		if($this->fishingHook instanceof FishingHook){
			$this->fishingHook->close();
			$this->fishingHook = null;
		}
		if($this->connected and !$this->closed){
			$pk = new DisconnectPacket;
			$pk->message = $reason;
			$this->directDataPacket($pk);
			$this->connected = false;
			if($this->username != ""){
				$this->server->getPluginManager()->callEvent($ev = new PlayerQuitEvent($this, $message, $reason));
				if($this->server->getSavePlayerData() and $this->loggedIn === true){
					$this->save();
				}
			}

			foreach($this->server->getOnlinePlayers() as $player){
				if(!$player->canSee($this)){
					$player->showPlayer($this);
				}
				$player->despawnFrom($this);
			}
			$this->hiddenPlayers = [];
			$this->hiddenEntity = [];

			if (!is_null($this->currentWindow)) {
				$this->removeWindow($this->currentWindow);
			}

			$this->interface->close($this, $reason);

			$chunkX = $chunkZ = null;
			foreach($this->usedChunks as $index => $d){
				Level::getXZ($index, $chunkX, $chunkZ);
				$this->level->freeChunk($chunkX, $chunkZ, $this);
				unset($this->usedChunks[$index]);
				foreach($this->level->getChunkEntities($chunkX, $chunkZ) as $entity){
					$entity->removeClosedViewer($this);
				}
			}

			parent::close();

            if($this->loggedIn){
			    $this->server->removeOnlinePlayer($this);
		    	$this->loggedIn = false;
            }

            $this->stopSleep();

			if(isset($ev) and $this->username !== "" and $this->spawned !== false and $ev->getQuitMessage() !== "" and $ev->getQuitMessage() !== null){
				$this->server->broadcastMessage($ev->getQuitMessage());
			}

			$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
			$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
			$this->spawned = false;
			$this->timestamp = false;
			$this->antibot = null;
			$this->server->getLogger()->info("{$this->username}/{$this->ip} отключился: {$reason}");
			$this->usedChunks = [];
			$this->loadQueue = [];
			$this->hasSpawned = [];
			$this->spawnPosition = null;
			$this->lastMoveBuffer = '';
			$this->deviceModel = null;
			$this->additionalSkinData = [];
			$this->lastQuickCraftTransactionGroup = [];
			$this->titleData = [];
			$this->inventory = null;
			unset($this->buffer);
		}
		if($this->perm !== null){
			$this->perm->clearPermissions();
			$this->perm = null;
		}
		$this->server->removePlayer($this);
	}

	public function __debugInfo(){
		return [];
	}

	/**
	 * Handles player data saving
	 */
	public function save(){
		if($this->closed){
			throw new \InvalidStateException("Tried to save closed player");
		}

		if($this->spawned) {
			parent::saveNBT();
			if($this->level instanceof Level) {
				$this->namedtag->Level = new StringTag("Level", $this->level->getName());
				if($this->spawnPosition instanceof Position and $this->spawnPosition->getLevel() instanceof Level) {
					$this->namedtag["SpawnLevel"] = $this->spawnPosition->getLevel()->getName();
					$this->namedtag["SpawnX"] = (int) $this->spawnPosition->x;
					$this->namedtag["SpawnY"] = (int) $this->spawnPosition->y;
					$this->namedtag["SpawnZ"] = (int) $this->spawnPosition->z;
				}
		        $this->namedtag["playerGameType"] = $this->gamemode;
		    	$this->namedtag["lastPlayed"] = new LongTag("lastPlayed", floor(microtime(true) * 1000));
		    	$this->namedtag["Hunger"] = new ShortTag("Hunger", $this->foodLevel);
		    	$this->namedtag["Health"] = new ShortTag("Health", $this->getHealth());
		    	$this->namedtag["MaxHealth"] = new ShortTag("MaxHealth", $this->getMaxHealth());
		    	if($this->namedtag["XpLevel"] == null){
			    	$this->namedtag["XpLevel"] = new IntTag("XpLevel", 0);
		    	}else{
		        	$this->namedtag["XpLevel"] = new IntTag("XpLevel", $this->expLevel);
		    	}
		    	if($this->username != "") {
			    	$this->server->saveOfflinePlayerData($this->username, $this->namedtag, true);
		    	}
			}
		}
	}

	/**
	 * Gets the username
	 *
	 * @return string
	 */
	public function getName(){
		return $this->username;
	}

	public function freeChunks(){
		$x = $z = null;
		foreach ($this->usedChunks as $index => $chunk) {
			Level::getXZ($index, $x, $z);
			$this->level->freeChunk($x, $z, $this);
			unset($this->usedChunks[$index]);
			unset($this->loadQueue[$index]);
			foreach($this->level->getChunkEntities($x, $z) as $entity){
				if($entity !== $this){
					$entity->despawnFrom($this);
				}
			}
		}
	}

	public function kill(){
		if($this->dead === true or $this->spawned === false){
			return;
		}

        $this->namedtag["XpLevel"] = new IntTag("XpLevel", 0);
		$name = $this->getName();
		$message = "Игрок $name умер";

		Entity::kill();

		$this->server->getPluginManager()->callEvent($ev = new PlayerDeathEvent($this, $this->getDrops(), $message));

		$this->freeChunks();
		if (!is_null($this->currentVehicle)) {
			$this->currentVehicle->dissMount();
		}
		if (!$ev->getKeepInventory()){
			foreach($ev->getDrops() as $item){
				$this->level->dropItem($this, $item);
			}

			if($this->inventory !== null){
				$this->inventory->clearAll();

			}
		}

		if($ev->getDeathMessage() !== "" && $ev->getDeathMessage() !== null){
			$this->server->broadcast($ev->getDeathMessage(), Server::BROADCAST_CHANNEL_USERS);
		}

		if($this->server->isHardcore()){
			$this->setBanned(true);
			return;
		}

		$pk = new RespawnPacket();
		$pos = $this->getSpawn();
		$pk->x = $pos->x;
		$pk->y = $pos->y +  $this->getEyeHeight();
		$pk->z = $pos->z;
		$this->dataPacket($pk);

	}

	public function setHealth($amount){
		parent::setHealth($amount);
		if($this->spawned){
			$pk = new UpdateAttributesPacket();
			$pk->entityId = $this->id;
			$pk->minValue = 0;
			$pk->maxValue = $this->getMaxHealth();
			$pk->value = $this->getHealth();
			$pk->defaultValue = $pk->maxValue;
			$pk->name = UpdateAttributesPacket::HEALTH;
			$this->dataPacket($pk);
		}
	}

	public function setFoodEnabled($enabled) {
		$this->hungerEnabled = $enabled;
	}

	public function getFoodEnabled() {
		return $this->hungerEnabled;
	}

	public function setFood($amount){
	    if($this->spawned){
	    	$pk = new UpdateAttributesPacket();
	    	$pk->entityId = $this->id;
	    	$pk->minValue = 0;
	    	$pk->maxValue = 20;
	    	$pk->value = $amount;
	    	$pk->defaultValue = $pk->maxValue;
	    	$pk->name = UpdateAttributesPacket::HUNGER;
	    	$this->dataPacket($pk);
	    }

		$this->foodLevel = $amount;
	}

	public function getFood() {
		return $this->foodLevel;
	}

	public function subtractFood($amount){
		if (!$this->getFoodEnabled()) {
			return false;
		}

//		if($this->getFood()-$amount <= 6 && !($this->getFood() <= 6)) {
////			$this->setDataProperty(self::DATA_FLAG_SPRINTING, self::DATA_TYPE_BYTE, false);
//			$this->removeEffect(Effect::SLOWNESS);
//		} elseif($this->getFood()-$amount < 6 && !($this->getFood() > 6)) {
////			$this->setDataProperty(self::DATA_FLAG_SPRINTING, self::DATA_TYPE_BYTE, true);
//			$effect = Effect::getEffect(Effect::SLOWNESS);
//			$effect->setDuration(0x7fffffff);
//			$effect->setAmplifier(2);
//			$effect->setVisible(false);
//			$this->addEffect($effect);
//		}
		if($this->foodLevel - $amount < 0) return;
		$this->setFood($this->getFood() - $amount);
	}

	public function isNotLiving(){
		return ($this->isCreative() || $this->isSpectator());
	}

	public function attack($damage, EntityDamageEvent $source){
		if($this->dead){
			return false;
		}
		if($this->isNotLiving()
		    && $source->getCause() !== EntityDamageEvent::CAUSE_MAGIC
		   	&& $source->getCause() !== EntityDamageEvent::CAUSE_SUICIDE
			&& $source->getCause() !== EntityDamageEvent::CAUSE_VOID
	     ){
		    $source->setCancelled(true);
		}
		if($source->getCause() === EntityDamageEvent::CAUSE_FALL){
			if($this->elytraIsActivated){
			   	$damage /= 5;
			   	$damage = round($damage, 1);
			   	$source->setDamage($damage);
		    }
	    }
	    parent::attack($damage, $source);
	    if(!$source->isCancelled() && $this->getLastDamageCause() === $source && $this->spawned){
		   	$pk = new EntityEventPacket();
		   	$pk->eid = $this->id;
		   	$pk->event = EntityEventPacket::HURT_ANIMATION;
		   	$this->dataPacket($pk);
	    }
	   	return true;
	}

	public function sendPosition(Vector3 $pos, $yaw = null, $pitch = null, $mode = MovePlayerPacket::MODE_RESET, array $targets = null) {
		$yaw = $yaw === null ? $this->yaw : $yaw;
		$pitch = $pitch === null ? $this->pitch : $pitch;

		$pk = new MovePlayerPacket();
		$pk->eid = $this->getId();
		$pk->x = $pos->x;
		$pk->y = $pos->y + $this->getEyeHeight() + 0.0001;
		$pk->z = $pos->z;
		$pk->bodyYaw = $yaw;
		$pk->pitch = $pitch;
		$pk->yaw = $yaw;
		$pk->mode = $mode;

		if($targets !== null) {
			Server::broadcastPacket($targets, $pk);
		} else {
			$this->directDataPacket($pk);
		}
		$this->newPosition = null;
	}

	protected function checkChunks() {
		$chunkX = $this->x >> 4;
		$chunkZ = $this->z >> 4;
		if ($this->chunk === null || $this->chunk->getX() !== $chunkX || $this->chunk->getZ() !== $chunkZ) {
			if ($this->chunk !== null) {
				$this->chunk->removeEntity($this);
			}
			$this->chunk = $this->level->getChunk($chunkX, $chunkZ);
			if ($this->chunk !== null) {
				$this->chunk->addEntity($this);
			}
		}

		$chunkViewers = $this->level->getUsingChunk($this->x >> 4, $this->z >> 4);
		unset($chunkViewers[$this->getId()]);

		foreach ($this->hasSpawned as $player) {
			if (!isset($chunkViewers[$player->getId()])) {
				$this->despawnFrom($player);
			} else {
				unset($chunkViewers[$player->getId()]);
			}
		}

		foreach ($chunkViewers as $player) {
			$this->spawnTo($player);
		}
	}

	public function teleport(Vector3 $pos, $yaw = null, $pitch = null) {
		if(parent::teleport($pos, $yaw, $pitch)){
			if (!is_null($this->currentWindow)) {
				$this->removeWindow($this->currentWindow);
			}
			$this->sendPosition($this, $this->yaw, $this->pitch, MovePlayerPacket::MODE_RESET);
			$this->sendPosition($this, $this->yaw, $this->pitch, MovePlayerPacket::MODE_RESET, $this->getViewers());
			$this->spawnToAll();
			$this->resetFallDistance();
			$this->nextChunkOrderRun = 0;
			$this->newPosition = null;
			$this->isTeleporting = true;
			$this->stopSleep();
		}
	}


	/**
	 * @param Inventory $inventory
	 *
	 * @return int
	 */
	public function getWindowId(Inventory $inventory) {
		if ($inventory === $this->currentWindow) {
			return $this->currentWindowId;
		} else if ($inventory === $this->inventory) {
			return 0;
		}
		return -1;
	}

	public function getCurrentWindowId() {
		return $this->currentWindowId;
	}

	public function getCurrentWindow() {
		return $this->currentWindow;
	}

	/**
	 * Returns the created/existing window id
	 *
	 * @param Inventory $inventory
	 * @param int       $forceId
	 *
	 * @return int
	 */
	public function addWindow(Inventory $inventory, $forceId = null) {
		if ($this->currentWindow === $inventory) {
			return $this->currentWindowId;
		}
		if (!is_null($this->currentWindow)) {
			echo '[INFO] Trying to open window when previous inventory still open'.PHP_EOL;
			$this->removeWindow($this->currentWindow);
		}
		$this->currentWindow = $inventory;
		$this->currentWindowId = !is_null($forceId) ? $forceId : rand(self::MIN_WINDOW_ID, 98);
		if (!$inventory->open($this)) {
			$this->removeWindow($inventory);
		}
		return $this->currentWindowId;
	}

	public function removeWindow(Inventory $inventory) {
		if ($this->currentWindow !== $inventory) {
			echo '[INFO] Trying to close not open window'.PHP_EOL;
		} else {
			$inventory->close($this);
			$this->currentWindow = null;
			$this->currentWindowId = -1;
		}
	}

	public function setMetadata($metadataKey, MetadataValue $metadataValue){
		$this->server->getPlayerMetadata()->setMetadata($this, $metadataKey, $metadataValue);
	}

	public function getMetadata($metadataKey){
		return $this->server->getPlayerMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata($metadataKey){
		return $this->server->getPlayerMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata($metadataKey, Plugin $plugin){
		$this->server->getPlayerMetadata()->removeMetadata($this, $metadataKey, $plugin);
	}

	public function setIdentifier($identifier){
		$this->identifier = $identifier;
	}

	public function getIdentifier(){
		return $this->identifier;
	}

	public function getVisibleEyeHeight() {
		return $this->eyeHeight;
	}

	public function kickOnFullServer() {
		return true;
	}

	protected function processLogin() {
		$ip = $this->getPlayer($this->iusername)->getAddress();
		$cid = $this->getPlayer($this->iusername)->getClientId();
		if(!$this->server->isWhitelisted($this->iusername)){
			$this->close($this->getLeaveMessage(), "§c Вы не в вайтлисте!");

			return;
		}elseif($this->getServer()->bans->query("SELECT * FROM bans WHERE name = '$this->iusername'")->fetchArray(SQLITE3_ASSOC)){
			$ban = $this->getServer()->bans->query("SELECT * FROM bans WHERE name = '$this->iusername'")->fetchArray(SQLITE3_ASSOC);
			$player = $ban["name"];
			$m = floor(($ban["due"] - time()) / 60); // Считаем минуты
			$h = floor($m / 60); // Считаем количество полных часов
			$m = $m - ($h * 60);  // Считаем количество оставшихся минут
			if(time() < $ban["due"]){
				$this->kick("§l§aВы были §cзабанены §aна §bсервере\n§l§aЗабанил: §d{$ban["bannedby"]}\n§l§cБан §aистекает через: §e{$h} §bчасов, §e{$m} §bминут, §aпричина: §b{$ban["reason"]}");
					return;
			}else{
				$this->getServer()->bans->query("DELETE FROM bans WHERE name = '$player'");
			}

		}elseif($this->getServer()->bansip->query("SELECT * FROM bansip WHERE ip = '$ip'")->fetchArray(SQLITE3_ASSOC)){
			$ban = $this->getServer()->bansip->query("SELECT * FROM bansip WHERE ip = '$ip'")->fetchArray(SQLITE3_ASSOC);
			$m = floor(($ban["due"] - time()) / 60); // Считаем минуты
			$h = floor($m / 60); // Считаем количество полных часов
			$m = $m - ($h * 60);  // Считаем количество оставшихся минут
			if(time() < $ban["due"]){
				$this->kick("§l§aВы были §cзабанены §aна §bсервере по §cIP\n§l§aЗабанил: §d{$ban["bannedby"]}\n§l§cБан §aистекает через: §e{$h} §bчасов, §e{$m} §bминут, §aпричина: §b{$ban["reason"]}");
					return;
			}else{
				$this->getServer()->bansip->query("DELETE FROM bansip WHERE ip = '$ip'");
			}
		}elseif($this->getServer()->banscid->query("SELECT * FROM banscid WHERE cid = '$cid'")->fetchArray(SQLITE3_ASSOC)){
			$ban = $this->getServer()->banscid->query("SELECT * FROM banscid WHERE cid = '$cid'")->fetchArray(SQLITE3_ASSOC);
			$m = floor(($ban["due"] - time()) / 60); // Считаем минуты
			$h = floor($m / 60); // Считаем количество полных часов
			$m = $m - ($h * 60);  // Считаем количество оставшихся минут
			if(time() < $ban["due"]){
				$this->kick("§l§aВы были §cзабанены §aна §bсервере по §cCID\n§l§aЗабанил: §d{$ban["bannedby"]}\n§l§cБан §aистекает через: §e{$h} §bчасов, §e{$m} §bминут, §aпричина: §b{$ban["reason"]}");
					return;
			}else{
				$this->getServer()->banscid->query("DELETE FROM banscid WHERE cid = '$cid'");
			}
		}

		foreach($this->server->getOnlinePlayers() as $p){
			if($p !== $this and ($p->iusername === $this->iusername or $this->getUniqueId()->equals($p->getUniqueId()))){
				$this->close($this->getLeaveMessage(), "Игрок с данным ником уже играет, смените ник!");
				return;
			}
		}

		if ($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)) {
			$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		}

		if ($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)) {
			$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
		}

		$nbt = $this->server->getOfflinePlayerData($this->username);

		if (!isset($nbt->NameTag)) {
			$nbt->NameTag = new StringTag("NameTag", $this->username);
		} else {
			$nbt["NameTag"] = $this->username;
		}

		$this->gamemode = $nbt["playerGameType"] & 0x03;
		if ($this->server->getForceGamemode()) {
			$this->gamemode = $this->server->getGamemode();
			$nbt->playerGameType = new IntTag("playerGameType", $this->gamemode);
		}

		$this->allowFlight = $this->isCreative();

		if (($level = $this->server->getLevelByName($nbt["Level"])) === null) {
			$this->setLevel($this->server->getDefaultLevel(), true);
			$nbt["Level"] = $this->level->getName();
			$nbt["Pos"][0] = $this->level->getSpawnLocation()->x;
			$nbt["Pos"][1] = $this->level->getSpawnLocation()->y + 5;
			$nbt["Pos"][2] = $this->level->getSpawnLocation()->z;
		} else {
			$this->setLevel($level, true);
		}

		$this->achievements = [];

		/** @var Byte $achievement */
		foreach ($nbt->Achievements as $achievement) {
			$this->achievements[$achievement->getName()] = $achievement->getValue() > 0 ? true : false;
		}

		$nbt->lastPlayed = new LongTag("lastPlayed", floor(microtime(true) * 1000));

		$this->namedtag = $nbt;

		$pk = new PlayStatusPacket();
		$pk->status = PlayStatusPacket::LOGIN_SUCCESS;
		$this->directDataPacket($pk);

		$this->loggedIn = true;

		$pk = new ResourcePacksInfoPacket();
		$manager = $this->server->getResourceManager();
		$pk->resourcePackEntries = $manager->getResourceStack();
		$pk->mustAccept = $manager->resourcePacksRequired();
		$this->directDataPacket($pk);
	}

	protected function completeLoginSequence() {
		if(!$this->isConnected()){
			return;
		}

		if (isset($this->antibot["LOGINED"])) {
		    $this->close("", "Недействительный сеанс. Причина: Не удалось проверить подпись.");
			$this->server->getNetwork()->blockAddress($this->getAddress(), 30000);
			return;
		}

        $this->antibot["LOGINED"] = true;

		parent::__construct($this->level->getChunk($this->namedtag["Pos"][0] >> 4, $this->namedtag["Pos"][2] >> 4, true), $this->namedtag); // 34% нагрузки в таймингах...

		$this->server->getPluginManager()->callEvent($ev = new PlayerLoginEvent($this, "Plugin reason."));

		if ($ev->isCancelled()) {
			$this->close(TextFormat::YELLOW . $this->username . " has left the game", $ev->getKickMessage());
			return;
		}

		if ($this->spawnPosition === null and isset($this->namedtag->SpawnLevel) and ( $level = $this->server->getLevelByName($this->namedtag["SpawnLevel"])) instanceof Level) {
			$this->spawnPosition = new Position($this->namedtag["SpawnX"], $this->namedtag["SpawnY"], $this->namedtag["SpawnZ"], $level);
		}

		if ($this->isCreative()) {
			$this->inventory->setHeldItemSlot(0);
		} else {
			$this->inventory->setHeldItemSlot($this->inventory->getHotbarSlotIndex(0));
		}

		$pk = new StartGamePacket();
		$pk->seed = -1;
		$pk->dimension = $this->level->getDimension(); // 0 - normal, 1 - nether, 2 - ender
		$pk->x = $this->x;
		$pk->y = $this->y + $this->getEyeHeight();
		$pk->z = $this->z;
		$pk->spawnX = (int) $spawnPosition->x;
		$pk->spawnY = (int) ($spawnPosition->y + $this->getEyeHeight());
		$pk->spawnZ = (int) $spawnPosition->z;
		$pk->generator = 1; //0 old, 1 infinite, 2 flat
		$pk->gamemode = $this->gamemode == 3 ? 1 : $this->gamemode;
		$pk->eid = $this->id;
		$pk->stringClientVersion = $this->clientVersion;
		$pk->multiplayerCorrelationId = $this->uuid->toString();
		$pk->originalProtocolPlayer = $this->getOriginalProtocol();
		$this->directDataPacket($pk);

        if ($this->protocol >= ProtocolInfo::PROTOCOL_331) {

            if ($this->protocol >= ProtocolInfo::PROTOCOL_418) {
                $this->directDataPacket(new ItemComponentPacket());
            }

            $this->directDataPacket(new BiomeDefinitionListPacket());
            $this->directDataPacket(new AvailableEntityIdentifiersPacket());
        }

        if($this->gamemode === 3){
            $this->sendCreativeInventory([]);
        } else {
            $this->sendCreativeInventory($this->getCreativeItems()); // 38% нагрузки в таймингах
        }

		$this->server->sendRecipeList($this); // 60% нагрузки в таймингах

        $this->sendCommandsData();

		if($this->getHealth() <= 0){
			$this->dead = true;
	   	}

		$pk = new SetTimePacket();
		$pk->time = $this->level->getTime();
		$pk->started = true;
		$this->directDataPacket($pk);

		$this->level->getWeather()->sendWeather($this);
		$this->updateSpeed($this->movementSpeed);
		$this->setXpLevel($this->namedtag["XpLevel"]);

	  	$pk = new PlayStatusPacket();
	   	$pk->status = PlayStatusPacket::PLAYER_SPAWN;
	   	$this->directDataPacket($pk);

		$this->server->getLogger()->info("Игрок {$this->getName()} вошел с айпи: {$this->getAddress()}, протокол: {$this->getOriginalProtocol()}, версия: {$this->getClientVersion()}");
		$this->server->addOnlinePlayer($this);
	}


	public function getInterface() {
		return $this->interface;
	}

	public function getCreativeItems(){
        $slots = [];
        foreach(Item::getCreativeItems() as $item){
            if($item['item']->getId() == 401 && $this->getPlayerProtocol() < ProtocolInfo::PROTOCOL_120){
                continue;
            }
            if($item['item']->getId() == 252 && $this->getPlayerProtocol() < ProtocolInfo::PROTOCOL_370){
                continue;
            }
            if($item['item']->getId() == 245 && $this->getOriginalProtocol() >= ProtocolInfo::PROTOCOL_350){
                continue;
            }
            $slots[] = clone $item['item'];
        }
        return $slots;
	}

	public function transfer($address, $port = false) {
		$pk = new TransferPacket();
		$pk->ip = $address;
		$pk->port = ($port === false ? 19132 : $port);
		$this->dataPacket($pk);
	}

	public function sendCreativeInventory($slots = []){
        if($this->protocol >= ProtocolInfo::PROTOCOL_392){
            $pk = new CreativeContentPacket();
            if(!empty($slots)){
                $pk->groups = Item::getCreativeGroups();
                $pk->items = Item::getCreativeItems();
            } else {
                $pk->groups = [];
                $pk->items = [];
            }
            $this->directDataPacket($pk);
        } else {
	        Multiversion::sendContainer($this, Protocol120::CONTAINER_ID_CREATIVE, $slots);
        }
    }

	public function sendSelfData() {
		$pk = new SetEntityDataPacket();
		$pk->eid = $this->id;
		$pk->metadata = $this->dataProperties;
		$this->dataPacket($pk);
	}
	/**
	 * Create new transaction pair for transaction or add it to suitable one
	 *
	 * @param BaseTransaction $transaction
	 * @return null
	 */
	protected function addTransaction($transaction) {
		$newItem = $transaction->getTargetItem();
		$oldItem = $transaction->getSourceItem();
		// if decreasing transaction drop down
		if ($newItem->getId() === Item::AIR || ($oldItem->deepEquals($newItem) && $oldItem->count > $newItem->count)) {

			return;
		}
		// if increasing create pair manualy

		// trying to find inventory
		$inventory = $this->currentWindow;
		if (is_null($this->currentWindow) || $this->currentWindow === $transaction->getInventory()) {
			$inventory = $this->inventory;
		}
		// get item difference
		if ($oldItem->deepEquals($newItem)) {
			$newItem->count -= $oldItem->count;
		}

		$items = $inventory->getContents();
		$targetSlot = -1;
		foreach ($items as $slot => $item) {
			if ($item->deepEquals($newItem) && $newItem->count <= $item->count) {
				$targetSlot = $slot;
				break;
			}
		}
		if ($targetSlot !== -1) {
			$trGroup = new SimpleTransactionGroup($this);
			$trGroup->addTransaction($transaction);
			// create pair for the first transaction
			if (!$oldItem->deepEquals($newItem) && $oldItem->getId() !== Item::AIR && $inventory === $transaction->getInventory()) { // for swap
				$targetItem = clone $oldItem;
			} else if ($newItem->count === $items[$targetSlot]->count) {
				$targetItem = Item::get(Item::AIR);
			} else {
				$targetItem = clone $items[$targetSlot];
				$targetItem->count -= $newItem->count;
			}
			$pairTransaction = new BaseTransaction($inventory, $targetSlot, $items[$targetSlot], $targetItem);
			$trGroup->addTransaction($pairTransaction);

			try {
				$isExecute = $trGroup->execute();
				if (!$isExecute) {
//					echo '[INFO] Transaction execute fail 1.'.PHP_EOL;
					$trGroup->sendInventories();
				}
			} catch (\Exception $ex) {
//				echo '[INFO] Transaction execute fail 2.'.PHP_EOL;
				$trGroup->sendInventories();
			}
		} else {
//			echo '[INFO] Suiteble item not found in the current inventory.'.PHP_EOL;
			$transaction->getInventory()->sendContents($this);
		}
	}

	protected function enchantTransaction(BaseTransaction $transaction) {
		if ($this->craftingType !== self::CRAFTING_ENCHANT) {
			$this->getInventory()->sendContents($this);
			return;
		}
		$oldItem = $transaction->getSourceItem();
		$newItem = $transaction->getTargetItem();
		$enchantInv = $this->currentWindow;

		if (($newItem instanceof Armor || $newItem instanceof Tool) && $transaction->getInventory() === $this->inventory) {
			// get enchanting data
			$source = $enchantInv->getItem(0);
			$enchantingLevel = $enchantInv->getEnchantingLevel();

			if ($enchantInv->isItemWasEnchant() && $newItem->deepEquals($source, true, false)) {
				// reset enchanting data
				$enchantInv->setItem(0, Item::get(Item::AIR));
				$enchantInv->setEnchantingLevel(0);

				$playerItems = $this->inventory->getContents();
				$dyeSlot = -1;
				$targetItemSlot = -1;
				foreach ($playerItems as $slot => $item) {
					if ($item->getId() === Item::DYE && $item->getDamage() === 4 && $item->getCount() >= $enchantingLevel) {
						$dyeSlot = $slot;
					} else if ($item->deepEquals($source)) {
						$targetItemSlot = $slot;
					}
				}
				if ($dyeSlot !== -1 && $targetItemSlot !== -1) {
					$this->inventory->setItem($targetItemSlot, $newItem);
					if ($playerItems[$dyeSlot]->getCount() > $enchantingLevel) {
						$playerItems[$dyeSlot]->count -= $enchantingLevel;
						$this->inventory->setItem($dyeSlot, $playerItems[$dyeSlot]);
					} else {
						$this->inventory->setItem($dyeSlot, Item::get(Item::AIR));
					}
				}
			} else if (!$enchantInv->isItemWasEnchant()) {
				$enchantInv->setItem(0, Item::get(Item::AIR));
			}
			$enchantInv->sendContents($this);
			$this->inventory->sendContents($this);
			return;
		}

		if (($oldItem instanceof Armor || $oldItem instanceof Tool) && $transaction->getInventory() === $this->inventory) {
			$enchantInv->setItem(0, $oldItem);
		}
	}

	protected function updateAttribute($name, $value, $minValue, $maxValue, $defaultValue) {
		$pk = new UpdateAttributesPacket();
		$pk->entityId = $this->id;
		$pk->name = $name;
		$pk->value = $value;
		$pk->minValue = $minValue;
		$pk->maxValue = $maxValue;
		$pk->defaultValue = $defaultValue;
		$this->dataPacket($pk);
	}

	public function updateSpeed($value) {
		$this->movementSpeed = $value;
		$this->updateAttribute(UpdateAttributesPacket::SPEED, $this->movementSpeed, 0, self::MAXIMUM_SPEED, $this->movementSpeed);
	}

	public function setSprinting($value = true, $setDefault = false) {
		if(!$setDefault) {
			if ($this->isSprinting() == $value) {
				return;
			}
			$ev = new PlayerToggleSprintEvent($this, $value);
			$this->server->getPluginManager()->callEvent($ev);
			if($ev->isCancelled()){
				$this->sendData($this);
				return;
			}
		}
		parent::setSprinting($value);
		if ($setDefault) {
			$this->movementSpeed = self::DEFAULT_SPEED;
		} else {
			$sprintSpeedChange = self::DEFAULT_SPEED * 0.3;
			if ($value === false) {
				$sprintSpeedChange *= -1;
			}
			$this->movementSpeed += $sprintSpeedChange;
		}
		$this->updateSpeed($this->movementSpeed);
	}

	public function getProtectionEnchantments() {
		$result = [
			Enchantment::TYPE_ARMOR_PROTECTION => null,
			Enchantment::TYPE_ARMOR_FIRE_PROTECTION => null,
			Enchantment::TYPE_ARMOR_EXPLOSION_PROTECTION => null,
			Enchantment::TYPE_ARMOR_FALL_PROTECTION => null,
			Enchantment::TYPE_ARMOR_PROJECTILE_PROTECTION => null
		];
		$armor = $this->getInventory()->getArmorContents();
		$armorProtection = 0;
		foreach ($armor as $item) {
			if ($item->getId() === Item::AIR) {
				continue;
			}
			$enchantments = $item->getEnchantments();
			foreach ($result as $id => $enchantment) {
				if (isset($enchantments[$id])) {
					if ($id == Enchantment::TYPE_ARMOR_PROTECTION) {
						$armorProtection += 0.05 * $enchantments[$id]->getLevel();
					} elseif ((is_null($enchantment) || $enchantments[$id]->getLevel() > $enchantment->getLevel())) {
						$result[$id] = $enchantments[$id];
					}
				}
			}
		}
		if ($armorProtection > 0) {
			$result[Enchantment::TYPE_ARMOR_PROTECTION] = $armorProtection;
		}
		return $result;
	}


	public function getExperience()
	{
		return $this->exp;
	}

	public function getExperienceLevel()
	{
		return $this->expLevel;
	}

	public function updateExperience($exp = 0, $level = 0, $checkNextLevel = true)
	{
		$this->exp = $exp;
		$this->expLevel = $level;

		if($this->hasEnoughExperience() && $checkNextLevel){
			$exp = $this->getExperience() - $this->getExperienceNeeded();
			$level = $this->getExperienceLevel() + 1;
			$this->updateExperience($exp, $level, false);
		}

		$this->updateAttribute(UpdateAttributesPacket::EXPERIENCE, $this->getExperience(), 0, self::MAX_EXPERIENCE, 0);
	    $this->updateAttribute(UpdateAttributesPacket::EXPERIENCE_LEVEL, $level, 0, self::MAX_EXPERIENCE_LEVEL, 0);
	}

	public function getXpProgress(){
		return $this->exp;
	}

	public function setXpProgress($progress){
		$this->exp = $progress;
		$this->updateAttribute(UpdateAttributesPacket::EXPERIENCE, $progress, 0, self::MAX_EXPERIENCE, 0);
	}

	public function addExperience($level = 0, $exp = 0, $checkNextLevel = true)
	{
		$this->updateExperience($this->getExperience() + $exp, $this->getExperienceLevel() + $level, $checkNextLevel);
	}

	public function removeExperience($level = 0, $exp = 0, $checkNextLevel = true)
	{
		$this->updateExperience($this->getExperience() - $exp, $this->getExperienceLevel() - $level, $checkNextLevel);
	}

	public function setXpLevel($level){
		$this->updateAttribute(UpdateAttributesPacket::EXPERIENCE_LEVEL, $level, 0, self::MAX_EXPERIENCE_LEVEL, 0);
	}
	public function getExperienceNeeded()
	{
		$level = $this->getExperienceLevel();
		if ($level <= 16) {
			return (2 * $level) + 7;
		} elseif ($level <= 31) {
			return (5 * $level) - 38;
		} elseif ($level <= 21863) {
			return (9 * $level) - 158;
		}
		return PHP_INT_MAX;
	}

	public function hasEnoughExperience() {
		return $this->getExperience() >= $this->getExperienceNeeded();
	}

	public function isUseElytra() {
		return ($this->isHaveElytra() && $this->elytraIsActivated);
	}

	public function isHaveElytra() {
		if ($this->getInventory()->getArmorItem(Elytra::SLOT_NUMBER) instanceof Elytra) {
			return true;
		}
		return false;
	}

	public function setElytraActivated($value) {
		$this->elytraIsActivated = $value;
	}

	public function isElytraActivated() {
		return $this->elytraIsActivated;
	}

	public function getPlayerProtocol() {
		return $this->protocol;
	}

	public function getDeviceOS() {
        return $this->deviceType;
    }

    public function getInventoryType() {
        return $this->inventoryType;
    }

	public function setPing($ping) {
		$this->ping = $ping;
	}

	public function getPing() {
		return $this->ping;
	}

	public function sendPing() {
		if ($this->ping <= 150) {
			$this->sendMessage("§7* §aОтличное §fсоеденение: §b{$this->ping} ms§f.");
		} elseif ($this->ping <= 250) {
			$this->sendMessage("§7* §aХорошее §fсоеденение: §b{$this->ping} ms§f.");
		} else {
			$this->sendMessage("§7* §cПлохое §fсоеденение: §b{$this->ping} ms§f.");
		}
	}

    public function getXUID() {
        return $this->xuid;
    }

	public function setTitle($text, $subtext = '', $time = 5) {
		if ($this->protocol >= Info::PROTOCOL_290) { //hack for 1.7.x
			$this->clearTitle();
			$this->titleData = ['text' => !empty($text) ? $text : ' ', 'subtext' => $subtext, 'time' => $time, 'holdTickCount' => 5];
		} else {
			$this->sendTitle($text, $subtext, $time);
		}

	}

	public function sendTitle($text, $subtext = '', $time = 5) {
		$pk = new SetTitlePacket();
		$pk->type = SetTitlePacket::TITLE_TYPE_TIMES;
		$pk->text = "";
		$pk->fadeInTime = 5;
		$pk->fadeOutTime = 5;
		$pk->stayTime = 20 * $time;
		$this->dataPacket($pk);

		if (!empty($subtext)) {
			$pk = new SetTitlePacket();
			$pk->type = SetTitlePacket::TITLE_TYPE_SUBTITLE;
			$pk->text = $subtext;
			$this->dataPacket($pk);
		}

		$pk = new SetTitlePacket();
		$pk->type = SetTitlePacket::TITLE_TYPE_TITLE;
		$pk->text = $text;
		$this->dataPacket($pk);
	}

	public function clearTitle() {
		if ($this->getPlayerProtocol() >= Info::PROTOCOL_340) {
			$this->titleData = [];
			$this->sendTitle(" ", "", 0);
		} else {
			$pk = new SetTitlePacket();
			$pk->type = SetTitlePacket::TITLE_TYPE_TIMES;
			$pk->text = "";
			$pk->fadeInTime = 0;
			$pk->fadeOutTime = 0;
			$pk->stayTime = 0;
			$this->dataPacket($pk);

			$pk = new SetTitlePacket();
			$pk->type = SetTitlePacket::TITLE_TYPE_CLEAR;
			$pk->text = "";
			$this->dataPacket($pk);
		}
	}

	public function setActionBar($text, $time = 5){
		$pk = new SetTitlePacket();
		$pk->type = SetTitlePacket::TITLE_TYPE_ACTION_BAR;
		$pk->text = $text;
		$pk->stayTime = $time;
		$pk->fadeInTime = 1;
		$pk->fadeOutTime = 1;
		$this->dataPacket($pk);
	}

	public function sendNoteSound($noteId) {
		$pk = new LevelSoundEventPacket();
		$pk->eventId = LevelSoundEventPacket::SOUND_NOTE;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		if ($this->getPlayerProtocol() >= Info::PROTOCOL_311) {
			// for 1.9.x gap between instruments 256 (1-256 - piano, 257-512 - another one, etc)
			$pk->customData = $noteId;
			$pk->entityType = MultiversionEntity::ID_NONE;
		} else {
			$pk->entityType = $noteId;
		}
		$this->directDataPacket($pk);
	}

	public function canSeeEntity(Entity $entity){
		return !isset($this->hiddenEntity[$entity->getId()]);
	}

	public function hideEntity(Entity $entity){
		if($entity instanceof Player){
			return;
		}
		$this->hiddenEntity[$entity->getId()] = $entity;
		$entity->despawnFrom($this);
	}

	public function showEntity(Entity $entity){
		if($entity instanceof Player){
			return;
		}
		unset($this->hiddenEntity[$entity->getId()]);
		if($entity !== $this && !$entity->closed && !$entity->dead){
			$entity->spawnTo($this);
		}
	}

	public function setOnFire($seconds, $damage = 1){
 		if($this->isSpectator()) {
 			return;
 		}
 		parent::setOnFire($seconds, $damage);
 	}

	public function attackInCreative($player) {

	}

	public function attackByTargetId($targetId) {
		if ($this->spawned === false || $this->dead === true) {
			return;
		}

		$target = $this->level->getEntity($targetId);
		if ($target instanceof Player && ($this->server->getConfigBoolean("pvp", true) === false || ($target->getGamemode() & 0x01) > 0 || !$this->canAttackPlayers())) {
			$target->attackInCreative($this);
			return;
		}

		if (!($target instanceof Entity) || $this->isSpectator() || $target->dead === true || !$this->canAttackMobs()) {
			return;
		}

		if ($target instanceof DroppedItem || $target instanceof Arrow) {
			return;
		}

		$item = $this->inventory->getItemInHand();
		$damageTable = [
			Item::WOODEN_SWORD => 4,
			Item::GOLD_SWORD => 4,
			Item::STONE_SWORD => 5,
			Item::IRON_SWORD => 6,
			Item::DIAMOND_SWORD => 7,
			Item::WOODEN_AXE => 3,
			Item::GOLD_AXE => 3,
			Item::STONE_AXE => 3,
			Item::IRON_AXE => 5,
			Item::DIAMOND_AXE => 6,
			Item::WOODEN_PICKAXE => 2,
			Item::GOLD_PICKAXE => 2,
			Item::STONE_PICKAXE => 3,
			Item::IRON_PICKAXE => 4,
			Item::DIAMOND_PICKAXE => 5,
			Item::WOODEN_SHOVEL => 1,
			Item::GOLD_SHOVEL => 1,
			Item::STONE_SHOVEL => 2,
			Item::IRON_SHOVEL => 3,
			Item::DIAMOND_SHOVEL => 4,
		];

		$damage = [
			EntityDamageEvent::MODIFIER_BASE => isset($damageTable[$item->getId()]) ? $damageTable[$item->getId()] : 1,
		];

		if ($this->add(0, $this->getEyeHeight())->distanceSquared($target) > 34.81) { //5.9 ** 2
			return;
		} elseif ($target instanceof Player) {
			$armorValues = [
				Item::LEATHER_CAP => 1,
				Item::LEATHER_TUNIC => 3,
				Item::LEATHER_PANTS => 2,
				Item::LEATHER_BOOTS => 1,
				Item::CHAIN_HELMET => 1,
				Item::CHAIN_CHESTPLATE => 5,
				Item::CHAIN_LEGGINGS => 4,
				Item::CHAIN_BOOTS => 1,
				Item::GOLD_HELMET => 1,
				Item::GOLD_CHESTPLATE => 5,
				Item::GOLD_LEGGINGS => 3,
				Item::GOLD_BOOTS => 1,
				Item::IRON_HELMET => 2,
				Item::IRON_CHESTPLATE => 6,
				Item::IRON_LEGGINGS => 5,
				Item::IRON_BOOTS => 2,
				Item::DIAMOND_HELMET => 3,
				Item::DIAMOND_CHESTPLATE => 8,
				Item::DIAMOND_LEGGINGS => 6,
				Item::DIAMOND_BOOTS => 3,
			];
			$points = 0;
			foreach ($target->getInventory()->getArmorContents() as $index => $i) {
				if (isset($armorValues[$i->getId()])) {
					$points += $armorValues[$i->getId()];
				}
			}

			$damage[EntityDamageEvent::MODIFIER_ARMOR] = -floor($damage[EntityDamageEvent::MODIFIER_BASE] * $points * 0.04);
		}

        if($this->fallDistance > 0 && !$this->isOnGround() && !$this->isInsideOfWater() && !$this->hasEffect(Effect::BLINDNESS)){
            $damage[EntityDamageEvent::MODIFIER_CRITICAL] = $damage[EntityDamageEvent::MODIFIER_BASE] / 2;
        }

		$ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage);
		$target->attack($ev->getFinalDamage(), $ev);

		if ($ev->isCancelled()) {
			if ($item->isTool() && $this->isSurvival()) {
				$this->inventory->sendContents($this);
			}
			return;
		}

		if($target instanceof Player){
            $damage = 0;
            foreach($target->getInventory()->getArmorContents() as $key => $item){
                if($item instanceof Armor && ($thornsLevel = $item->getEnchantment(Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_THORNS))) > 0){
                    if(mt_rand(1, 100) < $thornsLevel * 15){
                        $item->setDamage($item->getDamage() + 3);
                        $damage += ($thornsLevel > 10 ? $thornsLevel - 10 : random_int(0, 4));
                    }else{
                        $item->setDamage($item->getDamage() + 1);
                    }

                    if($item->getDamage() >= $item->getMaxDurability()) {
                        $target->getInventory()->setArmorItem($key, Item::get(Item::AIR));
                    }


                    $this->getInventory()->setArmorItem($key, $item);
                }
            }

            if($damage > 0){
                $target->attack($damage, new EntityDamageByEntityEvent($target, $this, EntityDamageEvent::CAUSE_MAGIC, $damage));
            }
        }

        if ($item->isTool() && $this->isSurvival()) {
            if ($item->useOn($target) && $item->getDamage() >= $item->getMaxDurability()) {
                $this->inventory->setItemInHand(Item::get(Item::AIR));
            } elseif ($this->inventory->getItemInHand()->getId() === $item->getId()) {
                $this->inventory->setItemInHand($item);
            }
        }
	}

	public function useItem(Item $item, $slot, $face, $blockPosition, $clickPosition, $held = false){
		if($held === false){
			$this->inventory->setHeldItemIndex($slot);
		}
		switch($face){
			case 0:
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
				$blockVector = new Vector3($blockPosition["x"], $blockPosition["y"], $blockPosition["z"]);
				$this->setDataFlag(Player::DATA_FLAGS, Player::DATA_FLAG_ACTION, false);

				$itemInHand = $this->inventory->getItemInHand();
				if($blockVector->distance($this) > 10 || ($this->isCreative() && $this->isAdventure())){

				}elseif($this->isCreative()){
					if($this->level->useItemOn($blockVector, $itemInHand, $face, $clickPosition["x"], $clickPosition["y"], $clickPosition["z"], $this) === true){
						break;
					}
				}elseif(!$itemInHand->deepEquals($item)){

				}else{
					$oldItem = clone $itemInHand;
					if($this->level->useItemOn($blockVector, $itemInHand, $face, $clickPosition["x"], $clickPosition["y"], $clickPosition["z"], $this)){
						if(!$itemInHand->deepEquals($oldItem) || $itemInHand->getCount() !== $oldItem->getCount()){
							$this->inventory->setItemInHand($itemInHand);
							$this->inventory->sendHeldItem($this->hasSpawned);
						}

						break;
					}
				}
				if($held === false){
					$this->inventory->sendHeldItem($this);
				}

				if($blockVector->distanceSquared($this) > 10000){
					break;
				}

				$target = $this->level->getBlock($blockVector);
				$block = $target->getSide($face);

				$this->level->sendBlocks([$this], [$target, $block], UpdateBlockPacket::FLAG_ALL_PRIORITY);
				break;
			case 0xff:
			case -1:
				if($this->isSpectator()){
					$this->inventory->sendHeldItem($this);
					if($this->inventory->getHeldItemSlot() !== -1){
						$this->inventory->sendContents($this);
					}

					break;
				}

				$itemInHand = $this->inventory->getItemInHand();
				if(!$itemInHand->deepEquals($item)){
					if($held === false){
						$this->inventory->sendHeldItem($this);
					}
					break;
				}

				if($blockPosition["x"] !== 0 || $blockPosition["y"] !== 0 || $blockPosition["z"] !== 0){
					$vectorLength = sqrt($blockPosition["x"] ** 2 + $blockPosition["y"] ** 2 + $blockPosition["z"] ** 2);
					$aimPos = new Vector3($blockPosition["x"] / $vectorLength, $blockPosition["y"] / $vectorLength, $blockPosition["z"] / $vectorLength);
				}else{
					$aimPos = new Vector3(0, 0, 0);
				}

				$ev = new PlayerInteractEvent($this, $itemInHand, $aimPos, $face, PlayerInteractEvent::RIGHT_CLICK_AIR);
				$this->server->getPluginManager()->callEvent($ev);
				if($ev->isCancelled()){
					if($held === false){
						$this->inventory->sendHeldItem($this);
					}
					if($this->inventory->getHeldItemSlot() !== -1){
						if($held === false){
							$this->inventory->sendContents($this);
						}
					}

					break;
				}
				if ($itemInHand instanceof Armor) {
					$this->inventory->setItem($this->inventory->getHeldItemSlot(), $this->inventory->getArmorItem($itemInHand::SLOT_NUMBER));
					$this->inventory->setArmorItem($itemInHand::SLOT_NUMBER, $itemInHand);
				} elseif (($isPotion = ($itemInHand instanceof Potion)) || isset(self::$foodData[$itemInHand->getId()])) {
					if ($isPotion && !$itemInHand->canBeConsumed() || !$isPotion && !in_array($itemInHand->getId(), [Item::GOLDEN_APPLE, Item::ENCHANTED_GOLDEN_APPLE]) && $this->getFood() >= self::FOOD_LEVEL_MAX) {
						$this->startAction = -1;
						return;
					}
					if ($this->startAction > -1) {
						$diff = ($this->server->getTick() - $this->startAction);
						if ($diff > 20 && $diff < 100) {
							if ($isPotion) {
								$ev = new PlayerItemConsumeEvent($this, $itemInHand);
								$this->server->getPluginManager()->callEvent($ev);
								if (!$ev->isCancelled()) {
									$itemInHand->onConsume($this);
								} else {
									$this->inventory->sendContents($this);
								}
							} else {
								$this->eatFoodInHand();
							}
						}
						$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
						$this->startAction = -1;
						return;
					}
				} elseif ($itemInHand->getId() === Item::SNOWBALL || $itemInHand->getId() === Item::SPLASH_POTION || $itemInHand->getId() === Item::EGG || $itemInHand->getId() === Item::BOTTLE_ENCHANTING || $itemInHand->getId() === Item::ENDER_PEARL || $itemInHand->getId() === Item::FISHING_ROD) {
					$yawRad = $this->yaw / 180 * M_PI;
					$pitchRad = $this->pitch / 180 * M_PI;
					$nbt = new Compound("", [
						"Pos" => new Enum("Pos", [
							new DoubleTag("", $this->x),
							new DoubleTag("", $this->y + $this->getEyeHeight() + 1),
							new DoubleTag("", $this->z)
						]),
						"Motion" => new Enum("Motion", [
							new DoubleTag("", -sin($yawRad) * cos($pitchRad)),
							new DoubleTag("", -sin($pitchRad)),
							new DoubleTag("", cos($yawRad) * cos($pitchRad))
						]),
						"Rotation" => new Enum("Rotation", [
							new FloatTag("", $this->yaw),
							new FloatTag("", $this->pitch)
						]),
					]);

					$f = 1.4; //Default: 1.5
                    $projectile = null;
					switch($itemInHand->getId()){
					    case Item::FISHING_ROD:
                            $this->server->getPluginManager()->callEvent($ev = new PlayerUseFishingRodEvent($this, ($this->isFishing() ? PlayerUseFishingRodEvent::ACTION_STOP_FISHING : PlayerUseFishingRodEvent::ACTION_START_FISHING)));
                            if(!$ev->isCancelled()){
                                if(!$this->isFishing()){
                                    $this->startFishing();
                                }else{
                                    $this->stopFishing();
                                }
                            }
					        break;
						case Item::SNOWBALL:
							$projectile = Entity::createEntity("Snowball", $this->chunk, $nbt, $this);
							break;
						case Item::EGG:
							$projectile = Entity::createEntity("Egg", $this->chunk, $nbt, $this);
							break;
						case Item::BOTTLE_ENCHANTING:
							$f = 1.1;
							$projectile = new BottleOEnchanting($this->chunk, $nbt, $this);
							break;
						case Item::SPLASH_POTION:
                            $projectile = Entity::createEntity("SplashPotion", $this->chunk, $nbt, $this, $itemInHand->getDamage());
                            break;
						case Item::ENDER_PEARL:
							$f = 1.1;
							if(floor(($time = microtime(true)) - $this->lastEnderPearlUse) >= 1){
								$projectile = Entity::createEntity("EnderPearl", $this->chunk, $nbt, $this);
								$this->lastEnderPearlUse = $time;
							}
							break;
					}

					if($this->isLiving()){
						if($itemInHand->getId() !== Item::FISHING_ROD) $itemInHand->setCount($itemInHand->getCount() - 1);
						$this->inventory->setItemInHand($itemInHand->getCount() > 0 ? $itemInHand : Item::get(Item::AIR));
					}

					if($projectile instanceof Projectile){
                        $projectile->setMotion($projectile->getMotion()->multiply($f));
                        $this->server->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($projectile));
						if($projectileEv->isCancelled()){
							$projectile->kill();
						}else{
							$projectile->spawnToAll();
							$this->level->addSound(new LaunchSound($this), $this->getViewers());
						}
					}
				}

				$this->setDataFlag(Player::DATA_FLAGS, Player::DATA_FLAG_ACTION, true, self::DATA_TYPE_BYTE);
				$this->startAction = $this->server->getTick();
				break;
		}
	}

	/**
	 *
	 * @param integer[] $blockPosition
	 */
	protected function breakBlock($blockPosition) {
		if($this->spawned === false or $this->dead === true){
			return;
		}

		$vector = new Vector3($blockPosition['x'], $blockPosition['y'], $blockPosition['z']);
		$item = $this->inventory->getItemInHand();

		$oldItem = clone $item;

		if($this->level->useBreakOn($vector, $item, $this) === true){
			if($this->isSurvival()){
				if(!$item->equals($oldItem, true) or $item->getCount() !== $oldItem->getCount()){
					$this->inventory->setItemInHand($item, $this);
					$this->inventory->sendHeldItem($this->hasSpawned);
				}
			}
			return;
		}

		$this->inventory->sendContents($this);
		$target = $this->level->getBlock($vector);
		$tile = $this->level->getTile($vector);

		$this->level->sendBlocks([$this], [$target], UpdateBlockPacket::FLAG_ALL_PRIORITY);

		$this->inventory->sendHeldItem($this);

		if($tile instanceof Spawnable){
			$tile->spawnTo($this);
		}
	}

	/**
	 * @minProtocolSupport 120
	 * @param InventoryTransactionPacket $packet
	 */
	private function normalTransactionLogic($packet) {
		$trGroup = new SimpleTransactionGroup($this);
		$isCraftResultTransaction = false;
		foreach ($packet->transactions as $trData) {
//			echo $trData . PHP_EOL;
			if ($trData->isDropItemTransaction()) {
				$this->tryDropItem($packet->transactions);
				return;
			}
			if ($trData->isCompleteEnchantTransaction()) {
				$this->tryEnchant($packet->transactions);
				return;
			}
			$transaction = $trData->convertToTransaction($this);
			if ($transaction == null) {
				// roolback
				$trGroup->sendInventories();
				return;
			}
			if ($trData->isCraftResultTransaction()) {
				$isCraftResultTransaction = true;
			}
//			echo " ---------- " . $transaction . PHP_EOL;
			$trGroup->addTransaction($transaction);
		}
		try {
			if (!$trGroup->execute()) {
				if ($isCraftResultTransaction) {
					$this->lastQuickCraftTransactionGroup[] = $trGroup;
//						echo '[INFO] Transaction execute holded. ' . count($packet->transactions) .PHP_EOL.PHP_EOL;
				} else {
//					echo '[INFO] Transaction execute fail. ' . count($packet->transactions) .PHP_EOL.PHP_EOL;
					$trGroup->sendInventories();
				}
			} else {
//				echo '[INFO] Transaction successfully executed.'.PHP_EOL;
			}
		} catch (\Exception $ex) {
//			echo '[INFO] Transaction execute exception. ' . $ex->getMessage() .PHP_EOL;
		}
	}

	/**
	 * @minprotocol 120
	 * @param SimpleTransactionData[] $transactionsData
	 */
	private function tryDropItem($transactionsData) {
		$dropItem = null;
		$transaction = null;
		foreach ($transactionsData as $trData) {
			if ($trData->isDropItemTransaction()) {
				$dropItem = $trData->newItem;
			} else {
				$transaction = $trData->convertToTransaction($this);
			}
		}
		if ($dropItem == null || $transaction == null) {
			$this->inventory->sendContents($this);
			if ($this->currentWindow != null) {
				$this->currentWindow->sendContents($this);
			}
			return;
		}
		//  check transaction and real data
		$inventory = $transaction->getInventory();
		if (!($inventory instanceof PlayerInventory)) {
			$inventory->sendContents($this);
			return;
		}
		$item = $inventory->getItem($transaction->getSlot());
		if ($item == null || !$item->deepEquals($dropItem) || $item->count < $dropItem->count) {
			$inventory->sendContents($this);
			return;
		}
		// generate event
		$ev = new PlayerDropItemEvent($this, $dropItem);
		$this->server->getPluginManager()->callEvent($ev);
		if($ev->isCancelled()) {
			$inventory->sendContents($this);
			return;
		}
		// finalizing drop item process
		if ($item->count == $dropItem->count) {
			$item = Item::get(Item::AIR, 0, 0);
		} else {
			$item->count -= $dropItem->count;
		}
		$inventory->setItem($transaction->getSlot(), $item);
		$motion = $this->getDirectionVector()->multiply(0.4);
		$this->level->dropItem($this->add(0, 1.3, 0), $dropItem, $motion, 40);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
	}

	/**
	 * @minprotocol 120
	 * @param Item[] $craftSlots
	 * @param Recipe $recipe
	 * @throws \Exception
	 */
	private function tryApplyCraft(&$craftSlots, $recipe) {
		if ($recipe instanceof ShapedRecipe) {
			$ingredients = [];
			$itemGrid = $recipe->getIngredientMap();
			// convert map into list
			foreach ($itemGrid as $line) {
				foreach ($line as $item) {
//					echo $item . PHP_EOL;
					$ingredients[] = $item;
				}
			}
		} else if ($recipe instanceof ShapelessRecipe) {
			$ingredients = $recipe->getIngredientList();
		}
		foreach ($ingredients as $ingKey => $ingredient) {
			if ($ingredient == null || $ingredient->getId() == Item::AIR) {
				unset($ingredients[$ingKey]);
			}
		}
		$isAllCraftSlotsEmpty = true;
		$usedItemData = [];
		foreach ($craftSlots as $itemKey => &$item) {
			if ($item == null || $item->getId() == Item::AIR) {
				continue;
			}
			foreach ($ingredients as $ingKey => $ingredient) {
				$isItemsNotEquals = $item->getId() != $ingredient->getId() ||
						($item->getDamage() != $ingredient->getDamage() && $ingredient->getDamage() != 32767) ||
						$item->count < $ingredient->count;
				if ($isItemsNotEquals) {
					throw new \Exception('Recive bad recipe');
				}
				$isAllCraftSlotsEmpty = false;
				$usedItemData[$itemKey] = $ingredient->count;
				unset($ingredients[$ingKey]);
				break;
			}
		}
		if (!empty($ingredients)) {
			throw new \Exception('Recive bad recipe');
		}
		if ($isAllCraftSlotsEmpty) {
			throw new \Exception('All craft slots are empty');
		}
		$this->server->getPluginManager()->callEvent($ev = new CraftItemEvent($ingredients, $recipe, $this));
		if ($ev->isCancelled()) {
			throw new \Exception('Event was canceled');
		}
		foreach ($usedItemData as $itemKey => $itemCount) {
			$craftSlots[$itemKey]->count -= $itemCount;
			if ($craftSlots[$itemKey]->count == 0) {
				/** @important count = 0 is important */
				$craftSlots[$itemKey] = Item::get(Item::AIR, 0, 0);
			}
		}
	}

	/**
	 * @minprotocol 120
	 * @param Item[] $craftSlots
	 * @param Recipe $recipe
	 * @throws \Exception
	 */
	private function tryApplyQuickCraft(&$craftSlots, $recipe) {
		$ingredients = [];
		if ($recipe instanceof ShapedRecipe) {
			$itemGrid = $recipe->getIngredientMap();
			foreach ($itemGrid as $line) {
				$ingredients = array_merge($ingredients, $line);
			}
		} else if ($recipe instanceof ShapelessRecipe) {
			$ingredients = $recipe->getIngredientList();
		}
		foreach ($ingredients as $ingKey => $ingredient) {
			if ($ingredient == null || $ingredient->getId() == Item::AIR) {
				unset($ingredients[$ingKey]);
			}
		}
		$isAllCraftSlotsEmpty = true;
		foreach ($ingredients as $ingKey => $ingredient) {
			foreach ($craftSlots as $itemKey => &$item) {
				if ($item == null || $item->getId() == Item::AIR) {
					continue;
				}
				$isItemsEquals = $item->getId() == $ingredient->getId() && ($item->getDamage() == $ingredient->getDamage() || $ingredient->getDamage() == 32767);
				if ($isItemsEquals) {
					$isAllCraftSlotsEmpty = false;
					$itemCount = $item->getCount();
					$ingredientCount = $ingredient->getCount();
					if ($itemCount >= $ingredientCount) {
						if ($itemCount == $ingredientCount) {
							$item = Item::get(Item::AIR, 0, 0);
						} else {
							$item->setCount($itemCount - $ingredientCount);
						}
						unset($ingredients[$ingKey]);
						break;
					} else {
						$ingredient->setCount($ingredientCount - $itemCount);
						$item = Item::get(Item::AIR, 0, 0);
					}
				}
			}
		}
		if (!empty($ingredients)) {
			throw new \Exception('Recive bad recipe');
		}
		if ($isAllCraftSlotsEmpty) {
			throw new \Exception('All craft slots are empty');
		}
		$this->server->getPluginManager()->callEvent($ev = new CraftItemEvent($ingredients, $recipe, $this));
		if ($ev->isCancelled()) {
			throw new \Exception('Event was canceled');
		}
	}

	/**
	 *
	 * @param PlayerActionPacket $packet
	 */
	protected function crackBlock($packet) {
		if (!isset($this->actionsNum['CRACK_BLOCK'])) {
			$this->actionsNum['CRACK_BLOCK'] = 0;
		}
		$block = $this->level->getBlock(new Vector3($packet->x, $packet->y, $packet->z));
		$blockPos = [
			'x' => $packet->x,
			'y' => $packet->y,
			'z' => $packet->z,
		];

		$isNeedSendPackets = $this->actionsNum['CRACK_BLOCK'] % 4 == 0;
		$this->actionsNum['CRACK_BLOCK']++;

		$breakTime = ceil($this->getBreakTime($block) * 20);
		if ($this->actionsNum['CRACK_BLOCK'] >= $breakTime) {
			$this->breakBlock($blockPos);
		}

		if ($isNeedSendPackets) {
			$recipients = $this->getViewers();
			$recipients[] = $this;

			$pk = new LevelEventPacket();
			$pk->evid = LevelEventPacket::EVENT_PARTICLE_CRACK_BLOCK;
			$pk->x = $packet->x;
			$pk->y = $packet->y + 1;
			$pk->z = $packet->z;
			$pk->data = $block->getId() | ($block->getDamage() << 8);
			Server::broadcastPacket($recipients, $pk);
			$this->sendSound(LevelSoundEventPacket::SOUND_HIT, $blockPos, MultiversionEntity::ID_NONE, $block->getId(), $recipients);
		}
	}

	public function getBreakTime(Block $block, Item $item = null) {
		$item = $item??$this->inventory->getItemInHand();
		$breakTime = $block->getBreakTime($item);
		$blockUnderPlayer = $this->level->getBlock(new Vector3(floor($this->x), floor($this->y) - 1, floor($this->z)));

		if ($blockUnderPlayer->getId() == Block::LADDER || $blockUnderPlayer->getId() == Block::VINE || !$this->onGround) {
			$breakTime *= 5;
		}
		return $breakTime;
	}

	/**
	 * @minprotocol 120
	 * @param SimpleTransactionData[] $transactionsData
	 */
	private function tryEnchant($transactionsData) {
		foreach ($transactionsData as $trData) {
			if (!$trData->isUpdateEnchantSlotTransaction() || $trData->oldItem->getId() == Item::AIR) {
				continue;
			}
			$transaction = $trData->convertToTransaction($this);
			if (!is_null($transaction)) {
				$inventory = $transaction->getInventory();

				$item = $inventory->getItem($transaction->getSlot());
				$oldItem = $transaction->getSourceItem();
				if ($oldItem->equals($item, true, false)) {
					$inventory->setItem($transaction->getSlot(), $transaction->getTargetItem());
				} else {
					$this->currentWindow->sendContents($this);
					$this->inventory->sendContents($this);
					return;
				}
			}
		}
	}

	 /**
	 *
	 * @param integer $soundId
	 * @param float[] $position
	 */
	public function sendSound($soundId, $position, $entityType = MultiversionEntity::ID_NONE, $blockId = -1, $targets = []) {
		$pk = new LevelSoundEventPacket();
		$pk->eventId = $soundId;
		$pk->x = $position['x'];
		$pk->y = $position['y'];
		$pk->z = $position['z'];
		$pk->blockId = $blockId;
		$pk->entityType = $entityType;
		if (empty($targets)) {
			$this->dataPacket($pk);
		} else {
			Server::broadcastPacket($targets, $pk);
		}
	}

	public function customInteract($packet) {

	}

	public function fall($fallDistance) {
		if (!$this->allowFlight && !$this->elytraIsActivated) {
			parent::fall($fallDistance);
		}
	}

	protected function onJump() {

 	}

	public function startFishing(){
	    $f = 0.9;
        $nbt = Entity::createBaseNBT(
            $this->add(0, $this->getEyeHeight(), 0),
            new Vector3(
                -sin(deg2rad($this->yaw)) * cos(deg2rad($this->pitch)) * $f * $f,
                -sin(deg2rad($this->pitch)) * $f * $f,
                cos(deg2rad($this->yaw)) * cos(deg2rad($this->pitch)) * $f * $f
            ),
            $this->yaw,
            $this->pitch
        );
        $fishingHook = new FishingHook($this->level->getChunk($this->x >> 4, $this->z >> 4), $nbt, $this);
        $this->linkHookToPlayer($fishingHook);

        $fishingHook->spawnToAll();
        $this->level->addSound(new LaunchSound($this), $this->getViewers());
    }

    public function stopFishing(){
	    $this->unlinkHookFromPlayer();
    }

	protected function releaseUseItem() {
		$itemInHand = $this->inventory->getItemInHand();
		if ($this->startAction > -1 && $itemInHand->getId() === Item::BOW) {
			$bow = $this->inventory->getItemInHand();
			if ($this->isSurvival() and !$this->inventory->contains(Item::get(Item::ARROW, 0, 1))) {
				$this->inventory->sendContents($this);
				return;
			}

			$yawRad = $this->yaw / 180 * M_PI;
			$pitchRad = $this->pitch / 180 * M_PI;
			$nbt = new Compound("", [
				"Pos" => new Enum("Pos", [
					new DoubleTag("", $this->x),
					new DoubleTag("", $this->y + $this->getEyeHeight()),
					new DoubleTag("", $this->z)
						]),
				"Motion" => new Enum("Motion", [
					new DoubleTag("", -sin($yawRad) * cos($pitchRad)),
					new DoubleTag("", -sin($pitchRad)),
					new DoubleTag("", cos($yawRad) * cos($pitchRad))
						]),
				"Rotation" => new Enum("Rotation", [
					new FloatTag("", $this->yaw),
					new FloatTag("", $this->pitch)
						]),
				"Fire" => new ShortTag("Fire", $this->isOnFire() ? 45 * 60 : 0)
			]);

			$diff = ($this->server->getTick() - $this->startAction);
			$p = $diff / 20;
			$f = min((($p ** 2) + $p * 2) / 3, 1) * 2;
			$ev = new EntityShootBowEvent($this, $bow, Entity::createEntity("Arrow", $this->chunk, $nbt, $this, $f >= 1), $f);

			if ($f < 0.1 or $diff < 5) {
				$ev->setCancelled();
			}

			$this->server->getPluginManager()->callEvent($ev);

			$projectile = $ev->getProjectile();
			if ($ev->isCancelled()) {
				$projectile->kill();
				$this->inventory->sendContents($this);
			} else {
				$projectile->setMotion($projectile->getMotion()->multiply($ev->getForce()));
				if ($this->isSurvival()) {
					if (is_null($bow->getEnchantment(Enchantment::TYPE_BOW_INFINITY))) {
						$this->inventory->removeItemWithCheckOffHand(Item::get(Item::ARROW, 0, 1));
					}

					$bow->setDamage($bow->getDamage() + 1);
					if ($bow->getDamage() >= 385) {
						$this->inventory->setItemInHand(Item::get(Item::AIR, 0, 0));
					} else {
						$this->inventory->setItemInHand($bow);
					}
				}
				if ($projectile instanceof Projectile) {
					$this->server->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($projectile));
					if ($projectileEv->isCancelled()) {
						$projectile->kill();
					} else {
						$projectile->spawnToAll();
						$recipients = $this->hasSpawned;
						$recipients[$this->id] = $this;
						$pk = new LevelSoundEventPacket();
						$pk->eventId = LevelSoundEventPacket::SOUND_BOW;
						$pk->x = $this->x;
						$pk->y = $this->y;
						$pk->z = $this->z;
						$pk->blockId = -1;
						$pk->entityType = 1;
						Server::broadcastPacket($recipients, $pk);
					}
				} else {
					$projectile->spawnToAll();
				}
			}
		} else if ($itemInHand->getId() === Item::BUCKET && $itemInHand->getDamage() === 1) { //Milk!
			$this->server->getPluginManager()->callEvent($ev = new PlayerItemConsumeEvent($this, $itemInHand));
			if ($ev->isCancelled()) {
				$this->inventory->sendContents($this);
				return;
			}

			$pk = new EntityEventPacket();
			$pk->eid = $this->getId();
			$pk->event = EntityEventPacket::USE_ITEM;
			$viewers = $this->getViewers();
			$viewers[] = $this;
			Server::broadcastPacket($viewers, $pk);

			if ($this->isSurvival()) {
				--$itemInHand->count;
				$this->inventory->setItemInHand($itemInHand);
				$this->inventory->addItem(Item::get(Item::BUCKET, 0, 1));
			}

			$this->removeAllEffects();
		} else {
			$this->inventory->sendContents($this);
		}
	}

	protected function useItem120() {
		$slot = $this->inventory->getItemInHand();
		if($slot instanceof Potion && $slot->canBeConsumed()){
			$ev = new PlayerItemConsumeEvent($this, $slot);
			$this->server->getPluginManager()->callEvent($ev);
			if(!$ev->isCancelled()){
				$slot->onConsume($this);
			}else{
				$this->inventory->sendContents($this);
			}
		} else {
			$this->eatFoodInHand();
		}
	}

	public function getLanguageCode() {
		return $this->languageCode;
	}

	public function getClientVersion() {
		return $this->clientVersion;
	}

	public function getOriginalProtocol() {
		return $this->originalProtocol;
	}

	protected function revertMovement(Vector3 $pos, $yaw = 0, $pitch = 0) {
		$this->sendPosition($pos, $yaw, $pitch, MovePlayerPacket::MODE_RESET);
		$this->newPosition = null;
	}

	protected function processMovement($tickDiff) {
		if (empty($this->lastMoveBuffer)) {
			return;
		}
		$pk = $this->server->getNetwork()->getPacket(0x13, $this->getPlayerProtocol());
		if (is_null($pk)) {
			$this->lastMoveBuffer = '';
			return;
		}
		$pk->setBuffer($this->lastMoveBuffer);
		$this->lastMoveBuffer = '';
		$pk->decode($this->getPlayerProtocol());
		$this->handleDataPacket($pk);
		$this->countMovePacketInLastTick = 0;
		if (!$this->isAlive() || !$this->spawned || $this->newPosition === null) {
			$this->setMoving(false);
			return;
		}

		$newPos = $this->newPosition;
		if ($this->chunk === null || !$this->chunk->isGenerated()) {
			$chunk = $this->level->getChunk($newPos->x >> 4, $newPos->z >> 4);
			if ($chunk === null || !$chunk->isGenerated() || !$this->level->isChunkLoaded($newPos->x >> 4, $newPos->z >> 4)) {
				$this->revertMovement($this, $this->lastYaw, $this->lastPitch);
				$this->nextChunkOrderRun = 0;
				return;
			}
		}
		$from = new Location($this->x, $this->y, $this->z, $this->lastYaw, $this->lastPitch, $this->level);
		$to = new Location($newPos->x, $newPos->y, $newPos->z, $this->yaw, $this->pitch, $this->level);

		$deltaAngle = abs($from->yaw - $to->yaw) + abs($from->pitch - $to->pitch);
		$distanceSquared = ($this->newPosition->x - $this->x) ** 2 + ($this->newPosition->y - $this->y) ** 2 + ($this->newPosition->z - $this->z) ** 2;
		if (($distanceSquared > 0.0625 || $deltaAngle > 10)) {
			$isFirst = ($this->lastX === null || $this->lastY === null || $this->lastZ === null);
			if (!$isFirst) {
				if (!$this->isSpectator() && $this->needCheckMovementInBlock()) {
					$toX = floor($to->x);
					$toZ = floor($to->z);
					$toY = ceil($to->y);
					$block = $from->level->getBlock(new Vector3($toX, $toY, $toZ));
					$blockUp = $from->level->getBlock(new Vector3($toX, $toY + 1, $toZ));
					if (!$block->isTransparent() || !$blockUp->isTransparent()) {
						if (!$blockUp->isTransparent()) {
							$blockLow = $from->level->getBlock(new Vector3($toX, $toY - 1, $toZ));
							if ($from->y == $to->y && !$blockLow->isTransparent()) {
								$this->revertMovement($this, $this->lastYaw, $this->lastPitch);
								return;
							}
						} else {
							$blockUpUp = $from->level->getBlock(new Vector3($toX, $toY + 2, $toZ));
							if (!$blockUpUp->isTransparent()) {
								$this->revertMovement($this, $this->lastYaw, $this->lastPitch);
								return;
							}
							$blockFrom = $from->level->getBlock(new Vector3($from->x, $from->y, $from->z));
							if ($blockFrom instanceof Liquid) {
								$this->revertMovement($this, $this->lastYaw, $this->lastPitch);
								return;
							}
						}
					}
				}
				$ev = new PlayerMoveEvent($this, $from, $to);
				$this->setMoving(true);
				$this->server->getPluginManager()->callEvent($ev);
				
				if ($ev->isCancelled()) {
					$this->revertMovement($this, $this->lastYaw, $this->lastPitch);
					return;
				}
				
		    	if ($this->nextChunkOrderRun > 20) {
			        $this->nextChunkOrderRun = 20;
		    	}
				
				if($this->server->netherEnabled){
					if($this->isInsideOfPortal()){
						if($this->portalTime == 0){
							$this->portalTime = $this->server->getTick();
						}
					}else{
						$this->portalTime = 0;
					}
				}
				if ($to->distanceSquared($ev->getTo()) > 0.01) {
					$this->teleport($ev->getTo());
					return;
				}
			}
			$dx = $to->x - $from->x;
			$dy = $to->y - $from->y;
			$dz = $to->z - $from->z;
			$this->move($dx, $dy, $dz);
			$this->x = $to->x;
			$this->y = $to->y;
			$this->z = $to->z;
			$this->lastX = $to->x;
			$this->lastY = $to->y;
			$this->lastZ = $to->z;
			$this->lastYaw = $to->yaw;
			$this->lastPitch = $to->pitch;
			$this->level->addEntityMovement($this->getViewers(), $this->getId(), $this->x, $this->y + $this->getVisibleEyeHeight(), $this->z, $this->yaw, $this->pitch, $this->yaw, true);
			if($this->getOriginalProtocol() >= ProtocolInfo::PROTOCOL_340){
	            $block = $this->getLevel()->getBlock($this->subtract(0, -1 , 0));
	           	if ($block->getId() === Block::WATER || $block->getId() === Block::STILL_WATER) {
		            $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SWIMMING, true, self::DATA_TYPE_BYTE);
	            } else {
		            $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SWIMMING, false);
	            }
			}
			if (!is_null($this->fishingHook)) {
				if ($this->distance($this->fishingHook) > 33 || $this->inventory->getItemInHand()->getId() !== Item::FISHING_ROD) {
					$this->setFishingHook();
				}
			}

			if (!$this->isSpectator()) {
				Timings::$playerCheckNearEntitiesTimer->startTiming();
				$this->checkNearEntities($tickDiff);
				Timings::$playerCheckNearEntitiesTimer->stopTiming();
			}
			if ($distanceSquared == 0) {
				$this->speed = new Vector3(0, 0, 0);
				$this->setMoving(false);
			} else {
				$this->speed = $from->subtract($to);
			}
			// Exhaustion logic
			if ($this->foodLevel > 0 && $this->getFoodEnabled() && $this->isSurvival()) {
				$distance = sqrt($dx ** 2 + $dz** 2);
				if ($distance > 0) {
					if ($this->isSprinting()) {
						$this->exhaustion += $distance * 0.1;
					} else if ($this->isCollideWithWater()) {
						$this->exhaustion += $distance * 0.01;
					}
				}
			}
		}
		$this->newPosition = null;
	}

	public function entityBaseTick($tickDiff = 1) {
		if ($this->dead === true) {
			return false;
		}

		if ($this->attackTime > 0) {
			$this->attackTime -= $tickDiff;
		}

		if ($this->noDamageTicks > 0) {
			$this->noDamageTicks -= $tickDiff;
		}

		if ($this->y < 0) {
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_VOID, 20);
			$this->attack($ev->getFinalDamage(), $ev);
		}

		foreach ($this->effects as $effect) {
			if ($effect->canTick()) {
				$effect->applyEffect($this);
			}
			$newDuration = $effect->getDuration() - $tickDiff;
			if ($newDuration <= 0) {
				$this->removeEffect($effect->getId());
			} else {
				$effect->setDuration($newDuration);
			}
		}

		$this->checkBlockCollision();

		if ($this->isInsideOfSolid()) {
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_SUFFOCATION, 1);//дамаг в стене
			$this->attack($ev->getFinalDamage(), $ev);
		}

		// stop sprinting if not solid block under feets
		$isInsideWater = $this->isInsideOfWater();
		$blockIDUnderFeets = $this->level->getBlockIdAt(floor($this->x), floor($this->y), floor($this->z));
		if ($this->protocol <= ProtocolInfo::PROTOCOL_201 && $this->isSprinting() && ((isset(Block::$liquid[$blockIDUnderFeets]) && Block::$liquid[$blockIDUnderFeets]) || $isInsideWater)) {
			$this->setSprinting(false);
		}
		$isShouldResetAir = true;
		if ($isInsideWater && !$this->hasEffect(Effect::WATER_BREATHING)) {
		    if($this->isSurvival()){
		    	$airTicks = $this->getDataProperty(self::DATA_AIR) - $tickDiff;
		    	if ($airTicks <= -20) {
			    	$airTicks = 0;
			    	$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_DROWNING, 2);
			    	$this->attack($ev->getFinalDamage(), $ev);
		    	}
		    	$this->setAirTick($airTicks);
		    	if ($this instanceof Player) {
			    	$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_NOT_IN_WATER, false, self::DATA_TYPE_LONG, false);
			    	$this->sendSelfData();
		    	}
		    }
		} else {
			if ($this->getDataProperty(self::DATA_AIR) != 300) {
				$this->setAirTick(300);
				if (($this instanceof Player)) {
					$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_NOT_IN_WATER, true, self::DATA_TYPE_LONG, false);
					$this->sendSelfData();
				}
			}
		}

		if ($this->fireTicks > 0) {
			if ($this->fireProof) {
				$this->fireTicks -= 4 * $tickDiff;
			} else {
				if (!$this->hasEffect(Effect::FIRE_RESISTANCE) && ($this->fireTicks % 20) === 0 || $tickDiff > 20) {
					$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FIRE_TICK, $this->fireDamage);
					$this->attack($ev->getFinalDamage(), $ev);
				}
				$this->fireTicks -= $tickDiff;
			}

			if ($this->fireTicks <= 0) {
				$this->extinguish();
			} else {
				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ONFIRE, true);
			}
		}
		return true;
	}

	protected function checkBlockCollision() {
		parent::checkBlockCollision();
		$blockAbove = $this->level->getBlock(new Vector3(floor($this->x), floor($this->y - 1), floor($this->z)));
		if ($blockAbove !== null && !($blockAbove instanceof Liquid) && $blockAbove->hasEntityCollision()) {
			$blockAbove->onEntityCollide($this);
		}
	}

	public function updatePlayerSkin($oldSkinName, $newSkinName) {
	    // не удалил, т.к у меня во многих плагинах используется вызов этой функции.
	}

	public function checkUUID($uuid){
		if($uuid->getVersion() !== 3){
			$this->close("", "Недействительный сеанс. Причина: Не удалось проверить подпись.");
			$this->server->getNetwork()->blockAddress($this->getAddress(), 3000);
			return false;
		}
		return true;
	}

	private function getNonValidProtocolMessage($protocol) {
		if ($protocol > ProtocolInfo::PROTOCOL_160) {
			$pk = new PlayStatusPacket();
			$pk->status = PlayStatusPacket::LOGIN_FAILED_SERVER;
			$this->dataPacket($pk);
			return TextFormat::WHITE . "§c* §fВаша версия Minecraft не поддерживается!";
		} else {
			$pk = new PlayStatusPacket();
			$pk->status = PlayStatusPacket::LOGIN_FAILED_CLIENT;
			$this->dataPacket($pk);
			return TextFormat::WHITE . "§c* §fОбновите майнкрафт.";
		}
	}

	public function sendFullPlayerList() {
	    // не удалил, т.к у меня во многих плагинах используется вызов этой функции.
	}


	public function setVehicle($vehicle) {
		$this->currentVehicle = $vehicle;
	}

	protected function getBlocksAround() {
		if ($this->blocksAround === null) {
			$this->blocksAround = [];
			$this->blocksAround[] = $this->level->getBlock(new Vector3(floor($this->x), floor($this->y), floor($this->z)));
			if (is_null($this->currentVehicle)) {
				$minX = floor($this->x - 0.3);
				$minZ = floor($this->z - 0.3);
				$maxX = floor($this->x + 0.3);
				$maxZ = floor($this->z + 0.3);
				$y = floor($this->y + 1);
				for ($z = $minZ; $z <= $maxZ; $z++) {
					for ($x = $minX; $x <= $maxX; $x++) {
						$block = $this->level->getBlock(new Vector3($x, $y, $z));
						$this->blocksAround[] = $block;
					}
				}
			}
		}
		return $this->blocksAround;
	}

	public function setInteractButtonText($text, $force = false) {
		if ($force || $this->interactButtonText != $text) {
			$this->interactButtonText = $text;
			$pk = new SetEntityDataPacket();
			$pk->eid = $this->id;
			$pk->metadata = [self::DATA_BUTTON_TEXT => [self::DATA_TYPE_STRING, $this->interactButtonText]];
			$this->dataPacket($pk);
		}
	}

	protected function onCloseSelfInventory() {

	}

	protected function onStartFly() {

	}

	protected function onStopFly() {

	}

	protected function onPlayerInput($forward, $sideway, $isJump, $isSneak) {

	}

	protected function onPlayerRequestMap($mapId) {

	}
	public function setCompassDestination($x, $y, $z, $dimension = 0) {
		$packet = new SetSpawnPositionPacket();
		$packet->spawnType = SetSpawnPositionPacket::SPAWN_TYPE_WORLD_SPAWN;
		$packet->x = $x;
		$packet->y = $y;
		$packet->z = $z;
		$packet->dimension = $dimension;

		$this->dataPacket($packet);
	}


	protected function changeHeldItem($item, $selectedSlot, $slot) {
		$hotbarItem = $this->inventory->getHotbatSlotItem($selectedSlot);
		$isNeedSendToHolder = !($hotbarItem->deepEquals($item));
		$this->inventory->setHeldItemIndex($selectedSlot, $isNeedSendToHolder);
		$this->inventory->setHeldItemSlot($slot);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
		if ($hotbarItem->getId() === Item::FISHING_ROD) {
			$this->setInteractButtonText('Ловить рыбу');
		} else {
			$this->setInteractButtonText('');
		}
	}

	protected function switchLevel(Level $targetLevel) {
		$oldLevel = $this->level;
		if(parent::switchLevel($targetLevel)){
			if($oldLevel !== null){
			    foreach($this->usedChunks as $index => $d){
				    Level::getXZ($index, $X, $Z);
				    $this->unloadChunk($X, $Z, $oldLevel);
			    }
			}

			$this->usedChunks = [];
			$this->loadQueue = [];

			$this->level->sendTime($this);
			$this->setDaylightCycle(!$this->level->stopTime);

			if($targetLevel->getDimension() != $oldLevel->getDimension()){
	    		$pk1 = new ChangeDimensionPacket();
	    		$pk1->dimension = $targetLevel->getDimension();
	    		$pk1->x = $this->x;
	    		$pk1->y = $this->y;
		    	$pk1->z = $this->z;
		    	$pk1->respawn = false;
		    	$this->dataPacket($pk1);
		    	if($this->getProtocol() < ProtocolInfo::PROTOCOL_120){
			        $pk2 = new PlayStatusPacket();
			        $pk2->status = PlayStatusPacket::PLAYER_SPAWN;
			        $this->dataPacket($pk2);
		    	}
			}
			$targetLevel->getWeather()->sendWeather($this);

			if($this->spawned){
				$this->spawnToAll();
			}
		}
	}

	public function setLastMovePacket($buffer) {
		$this->lastMoveBuffer = $buffer;
		$this->countMovePacketInLastTick++;
	}

	/**
	 * @param PEPacket[] $packets
	 */
	public function sentBatch($packets) {
		$buffer = '';
		$protocol = $this->getPlayerProtocol();
		foreach ($packets as $pk) {
			$pk->encode($protocol);
			$pkBuf = $pk->getBuffer();
			$buffer .= Binary::writeVarInt(strlen($pkBuf)) . $pkBuf;
		}
		$pk = new BatchPacket();
		$pk->payload = zlib_encode($buffer, self::getCompressAlg($this->originalProtocol), 7);
		$this->dataPacket($pk);
	}

	public function needAntihackCheck() {
		return true;
	}

	public function needCheckMovementInBlock() {
		return true;
	}

	public function setDaylightCycle($val) {
		if ($this->doDaylightCycle != $val) {
			$this->doDaylightCycle = $val;
			$pk = new GameRulesChangedPacket();
			$pk->gameRules = ["doDaylightCycle" => [1, $val]];
			$this->dataPacket($pk);
		}
	}

	public function sendAllInventories(){
		if (!is_null($this->currentWindow)) {
			$this->currentWindow->sendContents($this);
		}
		$this->getInventory()->sendContents($this);
	}

	protected function updateFallState($distanceThisTick, $onGround) {
		if ($onGround || !$this->allowFlight && !$this->elytraIsActivated) parent::updateFallState($distanceThisTick, $onGround);
	}

	public function getAdditionalSkinData() {
		return $this->additionalSkinData;
	}

	public function setAdditionalSkinData($data){
	    $this->additionalSkinData = $data;
	}

	public static function getCompressAlg($protocol) {
	    if($protocol >= ProtocolInfo::PROTOCOL_401) return ZLIB_ENCODING_RAW;
	    else return ZLIB_ENCODING_DEFLATE;
	}

	public function getOs() {
		return $this->translateVersion($this->deviceType);
	}

	public function getDeviceModel(){
		return $this->deviceModel;
	}

	public function translateVersion($fdp){
		switch($fdp){
		case 1:
			$akha = "Android"; // это обычный Android
		break;
		case 2:
			$akha = "IOS"; // Телефоны Apple
		break;
		case 3:
			$akha = "MacOS"; // Компьютеры и ноутбуки Apple
		break;
		case 4:
			$akha = "FireOS"; // Операционная система от Amazon, почти не используется, но всё же она есть
		break;
		case 5:
			$akha = "GearVR"; // Тоже мало используемая операционная система VR
		break;
		case 6:
			$akha = "Hololens"; // Чесно хз что это
		break;
		case 7:
			$akha = "Windows 10"; // Windows 10
		break;
		case 8:
			$akha = "Windows 32/Educal_version"; // Обучающее издание
		break;
		case 9:
			$akha = "NoName"; #If you have the Name of that send me a mp
		break;
		case 10:
			$akha = "Playstation 4"; // Тут понятно
		break;
		case 11:
			$akha = "NX"; #NX no name... wollah c vrai
		break;

		default:
			$akha = "Not Registered!"; // если я пропустил
		break;
		}
		return $akha;
	}

	public function getLowerCaseName(){
	    return $this->iusername;
	}

}
