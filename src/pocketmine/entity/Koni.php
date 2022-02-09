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
use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\nbt\tag\Compound;
use pocketmine\Player;
use pocketmine\level\format\FullChunk;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class Koni extends Vehicle
{
    const NETWORK_ID = 23;


    public $width = 0.6;
    public $length = 0.6;
    public $height = 0.6;
    protected $riderOffset = [0, 2.3, 0];
    public $flySpeed = 0.8;
    public $switchDirectionTicks = 100;

    public function getName()
    {
        return "Koni";
    }

    public function initEntity()
    {
        $this->setMaxHealth(PHP_INT_MAX);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SADDLE, true);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_TAME_WOLF, true);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_POWER_JUMP, true);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_LEASHED, true);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, true);
        parent::initEntity();
    }

     public function __construct(FullChunk $level, Compound $nbt)
    {  
        parent::__construct($level, $nbt);
    }
	
	public function attack($damage, EntityDamageEvent $source){
		if(!$source->isCancelled() && $source instanceof EntityDamageByEntityEvent){
		    $source->setCancelled(true);
	    }	 
	}
	

    
}
