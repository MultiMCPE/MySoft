<?php

namespace pocketmine\network\protocol\v370;

class StructureEditorData{
	public const TYPE_DATA = 0;
	public const TYPE_SAVE = 1;
	public const TYPE_LOAD = 2;
	public const TYPE_CORNER = 3;
	public const TYPE_INVALID = 4;
	public const TYPE_EXPORT = 5;

	/** @var string */
	public $structureName;
	/** @var string */
	public $structureDataField;
	/** @var bool */
	public $includePlayers;
	/** @var bool */
	public $showBoundingBox;
	/** @var int */
	public $structureBlockType;
	/** @var StructureSettings */
	public $structureSettings;
	/** @var int */
	public $structureRedstoneSaveMove;
}
