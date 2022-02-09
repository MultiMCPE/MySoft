<?php

/*
  __  __       ____         __ _
 |  \/  |_   _/ ___|  ___  / _| |_
 | |\/| | | | \___ \ / _ \| |_| __|
 | |  | | |_| |___) | (_) |  _| |_
 |_|  |_|\__, |____/ \___/|_|  \__|
         |___/
*/

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\level\format\mcregion\McRegion;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ChunkInfoCommand extends VanillaCommand {
	/**
	 * ChunkInfoCommand constructor.
	 *
	 * @param $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"Gets the information of a chunk or regenerate a chunk",
			"/chunkinfo (x) (y) (z) (levelName) (regenerate)"
		);
		$this->setPermission("pocketmine.command.chunkinfo");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param array         $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(!$sender instanceof Player and count($args) < 4){
			$sender->sendMessage($sender->getServer()->getLanguage()->translateString("commands.generic.usage", [$this->usageMessage]));
			return true;
		}

		if($sender instanceof Player and count($args) < 4){
			$pos = $sender->getPosition();
		}else{
			$level = $sender->getServer()->getLevelByName($args[3]);
			if(!$level instanceof Level){
				$sender->sendMessage(TextFormat::RED . "Invalid level name");

				return true;
			}
			$pos = new Position((int) $args[0], (int) $args[1], (int) $args[2], $level);
		}

		if(!isset($args[4]) && strtolower($args[0]) !== "regenerate"){
			$chunk = $pos->getLevel()->getChunk($pos->x >> 4, $pos->z >> 4);
			McRegion::getRegionIndex($chunk->getX(), $chunk->getZ(), $x, $z);

			$sender->sendMessage("Region X: $x Region Z: $z");
		} elseif(strtolower($args[0]) == "regenerate" or strtolower($args[4]) == "regenerate"){
	        foreach($sender->getServer()->getOnlinePlayers() as $p){
		        if($p->getLevel() === $pos->getLevel()){
			        $p->close("", TextFormat::AQUA . "A chunk of this level is regenerating, please re-login.");
		       	}
	        }
	        
	        $pos->getLevel()->regenerateChunk($pos->x >> 4, $pos->z >> 4);
		}
		
		return true;
	}
}