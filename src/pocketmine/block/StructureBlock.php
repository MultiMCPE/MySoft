<?php

namespace pocketmine\block;

use pocketmine\item\Item;

class StructureBlock extends Solid{
    
	public $id = self::STRUCTURE_BLOCK;
	
	public function __construct(){

	}
	
	public function getName(){
		return "Structure Block";
	}
	
	public function getHardness(){
		return -1;
	}

	public function getResistance(){
		return 18000000;
	}
	
	public function isBreakable(Item $item){
		return false;
	}
}