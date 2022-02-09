<?php

/*
  __  __       ____         __ _
 |  \/  |_   _/ ___|  ___  / _| |_
 | |\/| | | | \___ \ / _ \| |_| __|
 | |  | | |_| |___) | (_) |  _| |_
 |_|  |_|\__, |____/ \___/|_|  \__|
         |___/
 */

namespace pocketmine\level\particle;

use pocketmine\math\Vector3;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\Server;

class SpellParticle extends GenericParticle {
	/**
	 * SpellParticle constructor.
	 *
	 * @param Vector3 $pos
	 * @param int     $r
	 * @param int     $g
	 * @param int     $b
	 * @param int     $a
	 */
	public function __construct(Vector3 $pos, $r = 0, $g = 0, $b = 0, $a = 255){
		parent::__construct($pos, LevelEventPacket::EVENT_PARTICLE_SPLASH, (($a & 0xff) << 24) | (($r & 0xff) << 16) | (($g & 0xff) << 8) | ($b & 0xff));
	}
	
	public function spawnFor($players, $dimension = 0) {
	    $pk = new LevelEventPacket();
	    $pk->evid = LevelEventPacket::EVENT_PARTICLE_SPLASH;
		$pk->x = $this->x;
		$pk->y = $this->y;
	    $pk->z = $this->z;
		$pk->data = $this->data;
		
		Server::broadcastPackets($players, $pk);
	}
}