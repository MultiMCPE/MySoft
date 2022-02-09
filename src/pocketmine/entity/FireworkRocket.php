<?php

namespace pocketmine\entity;

use pocketmine\{Player, entity\Entity, Server, nbt\tag\Compound, level\format\FullChunk, scheduler\CallbackTask, item\Firework};
use pocketmine\network\protocol\{EntityEventPacket, AddEntityPacket, LevelSoundEventPacket, Info};

class FireworkRocket extends Entity {

	const NETWORK_ID = 72;
	
	public $width = 0.25;
	public $height = 0.25;

	public function __construct(FullChunk $chunk, Compound $nbt, $item = null){
		parent::__construct($chunk, $nbt);
        if($item instanceof Firework){
            //$this->setItem(16, $item);
        }
	}
	
	public function explode(){
	    if($this->closed) return;
	    foreach($this->getServer()->getOnlinePlayers() as $pl){
			if($pl->getPlayerProtocol() >= Info::PROTOCOL_120){
			    
			    $pk = new EntityEventPacket();
			    $pk->eid = $this->getId();
			    $pk->event = EntityEventPacket::FIREWORK_PARTICLES;
			    $pl->dataPacket($pk);
			    
	            $pk = new LevelSoundEventPacket();
	            $pk->eventId = LevelSoundEventPacket::SOUND_BLAST;
	            $pk->x = $pl->getX();
	        	$pk->y = $pl->getY();
	        	$pk->z = $pl->getZ();
                $pl->dataPacket($pk);
			}
		}
		$this->close();
	}
	
	public function getServer(){
	    return Server::getInstance();
	}
	
    public function spawnTo(Player $player){
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = 72;
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
        
        $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "explode")), 20 * 3);
    }
}