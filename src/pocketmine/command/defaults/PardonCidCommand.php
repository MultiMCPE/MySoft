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

class PardonCidCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Разбанить CID игрока",
			"/pardoncid §e<игрок>"
		);
		$this->setPermission("pocketmine.command.pardoncid");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) <= 0){
			$sender->sendMessage("§fИспользование: /pardoncid §e<игрок>");
			return true;
		}
		$player = strtolower(array_shift($args));

		if(!$sender->getServer()->banscid->query("SELECT * FROM banscid WHERE name = '$player'")->fetchArray(SQLITE3_ASSOC)){
			$sender->sendMessage("§fИгрок §e{$player} §fне в бане");
			return true;
		}

		$sender->getServer()->banscid->query("DELETE FROM banscid WHERE name = '$player'");
		$senderName = $sender->getName();
		$sender->sendMessage("§bВы §aуспешно разбанили CID игрока §e{$player}");
		$sender->getServer()->getLogger()->notice("CID игрока {$player} был разбанен игроком {$senderName}");
		return false;
}
}
