<?php

namespace pocketmine\entity;

use pocketmine\math\Math;
use pocketmine\math\Vector3;
use pocketmine\block\Air;
use pocketmine\block\Liquid;
use pocketmine\Player;
use pocketmine\entity\monster\Monster;
use pocketmine\block\Water;

abstract class WalkingEntity extends BaseEntity {

	protected $agrDistance = 25;

	protected function checkTarget($update = false) {
		if ($this->isKnockback() && !$update && $this->baseTarget instanceof Player && $this->baseTarge->isAlive() && sqrt($this->distanceSquared($player)) < $this->agrDistance) {
			return;
		}
		if ($update) {
			$this->moveTime = 0;
		}
		if ($this instanceof Monster && !$this->isFriendly()) {
			$near = PHP_INT_MAX;
			foreach ($this->getLevel()->getServer()->getOnlinePlayers() as $player) {
				if((!$player->isCreative()) and (!$player->isSpectator())){
				    if ($player->isAlive()) {
					    $distance = sqrt($this->distanceSquared($player));

					    if ($distance >= $near) {
						    continue;
		                }

					    $target = $player;
					    $near = $distance;
				    }
				}
			}

			if ($near <= $this->agrDistance) {
				$this->baseTarget = $target;
				$this->moveTime = 0;
				return;
			}
		}

		if ($this->moveTime <= 0) {
			$i = 0;
			while($i < 10) {
				$x = mt_rand(20, 100);
				$z = mt_rand(20, 100);
				$this->moveTime = mt_rand(0, 100);
				$this->baseTarget = new Vector3($this->getX() + (mt_rand(0, 1) ? $x : -$x), $this->getY(), $this->getZ() + (mt_rand(0, 1) ? $z : -$z));
				$y =  $this->level->getHighestBlockAt($this->baseTarget->getX(), $this->baseTarget->getZ());
				$this->baseTarget->y = $y;
				$block = $this->level->getBlock($this->baseTarget);
				if(($block instanceof Water)){
				}
				break;
				$i++;
			}
		}
	}

	public function updateMove() {
		if (!$this->isMovement()) {
			return null;
		}
		//if($rand > 90){

		/*$this->motionX = $this->getSpeed() * 0.15 * ($x / $diff);
		$this->motionZ = $this->getSpeed() * 0.15 * ($z / $diff);
		$this->checkTarget(true);*/


		if ($this->isKnockback() || $this->sprintTime > 0) {
			$target = null;
				//$this->yaw = -atan2($this->motionX, $this->motionZ) * 180 / M_PI;
				//$motion = new Vector3($this->baseTarget);
				//$motion->x = $motion->x + 1;
				//$motion->y = $motion->y + 1;
				//$motion->z = $motion->z + 1;
				//$this->setMotion($motion);
		} else {
			$this->checkTarget();
			if ($this->baseTarget instanceof Vector3) {
				$x = $this->baseTarget->x - $this->x;
				$z = $this->baseTarget->z - $this->z;
				if ($x ** 2 + $z ** 2 < 0.7) {
					$this->motionX = 0;
					$this->motionZ = 0;
				} else {
					$diff = abs($x) + abs($z);
					$this->motionX = $this->getSpeed() * 0.15 * ($x / $diff);
					$this->motionZ = $this->getSpeed() * 0.15 * ($z / $diff);
				}
				$this->yaw = -atan2($this->motionX, $this->motionZ) * 180 / M_PI;
				if ($this->baseTarget instanceof Player) {
					if(!($this->gamemode & 0x01) > 0){
					   // $y = $this->baseTarget->y - $this->y;
					   // $this->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x ** 2 + $z ** 2)));
					}
				}
			}

			$target = $this->baseTarget;
		}
		$isJump = false;
		$dx = $this->motionX;
		$dz = $this->motionZ;
		$dy = $this->motionZ;

		$newX = Math::floorFloat($this->x + $dx);
		$newZ = Math::floorFloat($this->z + $dz);
		$newy = Math::floorFloat($this->y + $dy);

		$block = $this->level->getBlock(new Vector3($newX, Math::floorFloat($this->y), $newZ));
		if (!($block instanceof Air) && !($block instanceof Liquid) && !$block->canBeFlowedInto()) {
			$block = $this->level->getBlock(new Vector3($newX, Math::floorFloat($this->y + 1), $newZ));
			if (!($block instanceof Air) && !($block instanceof Liquid) && !$block->canBeFlowedInto()) {
				$this->motionY = 0;
				$this->checkTarget(true);
				return;
			} else {
				$isJump = true;
				$this->motionY = 0.6;
				$this->y += 1;
			}
		} else {
			$block = $this->level->getBlock(new Vector3($newX, Math::floorFloat($this->y - 1), $newZ));
			if (!($block instanceof Air) && !($block instanceof Liquid)) {
				$blockY = Math::floorFloat($this->y);
				if ($this->y - $this->gravity * 4 > $blockY) {
					$this->motionY = -$this->gravity * 4;
				} else {
					$this->motionY = ($this->y - $blockY) > 0 ? ($this->y - $blockY) : 0;
				}
			} else {
				$this->motionY -= $this->gravity * 4;
			}
		}
		$dy = $this->motionY;
		$this->move($dx, $dy, $dz);
		$this->updateMovement();
		return $target;

	}

}
