<?php

#    ___                                          _
#   /   | ____ ___  ______ _____ ___  ____ ______(_)___  ___
#  / /| |/ __ `/ / / / __ `/ __ `__ \/ __ `/ ___/ / __ \/ _ \
# / ___ / /_/ / /_/ / /_/ / / / / / / /_/ / /  / / / / /  __/
#/_/  |_\__, /\__,_/\__,_/_/ /_/ /_/\__,_/_/  /_/_/ /_/\___/
#         /_/


declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;

class GetDeviceCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"",
			"/devicemodel или /devicemodel <игрок>"
		);
		$this->setPermission("pocketmine.command.getdevice");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		if(count($args) == 0){
			$device = $sender->getDeviceModel();
			$sender->sendMessage("§aВаше устройство: §b{$device}");
			return false;
		}else{
				if($sender->getServer()->getPlayer($args[0])){
					$player = $sender->getServer()->getPlayer($args[0]);
					$device = $player->getDeviceModel();
					$sender->sendMessage("§aУстройство игрока {$args[0]}: §b{$device}");
					return false;
				}else{
					$sender->sendMessage("§cИгрок §e{$args[0]} §cне найден");
					return true;
				}
		}
	}
}
