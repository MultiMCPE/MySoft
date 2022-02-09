<?php
/*
use pocketmine\utils\Random;
use pocketmine\level\generator\object\Tree;
use pocketmine\level\particle\HappyVillagerParticle;
	public function canBeActivated(){
		return true;
	}
	public function onActivate(Item $item, Player $player = null){
		if($item->getId() === Item::DYE and $item->getDamage() === 0x0F){
			if($player->isSurvival()) {
				$count = $item->getCount();
				if(--$count <= 0){
					$player->getInventory()->setItemInHand(Item::get(Item::AIR));
					return true;
				}
				$item->setCount($count);
			    $player->getInventory()->setItemInHand($item);
			}
            $this->level->setBlock($this, Block::get(Block::AIR), true, true);
			$rand = mt_rand(0, 15);
			if($rand >= 10){
		    	Tree::growTree($this->getLevel(), $this->x, $this->y, $this->z, new Random(mt_rand()), $this->meta);
			} else {
                $particle = new HappyVillagerParticle(new Vector3($this->x, $this->y, $this->z), 1);
                $this->getLevel()->addParticle($particle);
			}
			return true;
		}
		return true;
	}
*/
/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class AcaciaDoor extends Door{

	public $id = self::ACACIA_DOOR_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName(){
		return "Acacia Door Block";
	}

	public function canBeActivated(){
		return true;
	}

	public function getHardness(){
		return 3;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function getDrops(Item $item){
		return [
			[Item::ACACIA_DOOR, 0, 1],
		];
	}
}
