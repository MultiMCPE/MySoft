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

namespace pocketmine\network\protocol;

class CommandStepPacket extends PEPacket {

	const NETWORK_ID = Info::COMMAND_STEP_PACKET;
	const PACKET_NAME = "COMMAND_STEP_PACKET";

	public $command;
	public $overload;
	public $uvarint1;
	public $currentStep;
	public $done;
	public $clientId;
	public $inputJson;
	public $outputJson;

	public function decode($playerProtocol) {
		$this->getHeader($playerProtocol);
		$this->command = $this->getString();
		$this->overload = $this->getString();
        $this->uvarint1 = $this->getVarInt();
		$this->currentStep = $this->getVarInt();
		$this->done = $this->getByte();
		$this->clientId = $this->getVarInt();
		$this->inputJson = json_decode($this->getString());
		$this->outputJson = $this->getString();
	}
	public function encode($playerProtocol) {}

}