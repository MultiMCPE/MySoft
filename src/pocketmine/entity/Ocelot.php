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

class Ocelot extends Animal
{
    const NETWORK_ID = 22;

    const DATA_CAT_TYPE = 18;

    const TYPE_WILD = 0;
    const TYPE_TUXEDO = 1;
    const TYPE_TABBY = 2;
    const TYPE_SIAMESE = 3;

    public $width = 0.312;
    public $length = 2.188;
    public $height = 0.75;

    public $dropExp = [1, 3];

    public function getName()
    {
        return "Ocelot";
    }

    public function __construct(FullChunk $level, Compound $nbt)
    {
        if(!isset($nbt->CatType)){
            $nbt->CatType = new ByteTag("CatType", mt_rand(0, 3));
        }
        
        parent::__construct($level, $nbt);

        $this->setDataProperty(Ocelot::DATA_CAT_TYPE, Ocelot::DATA_TYPE_BYTE, $this->getCatType());
    }

    public function setCatType($type)
    {
        $this->namedtag->CatType = new ByteTag("CatType", $type);
    }
    
    public function getCatType()
    {
        return $this->namedtag["CatType"];
    }

    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = Ocelot::NETWORK_ID;
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
}
