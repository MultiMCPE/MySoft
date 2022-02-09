<?php

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\{Entity, FireworkRocket};
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\item\Item;
use pocketmine\nbt\tag\{Compound, Enum, ByteTag, ByteArray, IntTag, DoubleTag, FloatTag};
use pocketmine\network\protocol\{Info, LevelSoundEventPacket};
use pocketmine\Player;

class Firework extends Item{
	const COLOR_BLACK = 0;
	const COLOR_RED = 1;
	const COLOR_GREEN = 2;
	const COLOR_BROWN = 3;
	const COLOR_BLUE = 4;
	const COLOR_PURPLE = 5;
	const COLOR_CYAN = 6;
	const COLOR_LIGHT_GRAY = 7;
	const COLOR_GRAY = 8;
	const COLOR_PINK = 9;
	const COLOR_LIME = 10;
	const COLOR_YELLOW = 11;
	const COLOR_LIGHT_BLUE = 12;
	const COLOR_MAGENTA = 13;
	const COLOR_ORANGE = 14;
	const COLOR_WHITE = 15;

	const TYPE_SMALL_BALL = 0;
	const TYPE_LARGE_BALL = 1;
	const TYPE_STAR_SHAPED = 2;
	const TYPE_CREEPER_SHAPED = 3;
	const TYPE_BURST = 4;

	const TAG_FIREWORKS = "Fireworks";
	const TAG_EXPLOSIONS = "Explosions";
	const TAG_FLIGHT = "Flight";

	/** @var float */
	public $spread = 5.0;
	public function __construct($meta = 0, $count = 1) {
		parent::__construct(self::FIREWORK, $meta, $count, "Fireworks");
	}
	public function getMaxStackSize(){
		return 64;
	}
	public function canBeActivated(){
		return true;
	}
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $x, $y, $z){
		$nbt = new Compound(); 
		$nbt->Pos = new Enum("Pos", [
		new DoubleTag("", $block->getX()), 
		new DoubleTag("", $block->getY() + 1), 
		new DoubleTag("", $block->getZ())]); 
		$nbt->Motion = new Enum("Motion", [
		new DoubleTag("", 0), new DoubleTag("", 0), 
		new DoubleTag("", 0)]);
		$nbt->Rotation = new Enum("Rotation", [
		new FloatTag("", 0.0), new FloatTag("", 0.0)]);
		$entity = new FireworkRocket($player->getLevel()->getChunk($target->getX() >> 4, $target->getZ() >> 4), $nbt, $this);
		$entity->spawnToAll();
		if($player->getPlayerProtocol() >= Info::PROTOCOL_120){
	        $pk = new LevelSoundEventPacket();
	        $pk->eventId = LevelSoundEventPacket::SOUND_LAUNCH;
	        $pk->x = $player->getX();
	       	$pk->y = $player->getY();
	      	$pk->z = $player->getZ();
            $player->dataPacket($pk);
		}
	    $count = $this->getCount();
		if(--$count <= 0){
		    $player->getInventory()->setItemInHand(Item::get(Item::AIR));
			return true;
		}
		$this->setCount($count);
		return true;
	}
	public function getFireworksTag(){
        $tag = new Compound(self::TAG_FIREWORKS, []);
		$tag->Explosions = new Enum(self::TAG_EXPLOSIONS, []);
		$tag->Flight = new ByteTag(self::TAG_FLIGHT, 1);
		return $this->getNamedTag()->Fireworks ?? $tag;
	}

	public function getExplosionsTag(){
		return $this->getFireworksTag()->Explosions;
	}

	public function setExplosionsTag($explosionsTag){
		$fireworksTag = $this->getFireworksTag();
		$fireworksTag->Explosions = new Enum("Explosions", $explosionsTag);
		$this->setNamedTagEntry($fireworksTag);
	}

	public function appendExplosions($explosions){
		$explosionsTag = $this->getExplosionsTag();
		foreach($explosions as $explosion){
			$explosionsTag->Color = new ByteArray("FireworkColor", chr($explosion->getColor()));
			$explosionsTag->Fade = new ByteArray("FireworkFade", chr($explosion->getFade()));
			$explosionsTag->Flicker = new ByteTag("FireworkFlicker", (int) $explosion->isFlickering());
			$explosionsTag->Trail = new ByteTag("FireworkTrail", (int) $explosion->hasTrail());
			$explosionsTag->Type = new ByteTag("FireworkType", (int) $explosion->getType());
		}
		$this->setExplosionsTag($explosionsTag);
	}

	public function getFlight(){
		return $this->getFireworksTag()->Flight;
	}

	public function setFlight($value){
		$fireworksTag = $this->getFireworksTag();
		$fireworksTag->Flight = new ByteTag(self::TAG_FLIGHT, $value);
		$this->setNamedTagEntry($fireworksTag);
	}
	public static function createNBT($fireworksData){
		$list = [];
		$compound = new Compound();
		foreach($fireworksData->getExplosions() as $explosion){
			$tag = new Compound();
			$tag->Color = new ByteArray("FireworkColor", $explosion->getColor());
			$tag->Fade = new ByteArray("FireworkFade", $explosion->getFade());
			$tag->Flicker = new ByteTag("FireworkFlicker", $explosion->isFlickering());
			$tag->Trail = new ByteTag("FireworkTrail", $explosion->hasTrail());
			$tag->Type = new ByteTag("FireworkType", $explosion->getType());
			$list[] = $tag;
		}
		$compound->Fireworks = new Compound("Fireworks", [
			new Enum("Explosions", $list),
			new ByteTag("Flight", $fireworksData->getFlight())
		]);
		return $compound;
	}
}