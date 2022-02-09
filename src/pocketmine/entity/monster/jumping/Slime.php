<?php

namespace pocketmine\entity\monster\jumping;

use pocketmine\entity\monster\JumpingMonster;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\Creature;

class Slime extends JumpingMonster{
	
    const NETWORK_ID = 37;

    public $width = 1.2;
    public $height = 1.2;
    public $length = 1.2;
    
    public function getName(){
        return "Slime";
    }
    
    public function initEntity(){
        parent::initEntity();
        
        $this->setMaxHealth(4);
        $this->setDamage([0, 2, 2, 3]);
    }
    
    public function getSpeed(){
        return 0.8;
    }
    
    public function attackEntity(Entity $player){
        if($this->attackDelay > 10 && $this->distanceSquared($player) < 2){
            $this->attackDelay = 0;

            $ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getDamage());
            $player->attack($ev->getFinalDamage(), $ev);
        }
    }

    public function targetOption(Creature $creature, $distance){
        if ($creature instanceof Player) {
            return $creature->isAlive() && $distance <= 25;
        }
        return false;
    }

    public function getDrops(){
		$drops = [];
		if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
            $drops[] = Item::get(Item::SLIMEBALL, 0, mt_rand(0, 2));
		}
		return $drops;
	}
	
    public function getKillExperience(){
        return mt_rand(1, 4);
    }

}
