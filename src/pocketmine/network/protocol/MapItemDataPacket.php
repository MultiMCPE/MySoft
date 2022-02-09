<?php

namespace pocketmine\network\protocol;

use pocketmine\network\protocol\Info;
use pocketmine\utils\Color;

class MapItemDataPacket extends PEPacket {

	const NETWORK_ID = Info::CLIENTBOUND_MAP_ITEM_DATA_PACKET;
	const PACKET_NAME = "CLIENTBOUND_MAP_ITEM_DATA_PACKET";
	
	const TRACKED_OBJECT_TYPE_ENTITY = 0;
	const TRACKED_OBJECT_TYPE_BLOCK = 1;

	public $mapId;
	public $flags;
	public $dimension = 0;
	public $scale;
	public $width;
	public $height;
	public $data;
	public $pointners = [];
	public $entityIds = [];
	public $isLockedMap = false;

	public function decode($playerProtocol) {
	}

	public function encode($playerProtocol) {
		$this->reset($playerProtocol);
		$this->putSignedVarInt($this->mapId);
		$this->putVarInt($this->flags);
		if ($playerProtocol >= Info::PROTOCOL_120) {
			$this->putByte($this->dimension); // dimension
		}
		if ($playerProtocol >= Info::PROTOCOL_351) {
			$this->putByte($this->isLockedMap);
		}
		switch ($this->flags) {
			case 2:
				$this->putByte($this->scale);
				$this->putSignedVarInt($this->width);
				$this->putSignedVarInt($this->height);
				$this->putSignedVarInt(0);
				$this->putSignedVarInt(0);
				if ($playerProtocol >= Info::PROTOCOL_120) {
					$this->putVarInt($this->width * $this->height);
				}
				$this->put($this->data);
		    	/*for($y = 0; $y < $this->height; ++$y){
			    	for($x = 0; $x < $this->width; ++$x){
			        	if ($playerProtocol >= Info::PROTOCOL_120) {
				            $this->putVarInt($this->data[$y][$x]->toABGR());
			        	} else {
				            $this->putLInt($this->data[$y][$x]->toABGR());
			        	}
			    	}
				}*/
				break;
			/*case 4:
				$this->putByte($this->scale);
				if ($playerProtocol >= Info::PROTOCOL_120) {
					if (($entityCount = count($this->entityIds)) < ($pointnerCount = count($this->pointners))) {
						$lastFaKeId = -1;
						while ($entityCount < $pointnerCount) {
							array_unshift($this->entityIds, $lastFaKeId--);
							$entityCount++;
						}
					}
					$this->putVarInt($entityCount);
					foreach ($this->entityIds as $entityId) {
						if ($playerProtocol >= Info::PROTOCOL_271) {
							$this->putLInt(self::TRACKED_OBJECT_TYPE_ENTITY);
						}
						$this->putSignedVarInt($entityId);
					}
				}
				$this->putVarInt(count($this->pointners));
				foreach ($this->pointners as $pointner) {
					if ($playerProtocol >= Info::PROTOCOL_120) {
						$this->putByte($pointner['type']);
						$this->putByte($pointner['rotate']);
					} else {
						$this->putSignedVarInt($pointner['type'] << 4 | $pointner['rotate']);
					}
					if ($pointner['x'] > 0x7f) {
						$pointner['x'] = 0x7f;
					}
					if ($pointner['x'] < -0x7f) {
						$pointner['x'] = -0x7f;
					}
					if ($pointner['z'] > 0x7f) {
						$pointner['z'] = 0x7f;
					}
					if ($pointner['z'] < -0x7f) {
						$pointner['z'] = -0x7f;
					}
					$this->putByte($pointner['x']);
					$this->putByte($pointner['z']);
					$this->putString('');
					if ($playerProtocol >= Info::PROTOCOL_120) {
						$this->putVarInt(hexdec($pointner['color']));
					} else {
						$this->putLInt(hexdec($pointner['color']));
					}
				}
				break;*/
		}
	}

}
