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
use pocketmine\network\protocol\MobArmorEquipmentPacket;
use pocketmine\item\Item as ItemItem;
use pocketmine\Player;

class Horse extends Living
{
    const NETWORK_ID = 23;

    public function getName()
    {
        return "Horse";
    }

    public function setChestPlate($id)
    {
        $pk = new MobArmorEquipmentPacket();
        $pk->eid = $this->getId();
        $pk->slots = [
            ItemItem::get(0, 0),
            ItemItem::get($id, 0),
            ItemItem::get(0, 0),
            ItemItem::get(0, 0)
        ];
        
        foreach($this->level->getPlayers() as $player){
            $player->dataPacket($pk);
        }
    }

    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = Horse::NETWORK_ID;
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
