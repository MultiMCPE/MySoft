<?php

namespace pocketmine\entity\monster\jumping;

use pocketmine\entity\monster\JumpingMonster;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

class MagmaCube extends JumpingMonster{
	
    const NETWORK_ID = 42;

    public $width = 1.2;
    public $height = 1.2;
    public $length = 1.2;
    
    public function getName(){
        return "MagmaCube";
    }
    
    public function initEntity(){
        parent::initEntity();
        
        $this->setMaxHealth(4);
        $this->fireProof = true;
        $this->setDamage([0, 3, 4, 6]);
    }
    
    public function getSpeed(){
        return 0.8;
    }
    
    public function attackEntity(Entity $player){
        if($this->attackDelay > 10 && $this->distanceSquared($player) < 1){
            $this->attackDelay = 0;
            
            $ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getDamage());
            $player->attack($ev->getFinalDamage(), $ev);
        }
    }

    public function getDrops(){
        $drops = [];
        if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
            switch (mt_rand(0, 1)) {
                case 0:
                    $drops[] = Item::get(Item::NETHERRACK, 0, 1);
                    break;
                case 1:
                    $drops[] = Item::get(Item::MAGMA_CREAM, 0, 1);
                    break;
            }
        }
        return $drops;
    }

    public function getKillExperience(){
        return mt_rand(3, 6);
    }
    
}
