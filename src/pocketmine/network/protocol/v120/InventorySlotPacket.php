<?php

namespace pocketmine\network\protocol\v120;

use pocketmine\network\protocol\{Info120, Info};
use pocketmine\network\protocol\PEPacket;

class InventorySlotPacket extends PEPacket {
	
	const NETWORK_ID = Info120::INVENTORY_SLOT_PACKET;
	const PACKET_NAME = "INVENTORY_SLOT_PACKET";
	
	/** @var integer */
	public $containerId;
	/** @var integer */
	public $slot;
	/** @var Item */
	public $item = null;
	
	public function decode($playerProtocol) {}

	public function encode($playerProtocol) {
		$this->reset($playerProtocol);
		$this->putVarInt($this->containerId);
		$this->putVarInt($this->slot);
		if ($this->item == null) {
			if ($playerProtocol >= Info::PROTOCOL_392 && $playerProtocol <= Info::PROTOCOL_428) {
				$this->putSignedVarInt(0);
			}
			$this->putSignedVarInt(0);
		} else {
			if ($playerProtocol >= Info::PROTOCOL_392 && $playerProtocol <= Info::PROTOCOL_428) {
				$this->putSignedVarInt(1);
			}
			$this->putSlot($this->item, $playerProtocol);
		}
	}
}