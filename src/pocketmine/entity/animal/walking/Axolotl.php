<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\entity\animal\walking;

use pocketmine\block\StainedGlass;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\Level;
use pocketmine\nbt\tag\Compound;
use pocketmine\level\format\FullChunk;
use pocketmine\entity\{animal\WalkingAnimal, Colorable, Creature};

class Axolotl extends WalkingAnimal implements Colorable
{
    const NETWORK_ID = 114;

    const DATA_COLOR_INFO = 16;

    public $width = 0.625;
    public $length = 1.4375;
    public $height = 1.8;

    public function getName()
    {
        return "Axolotl";
    }

	public function initEntity(){
		parent::initEntity();

		$this->setMaxHealth(20);
	}

	public function targetOption(Creature $creature, $distance){
		if($creature instanceof Player){
			return $creature->spawned && $creature->isAlive() && !$creature->closed && $creature->getInventory()->getItemInHand()->getId() == Item::WHEAT && $distance <= 39;
		}
		return false;
	}

    public function getDrops(){
		if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
            $drops = [
                ItemItem::get(ItemItem::RAW_FISH, 0, mt_rand(0, 2))
            ];
            return $drops;
		}
		return [];
    }
}
