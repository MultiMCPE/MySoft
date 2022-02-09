<?php

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\tile\Beacon;
use pocketmine\inventory\InventoryType;

class BeaconInventory extends ContainerInventory {

	public function __construct(Beacon $tile){
		parent::__construct($tile, InventoryType::get(InventoryType::BEACON));
	}

	public function getName() {
		return "Beacon";
	}

	public function getDefaultSize() {
		return 1;
	}

	public function onResult(Player $player, Item $result) {
		return true;
	}

	public function getResultSlot() {
		return 0;
	}

}