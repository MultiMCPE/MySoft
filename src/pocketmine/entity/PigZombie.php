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
use pocketmine\network\protocol\MobEquipmentPacket;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\item\Item as ItemItem;
use pocketmine\item\enchantment\Enchantment;

class PigZombie extends Monster
{
    const NETWORK_ID = 36;

    public $width = 0.6;
    public $length = 0.6;
    public $height = 1.8;

    public $drag = 0.2;
    public $gravity = 0.3;

    public $dropExp = [5, 5];

    public function getName()
    {
        return "PigZombie";
    }

    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = PigZombie::NETWORK_ID;
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

        $pk = new MobEquipmentPacket();
        $pk->eid = $this->getId();
        $pk->item = new ItemItem(283);
        $pk->slot = 0;
        $pk->selectedSlot = 0;

        $player->dataPacket($pk);
    }

    public function getDrops()
    {
        $cause = $this->lastDamageCause;
        $drops = [];
        if ($cause instanceof EntityDamageByEntityEvent and $cause->getDamager() instanceof Player) {
            $lootingL = $cause->getDamager()->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
            if (mt_rand(1, 200) <= (5 + 2 * $lootingL)) {
                $drops[] = ItemItem::get(ItemItem::GOLD_INGOT, 0, 1);
            }
            $drops[] = ItemItem::get(ItemItem::GOLD_NUGGET, 0, mt_rand(0, 1 + $lootingL));
            $drops[] = ItemItem::get(ItemItem::ROTTEN_FLESH, 0, mt_rand(0, 1 + $lootingL));
        }
        return $drops;
    }
}
