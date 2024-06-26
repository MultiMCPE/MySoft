<?php

/*
 _      _          _____    _____    ___  _____
| \    / |  \  /  |        |     |  |       |
|  \  /  |   \/   |_____   |     | _|__     |
|   \/   |   /          |  |     |  |       |
|        |  /     ______|  |_____|  |       |
*/

namespace pocketmine\network\protocol;

use pocketmine\item\Item;
#include <rules/DataPacket.h>

class StartGamePacket extends PEPacket{
	const NETWORK_ID = Info::START_GAME_PACKET;
	const PACKET_NAME = "START_GAME_PACKET";
	
	const BROADCAST_SETTINGS_NO_MULTI_PLAY = 0;
	const BROADCAST_SETTINGS_INVITE_ONLY = 1;
	const BROADCAST_SETTINGS_FRIENDS_ONLY = 2;
	const BROADCAST_SETTINGS_FRIENDS_OF_FRIENDS = 3;
	const BROADCAST_SETTINGS_PUBLIC = 4;

	public $seed;
	public $dimension;
	public $generator = 1;
	public $gamemode;
	public $eid;
	public $spawnX;
	public $spawnY;
	public $spawnZ;
	public $x;
	public $y;
	public $z;	
	public $stringClientVersion;
	
	public static $defaultRules = [
		['name' => 'naturalregeneration', 'type' => 1, 'value' => 0],
		['name' => 'showcoordinates', 'type' => 1, 'value' => 1]
	];
	public $multiplayerCorrelationId;
	public $originalProtocolPlayer = 0;
	
	static public $itemsList = [];
	

	public function decode($playerProtocol){

	}

