<?php

namespace pocketmine\entity\monster\flying;

use pocketmine\entity\animal\Animal;
use pocketmine\entity\BaseEntity;
use pocketmine\entity\monster\FlyingMonster;
use pocketmine\entity\projectile\FireBall;
use pocketmine\entity\Creature;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\Item;
use pocketmine\entity\ProjectileSource;
use pocketmine\level\Location;
use pocketmine\level\sound\LaunchSound;
use pocketmine\math\Math;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Vex extends FlyingMonster implements ProjectileSource{
	
	const NETWORK_ID = 105;

	public $width = 0.72;
	public $height = 1.8;
	public $gravity = 0.04;

	public function initEntity(){
		parent::initEntity();
		
		$this->setDamage([0, 0, 0, 0]);
	}

	public function getName(){
		return "Vex";
	}

	protected function checkTarget(){
		if($this->isKnockback()){
			return;
		}

		$target = $this->baseTarget;
		if(!($target instanceof Creature) or !$this->targetOption($target, $this->distanceSquared($target))){
			$near = PHP_INT_MAX;
			foreach ($this->getLevel()->getEntities() as $creature){
				if($creature === $this || !($creature instanceof Creature) || $creature instanceof Animal){
					continue;
				}

				if($creature instanceof BaseEntity && $creature->isFriendly() == $this->isFriendly()){
					continue;
				}

				if(($distance = $this->distanceSquared($creature)) > $near or !$this->targetOption($creature, $distance)){
					continue;
				}

				$near = $distance;
				$this->baseTarget = $creature;
			}
		}

		if(
			$this->baseTarget instanceof Creature
			&& $this->baseTarget->isAlive()
		){
			return;
		}

		if($this->moveTime <= 0 or !$this->baseTarget instanceof Vector3){
			$x = mt_rand(20, 100);
			$z = mt_rand(20, 100);
			$this->moveTime = mt_rand(300, 1200);
			$this->baseTarget = $this->add(mt_rand(0, 1) ? $x : -$x, 0, mt_rand(0, 1) ? $z : -$z);
		}
	}

	public function updateMove(){
		if(!$this->isMovement()){
			return null;
		}

		if($this->isKnockback()){
			$this->move($this->motionX, $this->motionY, $this->motionZ);
			$this->updateMovement();
			return null;
		}

		$before = $this->baseTarget;
		$this->checkTarget();
		if($this->baseTarget instanceof Player or $before !== $this->baseTarget){
			$x = $this->baseTarget->x - $this->x;
			$y = $this->baseTarget->y - $this->y;
			$z = $this->baseTarget->z - $this->z;

			$diff = abs($x) + abs($z);
			if($x ** 2 + $z ** 2 < 0.5){
				$this->motionX = 0;
				$this->motionZ = 0;
			}else{
				if($this->baseTarget instanceof Creature){
					$this->motionX = 0;
					$this->motionZ = 0;
					if($this->distance($this->baseTarget) > $this->y - $this->getLevel()->getHighestBlockAt((int) $this->x, (int) $this->z)){
						$this->motionY = $this->gravity;
					}else{
						$this->motionY = 0;
					}
				}else{
					$this->motionX = $this->getSpeed() * 0.15 * ($x / $diff);
					$this->motionZ = $this->getSpeed() * 0.15 * ($z / $diff);
				}
			}
			$this->yaw = rad2deg(-atan2($x / $diff, $z / $diff));
			$this->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x ** 2 + $z ** 2)));
		}

		$target = $this->baseTarget;
		$isJump = false;
		$dx = $this->motionX;
		$dy = $this->motionY;
		$dz = $this->motionZ;

		$be = new Vector2($this->x + $dx, $this->z + $dz);
		$af = new Vector2($this->x, $this->z);
		$this->move($dx, $dy, $dz);

		if($be->x != $af->x || $be->y != $af->y){
			$x = 0;
			$z = 0;
			if($be->x - $af->x != 0){
				$x = $be->x > $af->x ? 1 : -1;
			}
			if($be->y - $af->y != 0){
				$z = $be->y > $af->y ? 1 : -1;
			}

			$vec = new Vector3(Math::floorFloat($be->x) + $x, $this->y, Math::floorFloat($be->y) + $z);
			$block = $this->level->getBlock($vec->add($x, 0, $z));
			$block2 = $this->level->getBlock($vec->add($x, 1, $z));
			if(!$block->canPassThrough()){
				$bb = $block2->getBoundingBox();
				if(
					$this->motionY > -$this->gravity * 4
					&& ($block2->canPassThrough() || ($bb == null || $bb->maxY - $this->y <= 1))
				){
					$isJump = true;
					if($this->motionY >= 0.3){
						$this->motionY += $this->gravity;
					}else{
						$this->motionY = 0.3;
					}
				}
			}

			if(!$isJump){
				$this->moveTime -= 90;
			}
		}

		if($this->onGround && !$isJump){
			$this->motionY = 0;
		}else if(!$isJump){
			if($this->motionY > -$this->gravity * 4){
				$this->motionY = -$this->gravity * 4;
			}else{
				$this->motionY -= $this->gravity;
			}
		}
		$this->updateMovement();
		return $target;
	}

	public function attackEntity(Entity $player){
		if($this->attackDelay > 20 && mt_rand(1, 32) < 4 && $this->distance($player) <= 18){
			$this->attackDelay = 0;

			$ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getDamage());
			$player->attack($ev->getFinalDamage(), $ev);
		}
	}

	public function getDrops(){
		if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
			return [Item::get(Item::IRON_SWORD, 0, mt_rand(0, 2))];
		}
		return [];
	}
}
