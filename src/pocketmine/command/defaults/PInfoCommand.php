<?php

/*
██╗██████╗░░█████╗░███╗░░██╗░█████╗░░█████╗░██████╗░███████╗
██║██╔══██╗██╔══██╗████╗░██║██╔══██╗██╔══██╗██╔══██╗██╔════╝
██║██████╔╝██║░░██║██╔██╗██║██║░░╚═╝██║░░██║██████╔╝█████╗░░
██║██╔══██╗██║░░██║██║╚████║██║░░██╗██║░░██║██╔══██╗██╔══╝░░
██║██║░░██║╚█████╔╝██║░╚███║╚█████╔╝╚█████╔╝██║░░██║███████╗
╚═╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚══╝░╚════╝░░╚════╝░╚═╝░░╚═╝╚══════╝
*/

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class PInfoCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Инфо об игроке",
			"/pinfo"
		);
		$this->setPermission("pocketmine.command.pinfo");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if (!isset($args[0])) return $sender->sendMessage("§r§7* §fИспользуйте: §b/pinfo <игрок>§f.");
		$target = $sender->getServer()->getPlayer($args[0]);
		if ($target == NULL) return $sender->sendMessage("§c* §fИгрок §c{$args[0]} §fоффлайн.");
		$tname = $target->getName();
		if ($target->isOp()) return $sender->sendMessage("§r§7* §fВы не можете получить дополнительные данные этого игрока!");
		$sender->sendMessage("§r§7* §fДополнительные данные игрока §4{$tname}§f:\n§7 - §fClientID: §4{$target->getClientId()}§f.\n§7 - §fУстройство: §4{$target->getDeviceModel()}§f.\n§7 - §fIP: §4{$target->getAddress()}§f.");
		//$this->vk->send("[БОТ] Игрок {$name} узнал дополнительную информацию о игроке {$tname}.");
		return true;
	}
}
