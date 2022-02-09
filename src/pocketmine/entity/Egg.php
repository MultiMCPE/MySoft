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
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\Compound;
use pocketmine\network\Network;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\entity\Projectile;
use pocketmine\Player;
//use pocketmine\level\format\FullChunk;

class Egg extends Projectile{

	const NETWORK_ID = 82;

	public $width = 0.23; //Default: 0.25
	public $length = 0.23; //This
	public $height = 0.23; //and This
	
	protected $gravity = 0.03;
	protected $drag = 0.01;

	public function __construct(FullChunk $level, Compound $nbt, Entity $shootingEntity = \null, bool $critical = \false){
		parent::__construct($level, $nbt, $shootingEntity);
	}

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}
		
		$hasUpdate = parent::onUpdate($currentTick);
		
		if($this->age > 1200 or $this->isCollided){
			$this->kill();
			$hasUpdate = true;
		}
		
		return $hasUpdate;
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = Egg::NETWORK_ID;
		$pk->eid = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
	
}
