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

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityCombustByBlockEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\block\Block;
use pocketmine\network\protocol\LevelSoundEventPacket;

class Lava extends Liquid {

	public $id = self::LAVA;

	/**
	 * Lava constructor.
	 *
	 * @param int $meta
	 */
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @return int
	 */
	public function getLightLevel(){
		return 15;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Lava";
	}

	public function tickRate() : int{
		return 30;
	}

	public function getFlowDecayPerBlock() : int{
		return 2; //TODO: this is 1 in the nether
	}

	protected function checkForHarden(){
		$colliding = null;
		for($side = 1; $side <= 5; ++$side){ //don't check downwards side
			$blockSide = $this->getSide($side);
			if($blockSide instanceof Water){
				$colliding = $blockSide;
				break;
			}
		}

		if($colliding !== null){
			if($this->getDamage() === 0){
				$this->liquidCollide($colliding, Block::get(Block::OBSIDIAN));
			}elseif($this->getDamage() <= 4){
				$this->liquidCollide($colliding, Block::get(Block::COBBLESTONE));
			}
		}
	}

	protected function flowIntoBlock(Block $block, int $newFlowDecay) : void{
		if($block instanceof Water){
			$block->liquidCollide($this, Block::get(Block::STONE));
		}else{
			parent::flowIntoBlock($block, $newFlowDecay);
		}
	}

	public function getStillForm() : Block{
		return Block::get(Block::STILL_LAVA, $this->meta);
	}

	public function getFlowingForm() : Block{
		return Block::get(Block::LAVA, $this->meta);
	}

	public function getBucketFillSound() : int{
		return LevelSoundEventPacket::SOUND_BUCKET_FILL_LAVA;
	}

	public function getBucketEmptySound() : int{
		return LevelSoundEventPacket::SOUND_BUCKET_EMPTY_LAVA;
	}

	/**
	 * @param Entity $entity
	 */
	public function onEntityCollide(Entity $entity){
		$entity->fallDistance *= 0.5;
		if(!$entity->hasEffect(Effect::FIRE_RESISTANCE)){
			$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_LAVA, 4);
			$entity->attack($ev->getFinalDamage(), $ev);
		}

		$ev = new EntityCombustByBlockEvent($this, $entity, 15);
		Server::getInstance()->getPluginManager()->callEvent($ev);
		if(!$ev->isCancelled()){
			$entity->setOnFire($ev->getDuration());
		}

		$entity->resetFallDistance();
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
		$ret = $this->getLevel()->setBlock($this, $this, true, false);
		$this->getLevel()->scheduleUpdate($this, $this->tickRate());

		return $ret;
	}

}
