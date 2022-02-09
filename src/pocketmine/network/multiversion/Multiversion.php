<?php

namespace pocketmine\network\multiversion;

use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\PlayerInventory120;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\network\protocol\v120\InventoryContentPacket;
use pocketmine\network\protocol\v120\InventorySlotPacket;
use pocketmine\Player;

abstract class Multiversion {
	
	/**
	 * 
	 * Create player inventory object base on player protocol
	 * 
	 * @param Player $player
	 * @return PlayerInventory
	 */
	public static function getPlayerInventory(Player $player) {
		$inventoryClass = PlayerInventory::class;
		if($player->getProtocol() >= ProtocolInfo::PROTOCOL_120){
			$inventoryClass = PlayerInventory120::class;
		}
		
		return new $inventoryClass($player);
	}
	
	/**
	 * Send all container's content
	 * 
	 * @param Player $player
	 * @param integer $windowId
	 * @param Item[] $items
	 */
	public static function sendContainer(Player $player, $windowId, $items) {
		$protocol = $player->getPlayerProtocol();
		if ($protocol >= ProtocolInfo::PROTOCOL_120) {
			$pk = new InventoryContentPacket();
			$pk->inventoryID = $windowId;
			$pk->items = $items;
		} else {
			$pk = new ContainerSetContentPacket();			
			$pk->windowid = $windowId;
			$pk->slots = $items;
			$pk->eid = $player->getId();
		}
		$player->dataPacket($pk);
	}
	
	/**
	 * Send one container's slot
	 * 
	 * @param Player $player
	 * @param integer $windowId
	 * @param Item $item
	 * @param integer $slot
	 */
	public static function sendContainerSlot(Player $player, $windowId, $item, $slot) {
	    //раньше было дополнено, удалил, т.к было нерабочее дерьмо)
		$protocol = $player->getPlayerProtocol();
		if ($protocol >= ProtocolInfo::PROTOCOL_120) {
			$pk = new InventorySlotPacket();
			$pk->containerId = $windowId;
			$pk->item = $item;
			$pk->slot = $slot;
		} else {
			$pk = new ContainerSetSlotPacket();			
			$pk->windowid = $windowId;
			$pk->item = $item;
			$pk->slot = $slot;
		}
		$player->dataPacket($pk);
	}
	
}
