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

use pocketmine\level\Level;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\nbt\tag\Compound;
use pocketmine\Player;
use pocketmine\level\format\FullChunk;

class Bat extends FlyingAnimal
{
    const NETWORK_ID = 19;

    const DATA_IS_RESTING = 16;

    public $width = 0.6;
    public $length = 0.6;
    public $height = 0.6;

    public $flySpeed = 0.8;
    public $switchDirectionTicks = 100;

    public function getName()
    {
        return "Bat";
    }

    public function initEntity()
    {
        $this->setMaxHealth(6);
        parent::initEntity();
    }

     public function __construct(FullChunk $level, Compound $nbt)
    {
        if(!isset($nbt->isResting)){
            $nbt->isResting = new ByteTag("isResting", 0);
        }
        
        parent::__construct($level, $nbt);

        $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RESTING, $this->isResting());
    }

    public function isResting()
    {
        return $this->namedtag["isResting"];
    }

    public function setResting($resting)
    {
        $this->namedtag->isResting = new ByteTag("isResting", $resting ? 1 : 0);
    }

    public function onUpdate($currentTick)
    {
        if($this->age > 20 * 60 * 10){
            $this->kill();
        }
        
        return parent::onUpdate($currentTick);
    }

    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = Bat::NETWORK_ID;
        $pk->x = $this->x;
        $pk->y = $this->y;
        $pk->z = $this->z;
        $pk->speedX = $this->motionX;
        $pk->speedY = $this->motionY;
        $pk->speedZ = $this->motionZ;
        $pk->yaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }
}
