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

namespace pocketmine\item;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Water;
use pocketmine\block\Liquid;
use pocketmine\block\Slab;
use pocketmine\block\Slab2;
use pocketmine\block\WoodSlab;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\level\Level;
use pocketmine\Player;

class LavaBucket extends Item{

	protected $itemIdBucket = self::LAVA_BUCKET;
	protected $targetBlock = Block::AIR;

	protected static $bucketByTarget = [
		8 => 0,
		0 => 8
		//Item::LAVA => Item::LAVA_BUCKET,
		//Item::STILL_LAVA => Item::LAVA_BUCKET,
		//Item::STILL_WATER => Item::WATER_BUCKET
	];

	public function __construct($meta = 0, $count = 1){
		parent::__construct($this->itemIdBucket, $meta, $count, "Lava Bucket");
	}

	public function getMaxStackSize(){
		return 1;
	}

	public function canBeActivated(){
		return true;
	}

	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		/*if ($block instanceof Slab || $block instanceof Slab2 || $block instanceof WoodSlab) {
			return false;
		}
		if ($target instanceof Slab || $target instanceof Slab2 || $target instanceof WoodSlab) {
			return false;
		}*/
		//$targetBlock = Block::get($this->targetBlock);

		//if($targetBlock instanceof Air){
			if($block instanceof Air or $block instanceof Water or $block instanceof Lava){
					$result = Item::get(Item::BUCKET, 0, 1);
					$player->getServer()->getPluginManager()->callEvent($ev = new PlayerBucketFillEvent($player, $block, $face, $this, $result));
					if(!$ev->isCancelled()){
						$player->getLevel()->setBlock($block, new Lava(), true, true);
						$player->getInventory()->setItemInHand($ev->getItem(), $player);
						/*if($player->isSurvival()){
							$player->getInventory()->setItemInHand($ev->getItem(), $player);
						}
						return true;*/
						return true;
					}else{
						$player->getInventory()->sendContents($player);
					}
				}else{
					return false;
				}
			}
		}
