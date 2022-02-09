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
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\Level;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\nbt\tag\Compound;
use pocketmine\Player;
use pocketmine\level\format\FullChunk;

class Rabbit extends Animal
{
    const NETWORK_ID = 18;

    const DATA_RABBIT_TYPE = 18;
    const DATA_JUMP_TYPE = 19;

    const TYPE_BROWN = 0;
    const TYPE_WHITE = 1;
    const TYPE_BLACK = 2;
    const TYPE_BLACK_WHITE = 3;
    const TYPE_GOLD = 4;
    const TYPE_SALT_PEPPER = 5;
    const TYPE_KILLER_BUNNY = 99;

    public $height = 0.5;
    public $width = 0.5;
    public $length = 0.5;

    public $dropExp = [1, 3];

    public function initEntity()
    {
        $this->setMaxHealth(3);
        parent::initEntity();
    }

    public function __construct(FullChunk $level, Compound $nbt)
    {
        if (!isset($nbt->RabbitType)) {
            $nbt->RabbitType = new ByteTag("RabbitType", $this->getRandomRabbitType());
        }
        parent::__construct($level, $nbt);

        $this->setDataProperty(self::DATA_RABBIT_TYPE, self::DATA_TYPE_BYTE, $this->getRabbitType());
    }

    public function getRandomRabbitType()
    {
        $arr = [0, 1, 2, 3, 4, 5, 99];
        return $arr[mt_rand(0, count($arr) - 1)];
    }

    public function setRabbitType($type)
    {
        $this->namedtag->RabbitType = new ByteTag("RabbitType", $type);
    }

    public function getRabbitType()
    {
        return (int)$this->namedtag["RabbitType"];
    }

    public function getName()
    {
        return "Rabbit";
    }

    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = Rabbit::NETWORK_ID;
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
        $drops = [ItemItem::get(ItemItem::RABBIT_HIDE, 0, mt_rand(0, 1))];
        if ($this->getLastDamageCause() === EntityDamageEvent::CAUSE_FIRE) {
            $drops[] = ItemItem::get(ItemItem::COOKED_RABBIT, 0, mt_rand(0, 1));
        } else {
            $drops[] = ItemItem::get(ItemItem::RAW_RABBIT, 0, mt_rand(0, 1));
        }
        if (mt_rand(1, 200) <= (5 + 2 * $lootingL)) {
            $drops[] = ItemItem::get(ItemItem::RABBIT_FOOT, 0, 1);
        }
        return $drops;
    }
}
