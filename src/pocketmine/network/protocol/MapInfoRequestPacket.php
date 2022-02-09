<?php
/*
 _      _          _____    _____    ___  _____
| \    / |  \  /  |        |     |  |       |
|  \  /  |   \/   |_____   |     | _|__     |
|   \/   |   /          |  |     |  |       |
|        |  /     ______|  |_____|  |       |
*/
namespace pocketmine\network\protocol;

class MapInfoRequestPacket extends PEPacket {

	const NETWORK_ID = Info::MAP_INFO_REQUEST_PACKET;
	const PACKET_NAME = "MAP_INFO_REQUEST_PACKET";
	
	public $mapId;

	public function decode($playerProtocol) {
		$this->getHeader($playerProtocol);
		$this->mapId = $this->getSignedVarInt();
	}

	public function encode($playerProtocol) {
	    $this->reset($playerProtocol);
	    $this->putSignedVarInt($this->mapId);
	}
}
