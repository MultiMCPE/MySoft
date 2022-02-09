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

use pocketmine\block\Liquid;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\item\Item as ItemItem;
use pocketmine\Player;

class Lightning extends Creature
{
    const NETWORK_ID = 93;

    public $width = 0.3;
    public $length = 0.9;
    public $height = 1.8;

    public function getName()
    {
        return "Lightning";
    }

    public function initEntity()
    {
        parent::initEntity();
        
        $this->setMaxHealth(2);
        $this->setHealth($this->getMaxHealth());
    }

    public function onUpdate($tick)
    {
        parent::onUpdate($tick);
        if($this->age > 20){
            $this->kill();
            $this->close();
        }
        return true;
    }

    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = Lightning::NETWORK_ID;
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

    public function spawnToAll()
    {
        parent::spawnToAll();

        if ($this->getLevel()->getServer()->lightningFire) {
            $fire = ItemItem::get(ItemItem::FIRE)->getBlock();
            $oldBlock = $this->getLevel()->getBlock($this);
            if ($oldBlock instanceof Liquid) {

            } elseif ($oldBlock->isSolid()) {
                $v3 = new Vector3($this->x, $this->y + 1, $this->z);
            } else {
                $v3 = new Vector3($this->x, $this->y, $this->z);
            }
            if (isset($v3)) $this->getLevel()->setBlock($v3, $fire);

            foreach ($this->level->getNearbyEntities($this->boundingBox->grow(4, 3, 4), $this) as $entity) {
                if ($entity instanceof Player) {
                    $damage = mt_rand(8, 20);
                    $ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageByEntityEvent::CAUSE_LIGHTNING, $damage);
                    if ($entity->attack($ev->getFinalDamage(), $ev) === true) {
                        $ev->useArmors();
                    }
                    $entity->setOnFire(mt_rand(3, 8));
                }

                if ($entity instanceof Creeper) {
                    $entity->setPowered(true, $this);
                }
            }
        }
    }
}
