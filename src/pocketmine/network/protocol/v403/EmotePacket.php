<?php

namespace pocketmine\network\protocol\v403;

use pocketmine\network\protocol\{PEPacket, Info331};

class EmotePacket extends PEPacket{
    
	const NETWORK_ID = Info331::EMOTE_PACKET;
	const PACKET_NAME = "EMOTE_PACKET";

	/** @var int */
	public $eid;
	/** @var string */
	public $emoteId;
	/** @var int */
	public $flags;

	public function decode($playerProtocol){
	    $this->getHeader($playerProtocol);
		$this->eid = $this->getSignedVarInt();
		$this->emoteId = $this->getString();
		$this->flags = $this->getByte();
	}

	public function encode($playerProtocol){
	    $this->reset($playerProtocol);
		$this->putSignedVarInt($this->eid);
		$this->putString($this->emoteId);
		$this->putByte($this->flags);
	}
}