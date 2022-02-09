<?php

namespace pocketmine\network\protocol;

class ResourcePackChunkRequestPacket extends PEPacket {
	
	const NETWORK_ID = Info::RESOURCE_PACK_CHUNK_REQUEST_PACKET;
	const PACKET_NAME = "RESOURCE_PACK_CHUNK_REQUEST_PACKET";
	
	public $packId;
	public $chunkIndex;
	
	public function decode($playerProtocol) {
		$this->getHeader($playerProtocol);
		$this->packId = $this->getString();
		$this->chunkIndex = $this->getLInt();
	}

	public function encode($playerProtocol) {}
}