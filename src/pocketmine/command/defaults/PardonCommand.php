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

class PardonCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.unban.player.description",
			"/pardon §e<игрок>"
		);
		$this->setPermission("pocketmine.command.pardon");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}


		if(count($args) <= 0){
			$sender->sendMessage("§fИспользование: /pardon §e<игрок>");
			return true;
		}
		$player = strtolower(array_shift($args));

		if(!$sender->getServer()->bans->query("SELECT * FROM bans WHERE name = '$player'")->fetchArray(SQLITE3_ASSOC)){
			$sender->sendMessage("§fИгрок §e{$player} §fне в бане");
			return true;
		}

		$sender->getServer()->bans->query("DELETE FROM bans WHERE name = '$player'");
		$senderName = $sender->getName();
		$sender->sendMessage("§bВы §aуспешно разбанили игрока §e{$player}");
		$sender->getServer()->getLogger()->notice("Игрок {$player} был разбанен игроком {$senderName}");
		return false;
}
}
