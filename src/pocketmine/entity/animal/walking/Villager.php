<?php

namespace pocketmine\entity\animal\walking;

use pocketmine\entity\animal\WalkingAnimal;
use pocketmine\nbt\tag\IntTag;

class Villager extends WalkingAnimal{
	
    const NETWORK_ID = 15;
    
    const PROFESSION_FARMER = 0;
	const PROFESSION_LIBRARIAN = 1;
	const PROFESSION_PRIEST = 2;
	const PROFESSION_BLACKSMITH = 3;
	const PROFESSION_BUTCHER = 4;
	const PROFESSION_GENERIC = 5;
	
    public $width = 0.938;
    public $length = 0.609;
    public $height = 2;
    
    public function getName(){
        return "Villager";
    }
    
    public function initEntity(){
		parent::initEntity();

		$this->setMaxHealth(10);
		
		if(!isset($this->namedtag->Profession)){
			$this->setProfession(self::PROFESSION_GENERIC);
		}
	}
	
	public function setProfession($profession){
		$this->namedtag->Profession = new IntTag("Profession", $profession);
	}

	public function getProfession(){
		return $this->namedtag["Profession"];
	}

	public function isBaby(){
		return $this->getDataFlag(self::DATA_AGEABLE_FLAGS, self::DATA_FLAG_BABY);
	}
	
    public function getSpeed(){
        return 1.1;
    }
    
    public function getDrops(){
        return [];
    }
    
    public function getKillExperience(){
        return mt_rand(3, 6);
    }

}
