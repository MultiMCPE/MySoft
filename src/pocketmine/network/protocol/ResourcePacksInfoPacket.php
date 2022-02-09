<?php

namespace pocketmine\network\protocol;

class ResourcePacksInfoPacket extends PEPacket {

	const NETWORK_ID = Info::RESOURCE_PACKS_INFO_PACKET;
	const PACKET_NAME = "RESOURCE_PACKS_INFO_PACKET";

	public $mustAccept = false; //force client to use selected resource packs
	/** @var ResourcePackInfoEntry */
	public $behaviorPackEntries = [];
	/** @var ResourcePackInfoEntry */
	public $resourcePackEntries = [];
	
	public function decode($playerProtocol) {}
    
	public function encode($playerProtocol) {
		$this->reset($playerProtocol);
		
	    $this->putBool($this->mustAccept);
	    
        if($playerProtocol >= Info::PROTOCOL_331){
            $this->putBool(false);
        }
        
	    if ($playerProtocol >= Info::PROTOCOL_448) {
		    $this->putBool(false);
	    }
	    
		$this->putLShort(count($this->behaviorPackEntries));
	    foreach ($this->behaviorPackEntries as $entry) {
			$this->putString($entry->getPackId());
			$this->putString($entry->getPackVersion());
			$this->putLLong($entry->getPackSize());
			$this->putString(''); //TODO: encryption key
			$this->putString(''); //TODO: subpack name
		    
		   	if ($playerProtocol >= Info::PROTOCOL_280) {
                $this->putString(''); // content identity
		    }
		    
            if ($playerProtocol >= Info::PROTOCOL_331) {
                $this->putBool(false); // has scripts
            }
        }
        
	    $this->putLShort(count($this->resourcePackEntries));
	    foreach ($this->resourcePackEntries as $entry) {
			$this->putString($entry->getPackId());
			$this->putString($entry->getPackVersion());
			$this->putLLong($entry->getPackSize());
			$this->putString(''); //TODO: encryption key
			$this->putString(''); //TODO: subpack name
		    
		    if ($playerProtocol >= Info::PROTOCOL_280) {
                $this->putString(''); // content identity
            }
            
            if ($playerProtocol >= Info::PROTOCOL_331) {
                $this->putBool(false); // has scripts
            }
            
			if ($playerProtocol >= Info::PROTOCOL_422) {
			    $this->putBool(false); // rtx
		    }
	    }
	}
}