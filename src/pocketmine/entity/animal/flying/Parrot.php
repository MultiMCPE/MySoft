<?php

namespace pocketmine\entity\animal\flying;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\IntTag;
use pocketmine\entity\animal\FlyingAnimal;
use pocketmine\entity\Creature;
use pocketmine\block\Block;
use pocketmine\level\format\FullChunk;
#TODO: Taming
class Parrot extends FlyingAnimal{
	
    const NETWORK_ID = 30;
    
    const COLOR_RED = 1;
    const COLOR_BLUE = 2;
    const COLOR_CYAN = 3;
    const COLOR_SILVER = 4;
    
    public $width = 0.5;
    public $length = 0.484;
    public $height = 0.9;
    
    public $dropExp = [1, 3];

    public $drag = 0.2;
    public $gravity = 0.3;
    
    public function __construct(FullChunk $level, Compound $nbt){
        if(!isset($nbt->Variant)){
            $nbt->Variant = new IntTag("Variant", mt_rand(0, 4));
        }
        parent::__construct($level, $nbt);
    }
    
    public function getSpeed(){
        return 1.1;
    }

    public function getName(){
        return "Parrot";
    }

    public function targetOption(Creature $creature, $distance){
        return false;
    }
    
    public function getMaxHealth(){
        return 6;
    }
    
    public function setColor($type){
        $this->namedtag->Variant = new IntTag("Variant", $type);
    }
    
    public function getColor(){
        return $this->namedtag["Variant"];
    }
    
    public function setDancing($value = true){
    	$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_DANCING, $value);
    }
    
	public function isDancing(){
    	return $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_DANCING);
    }
    
    public function entityBaseTick($tickDiff = 1){
    	foreach($this->getBlocksAround() as $block){
    	    if($block->getId() === Block::NOTE_BLOCK){
    	        $this->setDancing(true);
           }else{
           	$this->setDancing(false);
           }
       }
   }
   
   public function getDrops(){
        $drops = [
            Item::get(Item::FEATHER, 0, mt_rand(1, 2))
        ];

        return $drops;
    }
}
