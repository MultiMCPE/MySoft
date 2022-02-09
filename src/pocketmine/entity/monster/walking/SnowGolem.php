<?php

namespace pocketmine\entity\monster\walking;

use pocketmine\entity\monster\WalkingMonster;
use pocketmine\entity\Entity;
use pocketmine\entity\Projectile;
use pocketmine\entity\ProjectileSource;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\Item;
use pocketmine\level\sound\LaunchSound;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\Player;
use pocketmine\entity\Creature;
use pocketmine\entity\Shearable;
use pocketmine\nbt\tag\Enum;

class SnowGolem extends WalkingMonster{
	
	const NETWORK_ID = 21;
	
	const NBT_KEY_PUMPKIN = "Pumpkin";
	
	public $width = 0.6;
	public $height = 1.8;
	
	public function initEntity(){
		parent::initEntity();
		
		$this->setFriendly(true);
	}

	public function getName(){
		return "SnowGolem";
	}

	public function targetOption(Creature $creature, $distance){
		return !($creature instanceof Player) && $creature->isAlive() && $distance <= 40;
	}

	public function attackEntity(Entity $player){
		if($this->attackDelay > 23  && mt_rand(1, 32) < 4 && $this->distanceSquared($player) <= 45){
			$this->attackDelay = 0;
		
				$f = 1.2;
			$yaw = $this->yaw + mt_rand(-220, 220) / 10;
			$pitch = $this->pitch + mt_rand(-120, 120) / 10;
			$nbt = new Compound("", [
				"Pos" => new Enum("Pos", [
					new DoubleTag("", $this->x + (-sin($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * 0.5)),
					new DoubleTag("", $this->y + 1.62),
					new DoubleTag("", $this->z +(cos($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * 0.5))
				]),
				"Motion" => new Enum("Motion", [
					new DoubleTag("", -sin($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * $f),
					new DoubleTag("", -sin($pitch / 180 * M_PI) * $f),
					new DoubleTag("", cos($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * $f)
				]),
				"Rotation" => new Enum("Rotation", [
					new FloatTag("", $yaw),
					new FloatTag("", $pitch)
				]),
			]);

			/** @var Projectile $arrow */
			$arrow = Entity::createEntity("Snowball", $this->chunk, $nbt, $this);
			$snowball->setMotion($snowball->getMotion()->multiply($f));

			$this->server->getPluginManager()->callEvent($launch = new ProjectileLaunchEvent($snowball));
			if($launch->isCancelled()){
				$snowball->kill();
			}else{
				$snowball->spawnToAll();
				$this->level->addSound(new LaunchSound($this), $this->getViewers());
			}
		}
	}
	
	public function getMaxHealth(){
		return 4;
	}
	
	public function getDrops(){
		if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
			return [Item::get(Item::SNOWBALL, 0, 15)];
		}
		return [];
	}
}
