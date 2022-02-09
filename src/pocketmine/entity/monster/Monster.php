<?php

namespace pocketmine\entity\monster;

use pocketmine\entity\Entity;

interface Monster{

	public function attackEntity(Entity $player);

	public function getDamage($difficulty = null);
	public function getMinDamage($difficulty = null);
	public function getMaxDamage($difficulty = null);

	public function setDamage($damage, $difficulty = null);
	public function setMinDamage($damage, $difficulty = null);
	public function setMaxDamage($damage, $difficulty = null);

}