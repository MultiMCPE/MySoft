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

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\network\protocol\MobEquipmentPacket;
use pocketmine\item\IronSword;

class Vex extends Monster
{
	const NETWORK_ID = 105;
	
	public $width = 0.6;
	public $length = 0.6;
	public $height = 0;

	public $dropExp = [5, 5];
	
	public function getName()
	{
		return "Vex";
	}
	
	public function initEntity()
	{
		$this->setMaxHealth(14);
		parent::initEntity();
	}
	
	public function spawnTo(Player $player)
	{
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = 105;
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
		
		$pk = new MobEquipmentPacket();
        $pk->eid = $this->getId();
        $pk->item = new IronSword();
        $pk->slot = 0;
        $pk->selectedSlot = 0;
        
		parent::spawnTo($player);
	}
}
