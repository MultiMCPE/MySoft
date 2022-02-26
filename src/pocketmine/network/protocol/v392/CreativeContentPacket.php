<?php

namespace pocketmine\network\protocol\v392;

use pocketmine\network\protocol\PEPacket;
use pocketmine\network\protocol\Info331;
use pocketmine\network\protocol\Info;

class CreativeContentPacket extends PEPacket {

	const NETWORK_ID = Info331::CREATIVE_CONTENT_PACKET;
	const PACKET_NAME = "CREATIVE_CONTENT_PACKET";


	public $groups;
	public $items;

	public function decode($playerProtocol) {

	}

	public function encode($playerProtocol) {
		$this->reset($playerProtocol);
		if ($playerProtocol < Info::PROTOCOL_406) {
	        $this->putVarInt(count($this->groups));
	        foreach ($this->groups as $groupData) {
		        $this->putString($groupData['name']);
		        $this->putLInt($groupData['item']);
		        $this->putVarInt(0); // nbt count
	    	}
		}

		$this->putVarInt(count($this->items));
		$index = 1;
		foreach ($this->items as $itemData) {
		    if($playerProtocol >= Info::PROTOCOL_418 && $itemData['item']->getId() == 325 && $itemData['item']->getDamage() !== 0) continue;
		    if($playerProtocol >= Info::PROTOCOL_418){
		        $this->putSignedVarInt($index++);
		    } else {
		        $this->putVarInt($index++);
		        if ($playerProtocol < Info::PROTOCOL_406){
		            $this->putVarInt($itemData['group']);
		        }
		    }
		    $this->putSlot($itemData['item'], $playerProtocol, false);
		}
	}

}