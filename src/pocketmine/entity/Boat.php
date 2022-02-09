<?php

namespace pocketmine\entity;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\Compound;
use pocketmine\math\Vector3;

class Boat extends Vehicle {

	const NETWORK_ID = 90;

	public $height = 0.7;
	public $width = 1.6;
	protected $riderOffset = [0, 0.6, 0];
	protected $afterMovement = false;
	protected $interactText = "сесть";
	
	public function __construct(FullChunk $chunk, Compound $nbt) {
		parent::__construct($chunk, $nbt);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_HAS_COLLISION, true, self::DATA_TYPE_LONG, false);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_AFFECTED_BY_GRAVITY, true, self::DATA_TYPE_LONG, false);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SADDLE, true);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_TAME_WOLF, true);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_POWER_JUMP, true);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_LEASHED, true);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, true);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_CONTROLLING_RIDER_SEAT_NUMBER, 0);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_MARK_VARIANT, true);
	}
	
	public function initEntity() {
		$this->setMaxHealth(10);
		$this->setHealth($this->getMaxHealth());
		parent::initEntity();
	}

	public function getName() {
		return "Boat";
	}

/*	public function onUpdate($currentTick) {
		if ($this->closed !== false) {
			return false;
		}

		if ($this->dead === true) {
			$this->removeAllEffects();
			$this->despawnFromAll();
			$this->close();
			return false;
		}
		$tickDiff = $currentTick - $this->lastUpdate;
		if ($tickDiff < 1) {
			return true;
		}

		$this->lastUpdate = $currentTick;

		$hasUpdate = false;
		if ($this->afterMovement) {
			$this->afterMovement = false;
			$this->updateMovement();
		}
		return $hasUpdate;
	}*/
    public function onUpdate($currentTick)
    {
        if ($this->closed) {
            return false;
        }
        $tickDiff = $currentTick - $this->lastUpdate;
        if ($tickDiff <= 0 and !$this->justCreated) {
            return true;
        }
        $positionToCheck = new Vector3(floor($this->x), floor($this->y), floor($this->z));
        $minecartPosition = $positionToCheck->floor()->add(0.5, 1, 0.5);
        $this->setPosition($minecartPosition);
        $this->lastUpdate = $currentTick;

        $hasUpdate = $this->entityBaseTick($tickDiff);

        if (!$this->level->getBlock(new Vector3($this->x, $this->y, $this->z))->getBoundingBox() == null or $this->isInsideOfWater()) {
            $this->motionY = 0.1;
        } else {
            $this->motionY = -0.08;
        }

        $this->move($this->motionX, $this->motionY, $this->motionZ);
        $this->updateMovement();

        if ($this->linkedEntity == null or $this->linkedType = 0) {
            if ($this->age > 1500) {
                $this->close();
                $hasUpdate = true;
                $this->age = 0;
            }
            $this->age++;
        } else $this->age = 0;

        return $hasUpdate or !$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001;
    }
	public function updateByOwner($x, $y, $z, $yaw, $pitch) {
		$this->setPositionAndRotation(new Vector3($x, $y, $z), $yaw, $pitch);
		$this->afterMovement = true;
		$this->scheduleUpdate();
	}
	
	public function EntityDamageByEntityEvent(EntityDamageEvent $event){
        if($event instanceof EntityDamageByEntityEvent){
            $p = $event->getDamager();
            $ent = $event->getEntity();
            if($p instanceof Player){
                if($ent instanceof Vehicle){
					$ent->kill();
				}
			}
		}
	}
}
