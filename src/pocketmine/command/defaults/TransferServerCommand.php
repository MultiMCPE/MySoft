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
use pocketmine\Player;
use pocketmine\Server;

class TransferServerCommand extends VanillaCommand {

	/**
	 * TransferServerCommand constructor.
	 *
	 * @param $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"pocketmine.command.transfer.description",
			"/transferserver <player> <address> [port]",
		);
		$this->setPermission("pocketmine.command.transfer");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $currentAlias
	 * @param array         $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		$address = null;
		$port = null;
		$player = null;
		if($sender instanceof Player){
			if(!$this->testPermission($sender)){
				return true;
			}

			if(count($args) <= 0){
				$sender->sendMessage("Usage: /transferserver <address> [port]");
				return false;
			}

			$address = strtolower($args[0]);
			$port = (isset($args[1]) && is_numeric($args[1]) ? $args[1] : 19132);

            $sender->transfer($address, $port);

			return false;
		}

		if(count($args) <= 1){
			$sender->sendMessage("Usage: /transferserver <player> <address> [port]");
			return false;
		}

		if(!($player = Server::getInstance()->getPlayer($args[0])) instanceof Player){
			$sender->sendMessage("Player specified not found!");
			return false;
		}

		$address = strtolower($args[1]);
		$port = (isset($args[2]) && is_numeric($args[2]) ? $args[2] : 19132);

		$sender->sendMessage("Sending " . $player->getName() . " to " . $address . ":" . $port);

		$player->transfer($address, $port);
	}
}