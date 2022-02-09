<?php

/*
 *
 *  ____			_		_   __  __ _				  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___	  |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|	 |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\{Player, Server};
use pocketmine\nbt\tag\{Compound, Enum, FloatTag, DoubleTag};
use pocketmine\entity\{Entity, EnderCrystal as EnderCrystalEntity};

class EnderCrystal extends Item {

	/**
	 * EnderCrystal constructor.
	 *
	 * @param int $meta
	 * @param int $count
	 */
	
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::ENDER_CRYSTAL, 0, $count, "Ender Crystal");
	}

	/**
	 * @return int
	 */
	public function getMaxStackSize(){
		return 64;
	}

	/**
	 * @return bool
	 */
	
	public function canBeActivated(){
		return true;
	}

	/**
	 * @param Level  $level
	 * @param Player $player
	 * @param Block  $block
	 * @param Block  $target
	 * @param        $face
	 * @param        $fx
	 * @param        $fy
	 * @param        $fz
	 *
	 * @return bool
	 */
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($target->getId() == self::OBSIDIAN or $target->getId() == self::BEDROCK){
            if(Server::getInstance()->getAdvancedProperty("main.ender-crystals", false) == true){
		        $nbt = new Compound(); 
	         	$nbt->Pos = new Enum("Pos", [
	        	new DoubleTag("", $target->getX() + 0.5), 
	        	new DoubleTag("", $target->getY() + 1), 
	        	new DoubleTag("", $target->getZ() + 0.5)]); 
	        	$nbt->Motion = new Enum("Motion", [
	        	new DoubleTag("", 0), new DoubleTag("", 0), 
	        	new DoubleTag("", 0)]);
	        	$nbt->Rotation = new Enum("Rotation", [
	        	new FloatTag("", 0.0), new FloatTag("", 0.0)]);
	            	
	         	$entity = new EnderCrystalEntity($player->getLevel()->getChunk($block->getX() + 0.5 >> 4, $block->getZ() + 0.5 >> 4), $nbt);
	        	$entity->spawnToAll();
		    	return true;
            }
		}

		return false;
	}
}
