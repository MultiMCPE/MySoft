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
use pocketmine\utils\TextFormat;

class PingListCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Значение пинга у всех игроков",
			"/pinglist"
		);
		$this->setPermission("pocketmine.command.pinglist");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

        if(count($sender->getServer()->getOnlinePlayers()) <= 0){
            $sender->sendMessage("§r§8[§l§eПинг§r§8] §fНа сервере нет игроков.");
            return true;
        }

		if(empty($args[0])) $args[0] = 1;
		if(!is_numeric($args[0])) $args[0] = 1;
		$chunk = array_chunk($sender->getServer()->getOnlinePlayers(), 1);
		if(empty($chunk[$args[0] - 1])) $args[0] = $args[0] > count($chunk) ? count($chunk) : 1;
		
		$sender->sendMessage("§r§8[§l§eПинг§r§8] §fСписок пинга игроков. ".$args[0]." страница из ". count($chunk) .": ");
		foreach($chunk[$args[0] - 1] as $pl){
			$sender->sendMessage("§r§7 - §e{$pl->getName()}§r§f: §a{$pl->getPing()} ms");
		}
		
		return true;
	}
}