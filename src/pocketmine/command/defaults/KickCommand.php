<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class KickCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.kick.description",
			"%commands.kick.usage"
		);
		$this->setPermission("pocketmine.command.kick");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) >= 2){

		$name = array_shift($args);
		$reason = trim(implode(" ", $args));

		if($sender->getServer()->getPlayer($name)){

		if(($player = $sender->getServer()->getPlayer($name)) instanceof Player){
			$sd = $sender->getName();
			$player->kick("§l§aВы были §cкикнуты §aс сервера игроком §e{$sd}\n§l§aПричина: §b{$reason}");
			$sender->sendMessage("Игрок {$player} был кикнут игроком {$sd}, причина: {$reason}");
			$sender->getServer()->getLogger()->notice("Игрок {$name} был кикнуты с сервера игроком {$sd} по причине: §b{$reason}");
		}
	}else{
		$sender->sendMessage("§l§aИгрок §e{$name} §aне найден");
	}
}else{
	$sender->sendMessage("§fИспользование: /kick §e<игрок> §b<причина>");
}
}
}
