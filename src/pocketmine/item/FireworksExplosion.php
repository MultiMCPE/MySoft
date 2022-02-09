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
use pocketmine\item\Firework;
class FireworksExplosion{
	/** @var int|null */
	public $color;
	/** @var int|null */
	public $fade;
	/** @var bool */
	public $flicker;
	/** @var bool */
	public $trail;
	/** @var int */
	public $type;

	/**
	 * FireworksExplosion constructor.
	 * @param int|null $color
	 * @param int|null $fade
	 * @param bool     $flicker
	 * @param bool     $trail
	 * @param int      $type
	 */
	public function __construct($color = null, $fade = null, $flicker = false, $trail = false, $type = Firework::TYPE_SMALL_BALL){
		$this->color = $color;
		$this->fade = $fade;
		$this->flicker = $flicker;
		$this->trail = $trail;
		$this->type = $type;
	}

	/**
	 * @return int|null
	 */
	public function getColor(){
		return $this->color;
	}

	/**
	 * @return int|null
	 */
	public function getFade(){
		return $this->fade;
	}

	/**
	 * @return bool
	 */
	public function isFlickering(){
		return $this->flicker;
	}

	/**
	 * @return bool
	 */
	public function hasTrail(){
		return $this->trail;
	}

	/**
	 * @return int
	 */
	public function getType(){
		return $this->type;
	}
}