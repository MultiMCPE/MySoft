<?php

namespace pocketmine\entity\animal\walking;

use pocketmine\entity\animal\WalkingAnimal;
use pocketmine\entity\Rideable;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;
use pocketmine\entity\Creature;

class Donkey extends WalkingAnimal implements Rideable{
	
    const NETWORK_ID = 24;

    public $width = 0.75;
    public $height = 1.562;
    public $length = 1.2;

    public function getName(){
        return "Donkey";
    }
    
    public function initEntity(){
		parent::initEntity();

		$this->setMaxHealth(20);
	}
	
    public function getSpeed(){
        return 1.0;
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
