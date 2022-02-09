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

class BanIpCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.ban.ip.description",
			"/banip §b<игрок> §e<время в часах> §a<причина>"
		);
		$this->setPermission("pocketmine.command.ban.ip");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		if(count($args) < 3){
			$sender->sendMessage("§fИспользование: /banip §b<игрок> §e<время в часах> §a<причина>");
			return true;
		}
		if(count($args) >= 3){

			$ip = array_shift($args);
			$time = array_shift($args);
			$reason = implode(" ", $args);
			if(trim($ip) === "" or !is_numeric($time)){
				$sender->sendMessage("§fИспользование: /banip §b<игрок> §e<время в часах> §a<причина>");
				return true;
			}

			if($sender->getServer()->getPlayer($ip)){
			$secAfter = $time*3600;
			$player = $sender->getServer()->getPlayer($ip);
				if($player instanceof Player){
					$ip = $player->getAddress();
					$senderName = $sender->getName();
					$player->kick("§l§aВы были §cзабанены §aна §bсервере по §cIP\n§l§aЗабанил: §d{$senderName}\n§l§cБан §aвыдан на: §e{$time} §bчасов, §aпричина: §b{$reason}");
				}

			$due = $secAfter + time();
			$senderName = $sender->getName();
			$name = strtolower($player->getName());
			$sender->getServer()->bansip->query("INSERT INTO bansip(name, ip, due, bannedby, reason) VALUES ('$name', '$ip', '$due', '$senderName', '$reason');");

			$sender->sendMessage("IP {$ip} был забанен игроком {$senderName}, на {$time} часов, причина: {$reason}");
			$sender->getServer()->getLogger()->notice("IP {$ip} был забанен игроком {$senderName}, на {$time} часов, причина: {$reason}");
			return false;
		}else{
			$sender->sendMessage("§fИгрок §e{$ip} §fне онлайн");
			return true;
		}
		}
	}

	/*private function processIPBan($ip, CommandSender $sender, $reason){
		$sender->getServer()->getIPBans()->addBan($ip, $reason, null, $sender->getName());

		foreach($sender->getServer()->getOnlinePlayers() as $player){
			if($player->getAddress() === $ip){
				$player->kick($reason !== "" ? "Вы забенены по IP причина: {$reason}" : "Этот IP забанен");
			}
		}

		$sender->getServer()->getNetwork()->blockAddress($ip, -1);
	}*/
}
