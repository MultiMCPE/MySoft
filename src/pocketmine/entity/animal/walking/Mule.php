<?php

namespace pocketmine\entity\animal\walking;

use pocketmine\entity\animal\WalkingAnimal;
use pocketmine\entity\Rideable;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\entity\Creature;

class Mule extends WalkingAnimal implements Rideable{
	
    const NETWORK_ID = 25;

    public $width = 0.75;
    public $height = 1.562;
    public $length = 1.2;
    
    public function getName(){
        return "Mule";
    }
    
    public function initEntity(){
		parent::initEntity();

		$this->setMaxHealth(15);
	}
	
    public function getSpeed(){
        return 1.1;
    }

    public function targetOption(Creature $creature, $distance){
        if($creature instanceof Player){
            return $creature->spawned && $creature->isAlive() && !$creature->closed && $creature->getInventory()->getItemInHand()->getId() == Item::WHEAT && $distance <= 49;
        }
        return false;
    }

    public function getDrops(){
		$drops = [];
		if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
			  $drops[] = Item::get(Item::LEATHER, 0, mt_rand(0, 2));
		}
		return $drops;
	}
    
    public function getKillExperience(){
        return mt_rand(1, 3);
    }
    
    public function getRidePosition(){
        return null;
    }
    
}
