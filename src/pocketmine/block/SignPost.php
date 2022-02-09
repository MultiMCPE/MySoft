<?php

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
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\tile\Tile;

class SignPost extends Transparent{

	public $id = self::SIGN_POST;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 1;
	}

	public function isSolid(){
		return false;
	}

	public function getName(){
		return "Sign Post";
	}

	public function getBoundingBox(){
		return null;
	}

	/**
	 * @param Item        $item
	 * @param Block       $block
	 * @param Block       $target
	 * @param int         $face
	 * @param float       $fx
	 * @param float       $fy
	 * @param float       $fz
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face !== 0){
			$nbt = new Compound("", [
				"id" => new StringTag("id", Tile::SIGN),
				"x" => new IntTag("x", $block->x),
				"y" => new IntTag("y", $block->y),
				"z" => new IntTag("z", $block->z),
				"Text1" => new StringTag("Text1", ""),
				"Text2" => new StringTag("Text2", ""),
				"Text3" => new StringTag("Text3", ""),
				"Text4" => new StringTag("Text4", "")
			]);

			if($player !== null){
				$nbt->Creator = new StringTag("Creator", $player->getName());
			}

			if($item->hasCustomBlockData()){
				foreach($item->getCustomBlockData() as $key => $v){
					$nbt->{$key} = $v;
				}
			}

			$faces = [
				2 => 2,
				3 => 3,
				4 => 4,
				5 => 5,
			];
			
			if(!isset($faces[$face])){
				$this->meta = floor((($player->yaw + 180) * 16 / 360) + 0.5) & 0x0F;
				$this->getLevel()->setBlock($block, Block::get(Item::SIGN_POST, $this->meta), true);

				return true;
			}else{
				$this->meta = $faces[$face];
				$this->getLevel()->setBlock($block, Block::get(Item::WALL_SIGN, $this->meta), true);

				return true;
			}

			Tile::createTile(Tile::SIGN, $this->getLevel(), $nbt);
			return true;
		}
		
		return false;
	}

	public function onUpdate($type, $deep){
		if (!Block::onUpdate($type, $deep)) {
			return false;
		}
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->getId() === self::AIR){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}

	public function getDrops(Item $item){
		return [
			[Item::SIGN, 0, 1],
		];
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}
	
}