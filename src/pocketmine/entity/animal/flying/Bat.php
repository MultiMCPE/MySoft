<?php

namespace pocketmine\entity\animal\flying;

use pocketmine\entity\animal\FlyingAnimal;
use pocketmine\entity\Creature;

class Bat extends FlyingAnimal{
	
    const NETWORK_ID = 19;

    public $width = 0.469;
    public $length = 0.484;
    public $height = 0.5;
    
    public function getSpeed(){
        return 1.0;
    }

    public function getName(){
        return "Bat";
    }

    public function targetOption(Creature $creature, $distance){
        return false;
    }

    public function getDrops(){
        return [];
    }

    public function getMaxHealth(){
        return 6;
    }

}
