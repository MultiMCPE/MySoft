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

class PardonIpCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.unban.ip.description",
			"/pardonip §e<IP>"
		);
		$this->setPermission("pocketmine.command.pardon.ip");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) <= 0){
			$sender->sendMessage("§fИспользование: /pardonip §e<игрок>");
			return true;
		}
		$player = strtolower(array_shift($args));

		if(!$sender->getServer()->bansip->query("SELECT * FROM bansip WHERE name = '$player'")->fetchArray(SQLITE3_ASSOC)){
			$sender->sendMessage("§fИгрок §e{$player} §fне забанен по IP");
			return true;
		}

		$sender->getServer()->bansip->query("DELETE FROM bansip WHERE name = '$player'");
		$sender->sendMessage("§bВы §aуспешно разбанили IP игрока §e{$player}");
		$senderName = $sender->getName();
		$sender->getServer()->getLogger()->notice("IP игрока §e{$player} был разбанен игроком {$senderName}");
		return false;
}
}
