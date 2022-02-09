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

class GetOSCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Узнать с какой OS играет игрок",
			"/getos или /getos <игрок>"
		);
		$this->setPermission("pocketmine.command.getos");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		if(count($args) == 0){
			$os = $sender->getOs();
			$sender->sendMessage("§aВаша os: §b{$os}");
			return false;
		}else{
				if($sender->getServer()->getPlayer($args[0])){
					$player = $sender->getServer()->getPlayer($args[0]);
					$os = $player->getOs();
					$sender->sendMessage("§aOs игрока {$args[0]}: §b{$os}");
					return false;
				}else{
					$sender->sendMessage("§cИгрок §e{$args[0]} §cне найден");
					return true;
				}
		}
	}
}
