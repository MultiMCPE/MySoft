<?php

namespace pocketmine\network\protocol;

class ResourcePackChunkDataPacket extends PEPacket {

	const NETWORK_ID = Info::RESOURCE_PACK_CHUNK_DATA_PACKET;
	const PACKET_NAME = "RESOURCE_PACK_CHUNK_DATA_PACKET";

	public $packId;
	public $chunkIndex;
	public $progress;
	public $data;

	public function decode($playerProtocol) {}

	public function encode($playerProtocol) {
		$this->reset($playerProtocol);
		$this->putString($this->packId);
		$this->putLInt($this->chunkIndex);
		$this->putLLong($this->progress);
		if ($playerProtocol >= Info::PROTOCOL_370) {
		    $this->putString($this->data);
		} else {
	    	$this->putLInt(strlen($this->data));
	    	$this->put($this->data);
		}
	}
}