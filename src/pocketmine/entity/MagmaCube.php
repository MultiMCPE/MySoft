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
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\Player;

class MagmaCube extends Slime
{
	const NETWORK_ID = 42;

	const DATA_SLIME_SIZE = 16;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;
	
	public function getName()
	{
		return "MagmaCube";
	}

	public function spawnTo(Player $player)
	{
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = MagmaCube::NETWORK_ID;
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

	public function getDrops()
	{
		$drops = [];
		if(DATA_SLIME_SIZE > 1){
			$ev = $this->getLastDamageCause();
			$looting = $ev instanceof EntityDamageByEntityEvent ? $ev->getDamager() instanceof Player ? $ev->getDamager()->getInventory()->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING) : 0 : 0;

			$creams = rand(0, 3) - 2;

			if ($looting > 0){
				$creams += rand(0, $looting);
			}

			$drops[] = ItemItem::get(ItemItem::MAGMA_CREAM, 0, $creams);
		}
		
		return $drops;
	}
}
