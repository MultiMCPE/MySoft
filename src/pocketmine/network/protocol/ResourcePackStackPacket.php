<?php

namespace pocketmine\network\protocol;

class ResourcePackStackPacket extends PEPacket {

	const NETWORK_ID = Info::RESOURCE_PACKS_STACK_PACKET;
	const PACKET_NAME = "RESOURCE_PACKS_STACK_PACKET";

	public $mustAccept = false;

	/** @var ResourcePack[] */
	public $behaviorPackStack = [];
	/** @var ResourcePack[] */
	public $resourcePackStack = [];

	public function decode($playerProtocol) {}

	public function encode($playerProtocol) {
		$this->reset($playerProtocol);
		
		$this->putBool($this->mustAccept);
		
		$this->putVarInt(count($this->behaviorPackStack));
		foreach ($this->behaviorPackStack as $entry) {
			$this->putString($entry->getPackId());
			$this->putString($entry->getPackVersion());
			$this->putString('');
		}
		
		$this->putVarInt(count($this->resourcePackStack));
		foreach($this->resourcePackStack as $entry){
			$this->putString($entry->getPackId());
			$this->putString($entry->getPackVersion());
			$this->putString('');
		}
		
		if ($playerProtocol >= Info::PROTOCOL_290) {
			if ($playerProtocol < Info::PROTOCOL_415) {
				$this->putVarInt(0); // ???
			}			
			$this->putString('*'); // ???
		}
		
		if ($playerProtocol >= Info::PROTOCOL_415) {
			$this->putVarInt(0); //Experiments count
			$this->putByte(0); //Were any Experiments toggled		
			$this->putByte(0); //??	
			$this->putByte(0); //??		
			$this->putByte(0); //??		
		}
	}
}