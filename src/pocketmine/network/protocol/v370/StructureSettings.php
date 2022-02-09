<?php

namespace pocketmine\network\protocol\v370;

use pocketmine\math\Vector3;

class StructureSettings{
	/** @var string */
	public $paletteName;
	/** @var bool */
	public $ignoreEntities;
	/** @var bool */
	public $ignoreBlocks;
	/** @var int */
	public $structureSizeX;
	/** @var int */
	public $structureSizeY;
	/** @var int */
	public $structureSizeZ;
	/** @var int */
	public $structureOffsetX;
	/** @var int */
	public $structureOffsetY;
	/** @var int */
	public $structureOffsetZ;
	/** @var int */
	public $lastTouchedByPlayerID;
	/** @var int */
	public $rotation;
	/** @var int */
	public $mirror;
	/** @var float */
	public $integrityValue;
	/** @var int */
	public $integritySeed;
	/** @var Vector3 */
	public $pivot;
}
