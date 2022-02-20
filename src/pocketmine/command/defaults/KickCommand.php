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
