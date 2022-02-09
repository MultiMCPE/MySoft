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
use pocketmine\Player;
use pocketmine\item\Item as ItemItem;

class PolarBear extends Monster
{
    const NETWORK_ID = 28;

    public $width = 1.3;
    public $length = 0.6;
    public $height = 1.4;

    public $drag = 0.2;
    public $gravity = 0.3;

    public $dropExp = [5, 5];

    public function getName()
    {
        return "Polar Bear";
    }
    
    public function initEntity()
    {
    	$this->setMaxHealth(30);
        parent::initEntity();
    }
    
    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = PolarBear::NETWORK_ID;
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
        $cause = $this->lastDamageCause;
        $drops = [];
        if ($cause instanceof EntityDamageByEntityEvent and $cause->getDamager() instanceof Player) {
            $drops = [];
            if (mt_rand(1, 4) === 1) {
                $drops[] = ItemItem::get(ItemItem::RAW_SALMON, 0, mt_rand(0, 2));//yes.. 0,2
            } else {
                $drops[] = ItemItem::get(ItemItem::RAW_FISH, 0, mt_rand(0, 2));//yes.. 0,2
            }
        }
        return $drops;
    }
}
