<?php

/*
 _      _          _____    _____    ___  _____
| \    / |  \  /  |        |     |  |       |
|  \  /  |   \/   |_____   |     | _|__     |
|   \/   |   /          |  |     |  |       |
|        |  /     ______|  |_____|  |       |
*/

namespace pocketmine\network\protocol;

#include <rules/DataPacket.h>


class EntityEventPacket extends PEPacket{
	const NETWORK_ID = Info::ENTITY_EVENT_PACKET;
	const PACKET_NAME = "ENTITY_EVENT_PACKET";

	const HURT_ANIMATION = 2;
	const DEATH_ANIMATION = 3;
	const START_ATACKING = 4;

	const TAME_FAIL = 6;
	const TAME_SUCCESS = 7;
	const SHAKE_WET = 8;
	const USE_ITEM = 9;
	const EAT_GRASS_ANIMATION = 10;
	const FISH_HOOK_BUBBLE = 11;
	const FISH_HOOK_POSITION = 12;
	const FISH_HOOK_HOOK = 13;
	const FISH_HOOK_TEASE = 14;
	const SQUID_INK_CLOUD = 15;
	const ZOMBIE_VILLAGER_CURE = 16;
	const AMBIENT_SOUND = 17;
	const RESPAWN = 18;
	const ENCHANT = 34;
	const ARROW_SHAKE = 39;
	const IRON_GOLEM_OFFER_FLOWER = 19;
	const IRON_GOLEM_WITHDRAW_FLOWER = 20;
	const LOVE_PARTICLES = 21; //breeding
	const VILLAGER_ANGRY = 22;
	const VILLAGER_HAPPY = 23;
	const WITCH_SPELL_PARTICLES = 24;
	const FIREWORK_PARTICLES = 25; // 1.2 (By ssss1)
	const IN_LOVE_PARTICLES = 26;
	const SILVERFISH_SPAWN_ANIMATION = 27;
	const GUARDIAN_ATTACK = 28;
	const WITCH_DRINK_POTION = 29;
	const WITCH_THROW_POTION = 30;
	const MINECART_TNT_PRIME_FUSE = 31;
	const CREEPER_PRIME_FUSE = 32;
	const AIR_SUPPLY_EXPIRED = 33;
	const PLAYER_ADD_XP_LEVELS = 34;
	const ELDER_GUARDIAN_CURSE = 35;
	const AGENT_ARM_SWING = 36;
	const ENDER_DRAGON_DEATH = 37;
	const DUST_PARTICLES = 38; //not sure what this is

	const FEED = 57;

	const BABY_ANIMAL_FEED = 60; //green particles, like bonemeal on crops
	const DEATH_SMOKE_CLOUD = 61;
	const COMPLETE_TRADE = 62;
	const REMOVE_LEASH = 63; //data 1 = cut leash

	const CONSUME_TOTEM = 65;
	const PLAYER_CHECK_TREASURE_HUNTER_ACHIEVEMENT = 66; //mojang...
	const ENTITY_SPAWN = 67; //used for MinecraftEventing stuff, not needed
	const DRAGON_PUKE = 68; //they call this puke particles
	const ITEM_ENTITY_MERGE = 69;
	const START_SWIM = 70;
	const BALLOON_POP = 71;
	const TREASURE_HUNT = 72;
	const AGENT_SUMMON = 73;
	const CHARGED_CROSSBOW = 74;
	const FALL = 75;

	//TODO add new events

	public $eid;
	public $event;
	public $theThing = 0;

	public function decode($playerProtocol){
		$this->getHeader($playerProtocol);
		$this->eid = $this->getVarInt();
		$this->event = $this->getByte();
		$this->theThing = $this->getVarInt();
	}

	public function encode($playerProtocol){
		$this->reset($playerProtocol);
		$this->putVarInt($this->eid);
		$this->putByte($this->event);
		/** @todo do it right */
		$this->putVarInt($this->theThing); // event data
	}

}
