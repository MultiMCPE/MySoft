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

#include <rules/DataPacket.h>


class PlaySoundPacket extends PEPacket{
	const NETWORK_ID = Info110::PLAY_SOUND_PACKET;
	const PACKET_NAME = "PLAY_SOUND_PACKET";

	public $soundName, $volume, $pitch;
	public $x;
	public $y;
	public $z;

	public function decode($playerProtocol){
		$this->getHeader($playerProtocol);
		$this->soundName = $this->getString();
		$this->x = $this->getSignedVarInt() / 8;
		$this->y = $this->getVarInt() / 8;
		$this->z = $this->getSignedVarInt() / 8;
		$this->volume = $this->getLFloat();
		$this->pitch = $this->getLFloat();
	}

	public function encode($playerProtocol){
		$this->reset($playerProtocol);
		$this->putString($this->soundName);
		$this->putSignedVarInt((int) ($this->x * 8));
		$this->putVarInt((int) ($this->y * 8));
		$this->putSignedVarInt((int) ($this->z * 8));
		$this->putLFloat($this->volume);
		$this->putLFloat($this->pitch);
	}

}
