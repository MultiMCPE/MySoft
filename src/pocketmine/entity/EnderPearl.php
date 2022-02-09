<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\nbt\tag\Compound;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\level\format\FullChunk;
use pocketmine\math\Vector3;
class EnderPearl extends Projectile
{
    const NETWORK_ID = 87;

    public $width = 0.25;
    public $length = 0.25;
    public $height = 0.25;

    protected $gravity = 0.03;
    protected $drag = 0.01;
    protected $player;

    //private $hasTeleportedShooter = false;

   // public function __construct(FullChunk $level, Compound $nbt, Entity $shootingEntity = null, $critical = false){
    //    parent::__construct($level, $nbt, $shootingEntity);
   // }
	
	
		public function __construct(FullChunk $chunk, Compound $nbt, Player $owner = null) {
		parent::__construct($chunk, $nbt);
		$this->owner = $owner;
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_HAS_COLLISION, true, self::DATA_TYPE_LONG, false);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_AFFECTED_BY_GRAVITY, true, self::DATA_TYPE_LONG, false);
		if (!is_null($this->owner)) {
			$this->setDataProperty(self::DATA_OWNER_EID, self::DATA_TYPE_UNSIGNED_LONG, $this->owner->getId(), false);
		}
	}

    public function teleporter(){
		if($this->owner instanceof Player){
         $yaw = $this->owner->getYaw();
         $pitch = $this->owner->getPitch();
         $this->owner->setPositionAndRotation($this->getPosition(), $yaw, $pitch);
		}
		$this->kill();
	}

              
   
    

    public function onUpdate($currentTick)
    {
        if ($this->closed) {
            return false;
        }

        $this->timings->startTiming();

        $hasUpdate = parent::onUpdate($currentTick);

        if ($this->age > 1200 or $this->isCollided) {
            $this->teleporter();
            $hasUpdate = true;
        }

        $this->timings->stopTiming();

        return $hasUpdate;
    }
    
    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->type = EnderPearl::NETWORK_ID;
        $pk->eid = $this->getId();
        $pk->x = $this->x;
        $pk->y = $this->y;
        $pk->z = $this->z;
        $pk->speedX = $this->motionX;
        $pk->speedY = $this->motionY;
        $pk->speedZ = $this->motionZ;
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }
}
