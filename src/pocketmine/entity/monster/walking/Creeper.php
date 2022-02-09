<?php

namespace pocketmine\entity\monster\walking;

use pocketmine\entity\monster\WalkingMonster;
use pocketmine\entity\Creature;
use pocketmine\entity\Entity;
use pocketmine\entity\Explosive;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\level\Explosion;
use pocketmine\math\Math;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\IntTag;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;

class Creeper extends WalkingMonster implements Explosive{
	
	const NETWORK_ID = 33;

	public $width = 0.72;
	public $height = 1.8;

	private $bombTime = 0;

	public function getSpeed(){
		return 0.9;
	}

	public function initEntity(){
		parent::initEntity();

		if(isset($this->namedtag->BombTime)){
			$this->bombTime = (int) $this->namedtag["BombTime"];
		}
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->BombTime = new IntTag("BombTime", $this->bombTime);
	}

	public function getName(){
		return "Creeper";
	}

	public function explode(){
		$this->server->getPluginManager()->callEvent($ev = new ExplosionPrimeEvent($this, 0.1));

		if(!$ev->isCancelled()){
			$explosion = new Explosion($this, $ev->getForce(), $this);
			if($ev->isBlockBreaking()){
				$explosion->explodeB();
			}
			$explosion->explodeB();
			$this->close();
		}
	}

	public function onUpdate($currentTick){
        $tickDiff = $currentTick - $this->lastUpdate;

        if($this->baseTarget !== null){
            $x = $this->baseTarget->x - $this->x;
            $y = $this->baseTarget->y - $this->y;
            $z = $this->baseTarget->z - $this->z;

            $diff = abs($x) + abs($z);

            if($this->baseTarget instanceof Creature && $this->baseTarget->distanceSquared($this) <= 4.5){
                $this->bombTime += $tickDiff;
                if($this->bombTime >= 64){
                    $this->explodeB();
                    return false;
                }
            } else {
                $this->bombTime -= $tickDiff;
                if($this->bombTime < 0){
                    $this->bombTime = 0;
                }

                $this->motionX = $this->getSpeed() * 0.15 * ($x / $diff);
                $this->motionZ = $this->getSpeed() * 0.15 * ($z / $diff);
            }
            if($diff > 0){
                $this->yaw = rad2deg(-atan2($x / $diff, $z / $diff));
            }
            $this->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
        }

        return parent::onUpdate($currentTick);
    }

	public function updateMove(){
		return null;
	}

	public function attackEntity(Entity $player){

	}

	public function getDrops(){
		if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
			switch(mt_rand(0, 2)){
				case 0:
					return [Item::get(Item::FLINT, 0, 1)];
				case 1:
					return [Item::get(Item::GUNPOWDER, 0, 1)];
				case 2:
					return [Item::get(Item::REDSTONE_DUST, 0, 1)];
			}
		}
		
		return [];
	}

}