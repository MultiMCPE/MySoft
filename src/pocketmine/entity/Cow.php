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

use pocketmine\item\enchantment\Enchantment;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;

class Cow extends Animal
{
    const NETWORK_ID = 11;

    public $width = 0.3;
    public $length = 0.9;
    public $height = 1.8;

    public $dropExp = [1, 3];

    public function getName()
    {
        return "Cow";
    }

    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = Cow::NETWORK_ID;
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
        $lootingL = 0;
        $cause = $this->lastDamageCause;
        if ($cause instanceof EntityDamageByEntityEvent and $cause->getDamager() instanceof Player) {
            $lootingL = $cause->getDamager()->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
        }
        $drops = array(ItemItem::get(ItemItem::RAW_BEEF, 0, mt_rand(1, 3 + $lootingL)));
        $drops[] = ItemItem::get(ItemItem::LEATHER, 0, mt_rand(0, 2 + $lootingL));
        return $drops;
    }
}
