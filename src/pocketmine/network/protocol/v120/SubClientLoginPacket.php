<?php

namespace pocketmine\network\protocol\v120;

use pocketmine\network\protocol\Info120;
use pocketmine\network\protocol\PEPacket;

class SubClientLoginPacket extends PEPacket {

	const NETWORK_ID = Info120::SUB_CLIENT_LOGIN_PACKET;
	const PACKET_NAME = "SUB_CLIENT_LOGIN_PACKET";

	/** @var string */
	public $connectionRequestData;

	public function decode($playerProtocol) {
		$this->getHeader($playerProtocol);
		$this->connectionRequestData = $this->getString();
	}

	public function encode($playerProtocol) {}
}