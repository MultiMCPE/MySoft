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
use pocketmine\nbt\tag\Compound;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\level\format\FullChunk;

class LeashKnot extends Entity
{
    const NETWORK_ID = 88;
    
    public $width = 0.98;
    public $length = 0.98;
    public $height = 0.98;

    protected $gravity = 0.04;
    protected $drag = 0.02;

    public $canCollide = false;

    private $dropItem = true;

    public function __construct(FullChunk $level, Compound $nbt, $dropItem = true)
    {
        parent::__construct($level, $nbt);
        
        $this->dropItem = $dropItem;
    }
    
    public function canCollideWith(Entity $entity)
    {
        return false;
    }

    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->type = LeashKnot::NETWORK_ID;
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
