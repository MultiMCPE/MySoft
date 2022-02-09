<?php

namespace pocketmine\entity\monster\walking;

use pocketmine\entity\monster\WalkingMonster;
use pocketmine\entity\Effect;
use pocketmine\entity\Ageable;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;

class Husk extends WalkingMonster implements Ageable{
	
    const NETWORK_ID = 47;

    public $width = 1.031;
    public $length = 0.891;
    public $height = 2;
    
    public function getName(){
        return "Husk";
    }
    
    public function initEntity(){
        parent::initEntity();

        if($this->getDataFlag(self::DATA_FLAG_BABY, 0) === null){
            $this->setDataFlag(self::DATA_FLAG_BABY, self::DATA_TYPE_BYTE, 0);
        }
        $this->setMaxHealth(20);
        $this->setDamage([0, 3, 4, 6]);
    }
    
    public function getSpeed(){
        return 1.1;
    }
    
    public function isBaby(){
        return $this->getDataFlag(self::DATA_FLAG_BABY, 0);
    }

    public function setHealth($amount){
        parent::setHealth($amount);

        if ($this->isAlive()) {
            if (15 < $this->getHealth()) {
                $this->setDamage([0, 2, 3, 4]);
            } else if (10 < $this->getHealth()) {
                $this->setDamage([0, 3, 4, 6]);
            } else if (5 < $this->getHealth()) {
                $this->setDamage([0, 3, 5, 7]);
            } else {
                $this->setDamage([0, 4, 6, 9]);
            }
        }
    }

    /**
     * @param Entity $player
     */
    public function attackEntity(Entity $player){
        if ($this->attackDelay > 10 && $this->distanceSquared($player) < 2) {
            $this->attackDelay = 0;

            $ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getDamage());
			$player->attack($ev->getFinalDamage(), $ev);
            $effect = Effect::getEffect(17)->setDuration(1800)->setAmplifier(1);
            $player->addEffect($effect);
        }
    }

    public function getDrops(){
        $drops = [];
        if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
            array_push($drops, Item::get(Item::ROTTEN_FLESH, 0, mt_rand(0, 2)));
            switch (mt_rand(0, 5)) {
                case 1:
                    array_push($drops, Item::get(Item::CARROT, 0, 1));
                    break;
                case 2:
                    array_push($drops, Item::get(Item::POTATO, 0, 1));
                    break;
                case 3:
                    array_push($drops, Item::get(Item::IRON_INGOT, 0, 1));
                    break;
            }
        }
        return $drops;
    }
    
    public function getKillExperience(){
        return 5;
    }

}