	public function encode($playerProtocol){
		$this->reset($playerProtocol);
		$this->putVarInt($this->eid); //EntityUniqueID
		$this->putVarInt($this->eid); //EntityUniqueID
 		$this->putSignedVarInt($this->gamemode);	// Entity gamemode
		$this->putLFloat($this->x); // default position (4)
		$this->putLFloat($this->y); // (4)
		$this->putLFloat($this->z); // (4)
		
		$this->putLFloat(0);
		$this->putLFloat(0);
		
		// Level settings
		
		//$this->putSignedVarInt($this->seed);

		if($playerProtocol >= Info::PROTOCOL_503){
			$this->putLLong($this->seed);
		}else{
			$this->putSignedVarInt($this->seed);
		}

		if ($playerProtocol == Info::PROTOCOL_400 || $playerProtocol == Info::PROTOCOL_401) {
			$this->putByte(0);
			$this->putByte(0);
			$this->putString(0);
		}
		if ($playerProtocol >= Info::PROTOCOL_406 && $this->originalProtocolPlayer >= Info::PROTOCOL_406) {
			$this->putShort(0); //SpawnSettingsType			
			$this->putString(''); //User Difined Biome type
		}
		$this->putSignedVarInt($this->dimension);
		
		$this->putSignedVarInt($this->generator);
		
		$this->putSignedVarInt($this->gamemode);
		
		$this->putSignedVarInt(1); // Difficulty
		
		// default spawn 3x VarInt
		$this->putSignedVarInt($this->spawnX);
		$this->putVarInt($this->spawnY);
		$this->putSignedVarInt($this->spawnZ);

		$this->putByte(1); // hasAchievementsDisabled
		
		$this->putSignedVarInt(0); // DayCycleStopTyme 1x VarInt
		if ($playerProtocol == Info::PROTOCOL_400 || $playerProtocol == Info::PROTOCOL_401) {
			$this->putByte(0);
		}
		if ($playerProtocol >= Info::PROTOCOL_406 && $this->originalProtocolPlayer >= Info::PROTOCOL_406) {
			$this->putSignedVarInt(0); //edu edition offer
		}
		$this->putByte(0); //edu mode
		
		if ($playerProtocol < Info::PROTOCOL_415 && $playerProtocol >= Info::PROTOCOL_260 && $this->stringClientVersion != '1.2.20.1') {
			$this->putByte(0); // Are education features enabled?
		}

		if ($playerProtocol >= Info::PROTOCOL_406 && $this->originalProtocolPlayer >= Info::PROTOCOL_406) {
			$this->putString(''); //edu product id
		}
		
		$this->putLFloat(0); //rain level

		$this->putLFloat(0); //lightning level

		if ($playerProtocol >= Info::PROTOCOL_332) {
			$this->putByte(0); // has confirmed platform Locked Content
		}
		
		if ($playerProtocol >= Info::PROTOCOL_120) {
			$this->putByte(1); // is multiplayer game
			$this->putByte(1); // Broadcast to LAN?
			if ($playerProtocol >= Info::PROTOCOL_330) {
				$this->putSignedVarInt(self::BROADCAST_SETTINGS_FRIENDS_OF_FRIENDS); // XBox Live Broadcast setting
			    if ($playerProtocol < Info::PROTOCOL_401 || $playerProtocol >= Info::PROTOCOL_415) {
			    	$this->putSignedVarInt(self::BROADCAST_SETTINGS_FRIENDS_OF_FRIENDS); // Platform Broadcast setting
			    }	
			} else {
				$this->putByte(1); // Broadcast to XBL?
			}
		}
				
		if ($playerProtocol >= Info::PROTOCOL_392 && $playerProtocol < Info::PROTOCOL_400) {
			$this->putByte(0); // unknown
		}
		
		$this->putByte(1);	// commands enabled

		$this->putByte(0); // isTexturepacksRequired 1x Byte		
		if ($playerProtocol >= Info::PROTOCOL_120) {
			$this->putVarInt(count(self::$defaultRules)); // rules count
			foreach (self::$defaultRules as $rule) {
				$this->putString($rule['name']);
		    	if ($playerProtocol >= Info::PROTOCOL_440) {
		    		$this->putVarInt(0);
		    	}
				$this->putVarInt($rule['type']);
				switch ($rule['type']) {
					case 1:
						$this->putByte($rule['value']);
						break;
					case 2:
						$this->putSignedVarInt($rule['value']);
						break;
					case 3:
						$this->putLFloat($rule['value']);
						break;
				}	
			}
			if($playerProtocol >= Info::PROTOCOL_415){
	        	$this->putLInt(0); //Experiments
	        	$this->putBool(0); //hasPreviouslyUsedExperiments
			}
			$this->putByte(0); // is bonus chest enabled
			$this->putByte(0); // is start with map enabled
			if ($playerProtocol < Info::PROTOCOL_330) {
				$this->putByte(0); // has trust players enabled
			}
			$this->putSignedVarInt(0); // permission level
			if ($playerProtocol < Info::PROTOCOL_330) {
				$this->putSignedVarInt(4); // game publish setting
			}
			$this->putLInt(0); // server chunk tick range
			if ($playerProtocol < Info::PROTOCOL_330) {
				$this->putByte(0); // can platform broadcast
				$this->putSignedVarInt(0); // Broadcast mode
				$this->putByte(0); // XBL Broadcast intent
			}
			if ($playerProtocol >= Info::PROTOCOL_260 && $this->stringClientVersion != '1.2.20.1') {
				$this->putByte(0); // Has locked behavior pack?
				$this->putByte(0); // Has locked resource pack?
				$this->putByte(0); // Is from locked template?
				if ($playerProtocol >= Info::PROTOCOL_290) {
					$this->putByte(0); // Use Msa Gamertags Only?
				}
				if ($playerProtocol >= Info::PROTOCOL_311) {
					$this->putByte(0); // Is From World Template?
					$this->putByte(1); // Is World Template Option Locked?
				}
				if ($playerProtocol >= Info::PROTOCOL_361) {
					$this->putByte(0); // Only spawn v1 villagers
				}
				
				if ($playerProtocol >= Info::PROTOCOL_370) {
				    $this->putString(''); // Vanila version
			    }
			    
		    	if ($playerProtocol == Info::PROTOCOL_386) {
			    	$this->putByte(0); // unknown
			    	$this->putByte(1); // unknown
			    	$this->putLFloat(0); // unknown
		    	}
			}
	    	if ($playerProtocol >= Info::PROTOCOL_392) {
			    $this->putLInt(16); //Limited word width
			    $this->putLInt(16); //Limited word depth			
		    }
	    	if ($playerProtocol >= Info::PROTOCOL_400) {
		    	$this->putByte(1); //Nether type
	    	}

	    	if($playerProtocol >= Info::PROTOCOL_455){
		    	$this->putString(""); //Education URI resource -> buttonName
		    	$this->putString(""); //Education URI resource -> link URI
	    	}

	    	if ($playerProtocol >= Info::PROTOCOL_407) {
		    	$this->putByte(0); //exp gameplay
		    }
			// level settings end
			$this->putString('3138ee93-4a4a-479b-8dca-65ca5399e075'); // level id (random UUID)
			$this->putString(''); // level name
			$this->putString(''); // template pack id
			$this->putByte(0); // is trial?
	        if ($playerProtocol >= Info::PROTOCOL_389) {
			    if ($playerProtocol >= Info::PROTOCOL_415) {
			        $this->putVarInt(0);
				    if ($playerProtocol >= Info::PROTOCOL_428) {	
				    	$this->putSignedVarInt(0);
				    	$this->putByte(0);
			    	}
		    	} else {
		    		$this->putByte(0); // is server authoritative over movement
		    	}
	    	}
	    	if($playerProtocol >= Info::PROTOCOL_415 && $playerProtocol < Info::PROTOCOL_418){
		    	$this->put(hex2bin('3613000000000000f8'));
	    	} else {
	        	$this->putLong(0); // current level time
	        	if ($playerProtocol >= Info::PROTOCOL_418) {
		        	$this->putVarInt(0);
	        	}
	        	$this->putSignedVarInt(0); // enchantment seed
	    	}
	    	if ($playerProtocol >= Info::PROTOCOL_280 && $playerProtocol < Info::PROTOCOL_415) {
		    	$this->put(self::getBlockPalletData($playerProtocol));
		    }
			if ($playerProtocol >= Info::PROTOCOL_360) {
		    	if ($playerProtocol >= Info::PROTOCOL_415) {
			    	$itemsData = self::getItemsList();
			    	$this->putVarInt(count($itemsData));
			    	foreach ($itemsData as $name => $id) {
			    		$this->putString($name);
			    		$this->putLShort($id);
			    		if($playerProtocol >= Info::PROTOCOL_418){
					        $this->putByte(0);
			    		}
			    	}			
		    	} else {
			        $this->putVarInt(0); // item list size
		    	}
			}
			if ($playerProtocol >= Info::PROTOCOL_282) {
				$this->putString($this->multiplayerCorrelationId);//multiplayerCollerationId
			}
		    if ($playerProtocol >= Info::PROTOCOL_392) {
		    	$this->putByte(0); // Whether the new item stack net manager is enabled for server authoritative inventory
		    }
		    if ($playerProtocol >= Info::PROTOCOL_440) {
		    	$this->putString(''); //server version
	    	}
	    	if($playerProtocol >= Info::PROTOCOL_475){
		    	$this->putLLong(0); //block palette checksum
	    	}
		}
	}
	static protected function getItemsList() {
		if (!empty(self::$itemsList)) {
			return self::$itemsList;
		} else {
		    $path = __DIR__ . "/data/Items.json";
			self::$itemsList = json_decode(file_get_contents($path), true);
			return self::$itemsList;
		}
	}
}
