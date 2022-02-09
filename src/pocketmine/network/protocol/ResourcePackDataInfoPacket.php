<?php

namespace pocketmine\network\protocol;

use pocketmine\network\multiversion\MultiversionEnums;

class ResourcePackDataInfoPacket extends PEPacket {

	const NETWORK_ID = Info::RESOURCE_PACK_DATA_INFO_PACKET;
	const PACKET_NAME = "RESOURCE_PACK_DATA_INFO_PACKET";

	const TYPE_INVALID = 'TYPE_INVALID';
	const TYPE_ADDON = 'TYPE_ADDON';
	const TYPE_CACHED = 'TYPE_CACHED';
	const TYPE_COPY_PROTECTED = 'TYPE_COPY_PROTECTED';
	const TYPE_BEHAVIOR = 'TYPE_BEHAVIOR';
	const TYPE_PERSONA_PIECE = 'TYPE_PERSONA_PIECE';
	const TYPE_RESOURCE = 'TYPE_RESOURCE';
	const TYPE_SKINS = 'TYPE_SKINS';
	const TYPE_WORLD_TEMPLATE = 'TYPE_WORLD_TEMPLATE';
	const TYPE_COUNT = 'TYPE_COUNT';
	
	/** @var string */
	public $packId;
	/** @var int */
	public $maxChunkSize;
	/** @var int */
	public $chunkCount;
	/** @var int */
	public $compressedPackSize;
	/** @var string */
	public $sha256;
	/** @var bool */
	public $isPremium = false;
	/** @var int */
	public $packType = self::TYPE_RESOURCE; //TODO: check the values for this
    
	public function decode($playerProtocol) {}
	
	public function encode($playerProtocol) {
		$this->reset($playerProtocol);
		$this->putString($this->packId);
		$this->putLInt($this->maxChunkSize);
		$this->putLInt($this->chunkCount);
		$this->putLLong($this->compressedPackSize);
		$this->putString($this->sha256);
		if ($playerProtocol >= Info::PROTOCOL_360) {
			$this->putBool($this->isPremium);
			$this->putByte(MultiversionEnums::getPackTypeId($playerProtocol, $this->packType));
		}
	}
}