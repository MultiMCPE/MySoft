<?php

/*
 _      _          _____    _____    ___  _____
| \    / |  \  /  |        |     |  |       |
|  \  /  |   \/   |_____   |     | _|__     |
|   \/   |   /          |  |     |  |       |
|        |  /     ______|  |_____|  |       |
*/

namespace pocketmine\network\protocol;

class AdventureSettingsPacket extends PEPacket{
	const NETWORK_ID = Info::ADVENTURE_SETTINGS_PACKET;
	const PACKET_NAME = "ADVENTURE_SETTINGS_PACKET";

	const FLAG_WORLD_IMMUTABLE = 0x01;
	const FLAG_NO_PVM = 0x02;
	const FLAG_NO_MVP = 0x04;
	const FLAG_UNUSED = 0x08;
	const FLAG_SHOW_NAME_TAGS = 0x10;
	const FLAG_AUTO_JUMP = 0x20;
	const FLAG_PLAYER_MAY_FLY = 0x40;
	const FLAG_PLAYER_NO_CLIP = 0x80;
	const FLAG_PLAYER_WORLD_BUILDER = 0x0100;
	const FLAG_PLAYER_FLYING = 0x0200;
	const FLAG_PLAYER_MUTED = 0x0400;

	const ACTION_FLAG_PROHIBIT_ALL = 0x00;
	const ACTION_FLAG_MINE = 0x01;
	const ACTION_FLAG_DOORS_AND_SWITCHES = 0x02;
	const ACTION_FLAG_OPEN_CONTAINERS = 0x04;
	const ACTION_FLAG_ATTACK_PLAYERS = 0x08;
	const ACTION_FLAG_ATTACK_MOBS = 0x10;
	const ACTION_FLAG_OP = 0x20;
	const ACTION_FLAG_TELEPORT = 0x40;
	const ACTION_FLAG_DEFAULT_LEVEL_PERMISSIONS = 0x80;
	const ACTION_FLAG_BUILD = 0x0100;
	const ACTION_FLAG_ALLOW_ALL = 0x01FF;
	
	const PERMISSION_LEVEL_VISITOR = 0;
	const PERMISSION_LEVEL_MEMBER = 1;
	const PERMISSION_LEVEL_OPERATOR = 2;
	const PERMISSION_LEVEL_CUSTOM = 3;
	
	const COMMAND_PERMISSION_LEVEL_ANY = 0;
	const COMMAND_PERMISSION_LEVEL_GAME_MASTERS = 1;
	const COMMAND_PERMISSION_LEVEL_ADMIN = 2;
	const COMMAND_PERMISSION_LEVEL_HOST = 3;
	const COMMAND_PERMISSION_LEVEL_OWNER = 4;
	const COMMAND_PERMISSION_LEVEL_INTERNAL = 5;

	/** @deprecated Prohibits only mining */
	const ACTION_FLAG_BUILD_AND_MINE = 0x01;
	
	public $flags = 0;
	public $actionPermissions = self::ACTION_FLAG_DEFAULT_LEVEL_PERMISSIONS;
	public $permissionLevel = self::PERMISSION_LEVEL_MEMBER;
	public $customStoredPermissions = 0;
	public $userId = 0;
	public $commandPermissions = self::COMMAND_PERMISSION_LEVEL_ANY;
	
	public function decode($playerProtocol){
		$this->getHeader($playerProtocol);
        $this->flags = $this->getVarInt();
	}

	public function encode($playerProtocol){
		$this->reset($playerProtocol);
		$this->putVarInt($this->flags);
		$this->putVarInt($this->commandPermissions);
		if($playerProtocol >= Info::PROTOCOL_120){
			$this->putVarInt($this->actionPermissions);
			$this->putVarInt($this->permissionLevel);
			$this->putVarInt($this->customStoredPermissions);
			// we should put eid as long but in signed varint format
			// maybe i'm wrong but it works
			if ($this->userId & 1) { // userId is odd
				$this->putLLong(-1 * (($this->userId + 1) >> 1));
			} else { // userId is even
				$this->putLLong($this->userId >> 1);
			}
		}
	}

}
