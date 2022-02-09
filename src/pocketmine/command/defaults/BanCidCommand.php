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

class BanCidCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.ban.player.description",
			"/bancid §b<игрок> §e<время в часах> §a<причина>"
		);
		$this->setPermission("pocketmine.command.ban.cid");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		if(count($args) >= 3){
					$player = array_shift($args);
					$time = array_shift($args);
					$reason = implode(" ", $args);
					if(trim($player) === "" or !is_numeric($time)){
						$sender->sendMessage("§fИспользование: /bancid §b<игрок> §e<время в часах> §a<причина>");
						return true;
					}

					$secAfter = $time*3600;

					$due = $secAfter + time();
					$senderName = $sender->getName();
					$name = strtolower($player);
					$cid = $sender->getServer()->getPlayer($player)->getClientId();
					$sender->getServer()->banscid->query("INSERT INTO banscid(name, cid, due, bannedby, reason) VALUES ('$name', '$cid', '$due', '$senderName', '$reason');");

					$sender->sendMessage("Игрок {$player} был забанен игроком {$senderName} по CID, на {$time} часов, причина: {$reason}");
					$sender->getServer()->getLogger()->notice("Игрок {$player} был забанен игроком {$senderName}, на {$time} часов, причина: {$reason}");

					if(($player = $sender->getServer()->getPlayer($player)) instanceof Player){
						$player->kick("§l§aВы были §cзабанены §aна §bсервере по CID\n§l§aЗабанил: §d{$senderName}\n§l§cБан §aвыдан на: §e{$time} §bчасов, §aпричина: §b{$reason}");
					}
					return false;
				}else{
					$sender->sendMessage("§fИспользование: /bancid §b<игрок> §e<время в часах> §a<причина>");
					return true;
				}
	}
}
