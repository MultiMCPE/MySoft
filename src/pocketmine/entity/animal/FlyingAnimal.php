<?php

namespace pocketmine\entity\animal;

use pocketmine\entity\Entity;
use pocketmine\entity\Effect;
use pocketmine\entity\FlyingEntity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Timings;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class FlyingAnimal extends FlyingEntity implements Animal{

    public function getSpeed(){
        return 0.7;
    }

    public function initEntity(){
        parent::initEntity();

        if($this->getDataFlag(self::DATA_FLAG_BABY, 0) === null){
            $this->setDataFlag(self::DATA_FLAG_BABY, self::DATA_TYPE_BYTE, 0);
        }
    }

    public function isBaby(){
        return $this->getDataFlag(self::DATA_FLAG_BABY, 0);
    }

    public function entityBaseTick($tickDiff = 1){
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if(!$this->hasEffect(Effect::WATER_BREATHING) && $this->isInsideOfWater()){
            $hasUpdate = true;
            $airTicks = $this->getDataProperty(self::DATA_AIR) - $tickDiff;
            if($airTicks <= -20){
                $airTicks = 0;
                $ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_DROWNING, 2);
                $this->attack($ev);
            }
            $this->setDataProperty(Entity::DATA_AIR, Entity::DATA_TYPE_SHORT, $airTicks);
        }else{
            $this->setDataProperty(Entity::DATA_AIR, Entity::DATA_TYPE_SHORT, 300);
        }
        
        return $hasUpdate;
    }

    public function onUpdate($currentTick){
        if(!$this->isAlive()){
            if(++$this->deadTicks >= 23){
                $this->close();
                return false;
            }
            return true;
        }

        $tickDiff = $currentTick - $this->lastUpdate;
        $this->lastUpdate = $currentTick;
        $this->entityBaseTick($tickDiff);

        $target = $this->updateMove($tickDiff);
        if($target instanceof Player){
            if($this->distance($target) <= 2){
                $this->pitch = 22;
                $this->x = $this->lastX;
                $this->y = $this->lastY;
                $this->z = $this->lastZ;
            }
        }elseif(
            $target instanceof Vector3
            && $this->distance($target) <= 1
        ){
            $this->moveTime = 0;
        }
        return true;
    }

}
