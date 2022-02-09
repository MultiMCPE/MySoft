<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\event\entity;


use pocketmine\event\Cancellable;
use pocketmine\math\Vector3;

class EntityGenerateEvent extends EntityEvent implements Cancellable {
	public static $handlerList = null;

	const CAUSE_AI_HOLDER = 0;
	const CAUSE_MOB_SPAWNER = 1;

	/** @var Vector3 */
	private $position;
	private $cause;
	private $entityType;

	/**
	 * EntityGenerateEvent constructor.
	 *
	 * @param Vector3 $pos
	 * @param int      $entityType
	 * @param int      $cause
	 */
	public function __construct(Vector3 $pos, $entityType, $cause = self::CAUSE_MOB_SPAWNER){
		$this->position = $pos;
		$this->entityType = $entityType;
		$this->cause = $cause;
	}

	/**
	 * @return Position
	 */
	public function getPosition(){
		return $this->position;
	}

	/**
	 * @param Vector3 $pos
	 */
	public function setPosition(Vector3 $pos){
		$this->position = $pos;
	}

	/**
	 * @return int
	 */
	public function getType(){
		return $this->entityType;
	}

	/**
	 * @return int
	 */
	public function getCause(){
		return $this->cause;
	}
}