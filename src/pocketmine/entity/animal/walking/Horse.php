<?php

namespace pocketmine\entity\animal\walking;

use pocketmine\entity\animal\WalkingAnimal;
use pocketmine\entity\Attribute;
use pocketmine\entity\Rideable;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\MobArmorEquipmentPacket;
use pocketmine\network\protocol\UpdateAttributesPacket;
use pocketmine\Player;
use pocketmine\entity\Creature;

class Horse extends WalkingAnimal implements Rideable{
	public $width = 0.6;
    public $length = 0.6;
    public $height = 0.6;
    protected $riderOffset = [0, 2.3, 0];
    public $flySpeed = 0.8;
    public $switchDirectionTicks = 100;

    public function getName()
    {
        return "Horse";
    }

    public function initEntity()
    {
        $this->setMaxHealth(10);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SADDLE, true);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_TAME_WOLF, true);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_POWER_JUMP, true);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_LEASHED, true);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SPRINTING, 0.2);
		
        parent::initEntity();
    }
}