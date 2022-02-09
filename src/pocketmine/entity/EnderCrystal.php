<?php

/*
 _      _          _____    _____    ___  _____
| \    / |  \  /  |        |     |  |       |
|  \  /  |   \/   |_____   |     | _|__     |
|   \/   |   /          |  |     |  |       |
|        |  /     ______|  |_____|  |       |
*/

namespace pocketmine\entity;

use pocketmine\level\format\FullChunk;;
use pocketmine\nbt\tag\Compound;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\{Player, Server};
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\{Position, Explosion};

class EnderCrystal extends Entity{
    const NETWORK_ID = 71;

    public $height = 0.7;
    public $width = 1.6;

    public $gravity = 0.5;
    public $drag = 0.1;

    public function __construct(FullChunk $chunk, Compound $nbt){
        parent::__construct($chunk, $nbt);
    }

	public function attack($damage, EntityDamageEvent $source){
	    if(Server::getInstance()->getAdvancedProperty("main.ender-crystals", false) == true){
	    	parent::attack($damage, $source);
		
	    	if($source->isCancelled()) return false;
	    	$this->close();
	    	
            $explode = new Explosion(new Position($this->getX(), $this->getY(), $this->getZ(), $this->getLevel()), 7);
	        if(Server::getInstance()->getAdvancedProperty("main.explode-blocks", false) == true){
	            $explode->explodeA();
	        }
            $explode->explodeB();
	        return true;
	    }
		return false;
	}

    public function spawnTo(Player $player){
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = EnderCrystal::NETWORK_ID;
        $pk->x = $this->x;
        $pk->y = $this->y;
        $pk->z = $this->z;
        $pk->speedX = 0;
        $pk->speedY = 0;
        $pk->speedZ = 0;
        $pk->yaw = 0;
        $pk->pitch = 0;
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }
}
