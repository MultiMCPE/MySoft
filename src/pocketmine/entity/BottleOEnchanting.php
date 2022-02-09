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

namespace pocketmine\entity;

use pocketmine\level\particle\SplashParticle;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\Player;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\entity\Projectile;
use pocketmine\network\multiversion\Entity as EntityIds;
use pocketmine\entity\ExperienceOrb;
use pocketmine\level\format\FullChunk;


class BottleOEnchanting extends Projectile{
	const NETWORK_ID = 69;
	protected $gravity = 0.05;
	protected $drag = 0.01;

	protected $givenOutXp = false;

    public $shootingEntity;

    public function __construct(FullChunk $level, Compound $nbt, Entity $shootingEntity = null){
        parent::__construct($level, $nbt, $shootingEntity);
        $this->shootingEntity = $shootingEntity;
    }

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}
		$hasUpdate = parent::onUpdate($currentTick);
		// If bottle has collided with the ground or with an entity
		if($this->age > 1200 || $this->isCollided || $this->hadCollision){
			// Add Splash particles
			for($i = 0; $i <= 14; $i++) {
				$particle = new SplashParticle($this->add(
					$this->width / 2 + mt_rand(-100, 100) / 500,
					$this->height / 2 + mt_rand(-100, 100) / 500,
					$this->width / 2 + mt_rand(-100, 100) / 500));


				$this->level->addParticle($particle);
			}
			if($this->shootingEntity instanceof Player) {
				$this->shootingEntity->sendSound("SOUND_GLASS", ['x' => $this->getX(), 'y' => $this->getY(), 'z' => $this->getZ()],EntityIds::ID_NONE, -1 ,$this->getViewers());
			}
			$orbCount = mt_rand(2,3);
			$chunk = $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4);
			for ($i = 0; $i < $orbCount; $i++) {
				$nbt = new Compound("", [
					"Pos" => new Enum("Pos", [
						new DoubleTag("", $this->x),
						new DoubleTag("", $this->y),
						new DoubleTag("", $this->z)
					]),
					"Motion" => new Enum("Motion", [
						new DoubleTag("", 0),
						new DoubleTag("", 0),
						new DoubleTag("", 0)
					]),
					"Rotation" => new Enum("Rotation", [
						new FloatTag("", 0),
						new FloatTag("", 0)
					])
				]);
				new ExperienceOrb($chunk, $nbt);
			}

			$this->close();
			$hasUpdate = true;
		}
		return $hasUpdate;
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = BottleOEnchanting::NETWORK_ID;
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
}