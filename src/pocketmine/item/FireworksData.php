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

class FireworksData{
	/** @var int */
	public $flight;
	/** @var FireworksExplosion[] */
	public $explosions;

	/**
	 * FireworksData constructor.
	 * @param int                  $flight
	 * @param FireworksExplosion[] $explosions
	 */
	public function __construct($flight = 1, $explosions = []){
		$this->flight = $flight;
		$this->explosions = $explosions;
	}

	/**
	 * @return int
	 */
	public function getFlight(){
		return $this->flight;
	}

	/**
	 * @return FireworksExplosion[]
	 */
	public function getExplosions(){
		return $this->explosions;
	}
}