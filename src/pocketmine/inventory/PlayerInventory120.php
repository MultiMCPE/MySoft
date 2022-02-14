<?php

namespace pocketmine\inventory;

use pocketmine\entity\Human;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\network\protocol\MobArmorEquipmentPacket;
use pocketmine\network\protocol\MobEquipmentPacket;
use pocketmine\network\protocol\v120\InventoryContentPacket;
use pocketmine\network\protocol\v120\InventorySlotPacket;
use pocketmine\network\protocol\v120\Protocol120;
use pocketmine\Player;
use pocketmine\network\protocol\{Info, ContainerClosePacket, ContainerOpenPacket};
use pocketmine\Server;

class PlayerInventory120 extends PlayerInventory {

	const CURSOR_INDEX = -1;
	const CREATIVE_INDEX = -2;
	const CRAFT_INDEX_0 = -3;
	const CRAFT_INDEX_1 = -4;
	const CRAFT_INDEX_2 = -5;
	const CRAFT_INDEX_3 = -6;
	const CRAFT_INDEX_4 = -7;
	const CRAFT_INDEX_5 = -8;
	const CRAFT_INDEX_6 = -9;
	const CRAFT_INDEX_7 = -10;
	const CRAFT_INDEX_8 = -11;
	const CRAFT_RESULT_INDEX = -12;
	const QUICK_CRAFT_INDEX_OFFSET = -100;

	/** @var Item */
	protected $cursor;
	/** @var Item[] */
	protected $craftSlots = [];
	/** @var Item */
	protected $craftResult = null;
	/** @var Item[] */
	protected $quickCraftSlots = []; // reason: bug with quick craft
	/** @var boolean */
	protected $isQuickCraftEnabled = false;

	public function __construct(Human $player) {
		parent::__construct($player);
		$this->cursor = Item::get(Item::AIR, 0, 0);
		for ($i = 0; $i < 9; $i++) {
			$this->craftSlots[$i] = Item::get(Item::AIR, 0, 0);
		}
	}

	public function getItemInOffHand(){
	    return $this->getItem($this->getSize() + 4);
	}

	public function setItemInOffHand(Item $item){
	    return $this->setItem($this->getSize() + 4, $item);
	}

	public function setItem($index, Item $item, $sendPacket = true) {
		if ($index >= 0) {
			return parent::setItem($index, $item, $sendPacket);
		}
		// protocol 120 logic
		switch ($index) {
			case self::CURSOR_INDEX:
				$this->cursor = clone $item;
				if ($sendPacket === true) {
					$this->sendCursor();
				}
				break;
			case self::CRAFT_INDEX_0:
			case self::CRAFT_INDEX_1:
			case self::CRAFT_INDEX_2:
			case self::CRAFT_INDEX_3:
			case self::CRAFT_INDEX_4:
			case self::CRAFT_INDEX_5:
			case self::CRAFT_INDEX_6:
			case self::CRAFT_INDEX_7:
			case self::CRAFT_INDEX_8:
				$slot = self::CRAFT_INDEX_0 - $index;
				$this->craftSlots[$slot] = clone $item;
				break;
			case self::CRAFT_RESULT_INDEX:
				$this->craftResult = clone $item;
				break;
			default:
				if ($index <= self::QUICK_CRAFT_INDEX_OFFSET) {
					$slot = self::QUICK_CRAFT_INDEX_OFFSET - $index;
					$this->quickCraftSlots[$slot] = clone $item;
				}
				break;
		}
		$this->sendContents($this->getHolder());
		$this->sendArmorContents($this->getHolder());
		return true;
	}

	public function getItem($index) {
		if ($index < 0) {
			switch ($index) {
				case self::CURSOR_INDEX:
					return $this->cursor == null ? clone $this->air : clone $this->cursor;
				case self::CRAFT_INDEX_0:
				case self::CRAFT_INDEX_1:
				case self::CRAFT_INDEX_2:
				case self::CRAFT_INDEX_3:
				case self::CRAFT_INDEX_4:
				case self::CRAFT_INDEX_5:
				case self::CRAFT_INDEX_6:
				case self::CRAFT_INDEX_7:
				case self::CRAFT_INDEX_8:
					$slot = self::CRAFT_INDEX_0 - $index;
					return $this->craftSlots[$slot] == null ? clone $this->air : clone $this->craftSlots[$slot];
				case self::CRAFT_RESULT_INDEX:
					return $this->craftResult == null ? clone $this->air : clone $this->craftResult;
				default:
					if ($index <= self::QUICK_CRAFT_INDEX_OFFSET) {
						$slot = self::QUICK_CRAFT_INDEX_OFFSET - $index;
						return !isset($this->quickCraftSlots[$slot]) || $this->quickCraftSlots[$slot] == null ? clone $this->air : clone $this->quickCraftSlots[$slot];
					}
					break;
			}
			return clone $this->air;
		} else {
			return parent::getItem($index);
		}
	}

	public function setHotbarSlotIndex($index, $slot) {
		if ($index == $slot || $slot < 0) {
			return;
		}
		$tmp = $this->getItem($index);
		$this->setItem($index, $this->getItem($slot));
		$this->setItem($slot, $tmp);
	}

	public function sendSlot($index, $target) {
		$pk = new InventorySlotPacket();
		$pk->containerId = Protocol120::CONTAINER_ID_INVENTORY;
		$pk->slot = $index;
		$pk->item = $this->getItem($index);
		$this->holder->dataPacket($pk);
	}

	public function sendContents($target) {
		$pk = new InventoryContentPacket();
		$pk->inventoryID = Protocol120::CONTAINER_ID_INVENTORY;
		$pk->items = [];

		$mainPartSize = $this->getSize();
		for ($i = 0; $i < $mainPartSize; $i++) { //Do not send armor by error here
			$pk->items[$i] = $this->getItem($i);
		}

		$this->holder->dataPacket($pk);
		$this->sendCursor();
	}

	public function sendCursor() {
		$pk = new InventorySlotPacket();
		$pk->containerId = Protocol120::CONTAINER_ID_CURSOR_SELECTED;
		$pk->slot = 0;
		$pk->item = $this->cursor;
		$this->holder->dataPacket($pk);
	}

	/**
	 *
	 * @param integer $index
	 * @param Player[] $target
	 */
	public function sendArmorSlot($index, $target){
		if ($target instanceof Player) {
			$target = [$target];
		}

		if ($index - $this->getSize() == self::OFFHAND_ARMOR_SLOT_ID) {
			$this->sendOffHandContents($target);
		} else {
			$armor = $this->getArmorContents();

			$pk = new MobArmorEquipmentPacket();
			$pk->eid = $this->holder->getId();
			$pk->slots = $armor;

			foreach($target as $player){
				if ($player === $this->holder) {
					/** @var Player $player */
					$pk2 = new InventorySlotPacket();
					$pk2->containerId = Protocol120::CONTAINER_ID_ARMOR;
					$pk2->slot = $index - $this->getSize();
					$pk2->item = $this->getItem($index);
					$player->dataPacket($pk2);
				} else {
					$player->dataPacket($pk);
				}
			}
		}
	}

	public function sendArmorContents($target) {
		if ($target instanceof Player) {
			$target = [$target];
		}

		$armor = $this->getArmorContents();

		$pk = new MobArmorEquipmentPacket();
		$pk->eid = $this->holder->getId();
		$pk->slots = $armor;

		foreach ($target as $player) {
			if ($player === $this->holder) {
				$pk2 = new InventoryContentPacket();
				$pk2->inventoryID = Protocol120::CONTAINER_ID_ARMOR;
				$pk2->items = $armor;
				$player->dataPacket($pk2);
			} else {
				$player->dataPacket($pk);
			}
		}
		$this->sendOffHandContents($target);
	}

	/**
	 *
	 * @param Player[] $target
	 */
	private function sendOffHandContents($targets) {
		$pk = new MobEquipmentPacket();
		$pk->eid = $this->getHolder()->getId();
		$pk->item = $this->getItem($this->getSize() + self::OFFHAND_ARMOR_SLOT_ID);
		$pk->slot = $this->getHeldItemSlot();
		$pk->selectedSlot = $this->getHeldItemIndex();
		$pk->windowId = MobEquipmentPacket::WINDOW_ID_PLAYER_OFFHAND;
		foreach ($targets as $player) {
			if ($player === $this->getHolder()) {
				$pk2 = new InventoryContentPacket();
				$pk2->inventoryID = Protocol120::CONTAINER_ID_OFFHAND;
				$pk2->items = [$this->getItem($this->getSize() + self::OFFHAND_ARMOR_SLOT_ID)];
				$player->dataPacket($pk2);
			} else {
				$player->dataPacket($pk);
			}
		}
	}

	/**
	 *
	 * @return Item[]
	 */
	public function getCraftContents() {
		return $this->craftSlots;
	}

	/**
	 *
	 * @param integer $slotIndex
	 * @return boolean
	 */
	protected function isArmorSlot($slotIndex) {
		return $slotIndex >= $this->getSize();
	}

	/**
	 *
	 * @param integer $slotIndex
	 * @return boolean
	 */
	public function clear($slotIndex) {
		if (isset($this->slots[$slotIndex])) {
			if ($this->isArmorSlot($slotIndex)) { //Armor change
				$ev = new EntityArmorChangeEvent($this->holder, $this->slots[$slotIndex], clone $this->air, $slotIndex);
				Server::getInstance()->getPluginManager()->callEvent($ev);
				if ($ev->isCancelled()) {
					$this->sendArmorSlot($slotIndex, $this->holder);
					return false;
				}
			} else {
				$ev = new EntityInventoryChangeEvent($this->holder, $this->slots[$slotIndex], clone $this->air, $slotIndex);
				Server::getInstance()->getPluginManager()->callEvent($ev);
				if ($ev->isCancelled()) {
					$this->sendSlot($slotIndex, $this->holder);
					return false;
				}
			}
			$oldItem = $this->slots[$slotIndex];
			$newItem = $ev->getNewItem();
			if ($newItem->getId() !== Item::AIR) {
				$this->slots[$slotIndex] = clone $newItem;
			} else {
				unset($this->slots[$slotIndex]);
			}
			$this->onSlotChange($slotIndex, $oldItem);
		}
		return true;
	}

	public function close(Player $who) {
		parent::close($who);
		$isChanged = false;
		foreach ($this->craftSlots as $index => $slot) {
			if ($slot->getId() != Item::AIR) {
				$this->addItem($slot);
				$this->craftSlots[$index] = Item::get(Item::AIR, 0, 0);
				$isChanged = true;
			}
		}
		foreach ($this->quickCraftSlots as $slot) {
			if ($slot->getId() != Item::AIR) {
				$this->addItem($slot);
				$isChanged = true;
			}
		}
		$this->setQuickCraftMode(false);
		if ($isChanged) {
			$this->sendContents($this->holder);
		}
	}

	public function clearAll() {
		parent::clearAll();
		for ($index = self::CRAFT_INDEX_0; $index >= self::CRAFT_INDEX_8; $index--) {
			$this->setItem($index, clone $this->air);
		}
		$this->cursor = null;
	}

	public function __toString() {
		$result = "";
		foreach ($this->getContents() as $index => $item) {
			$result .= $index . " - " . $item . PHP_EOL;
		}
		return $result;
	}

	public function setQuickCraftMode($value) {
		$this->isQuickCraftEnabled = $value;
		$this->quickCraftSlots = [];
	}

	public function isQuickCraftEnabled() {
		return $this->isQuickCraftEnabled;
	}

	public function getNextFreeQuickCraftSlot() {
		return self::QUICK_CRAFT_INDEX_OFFSET - count($this->quickCraftSlots);
	}

	public function getQuckCraftContents() {
		return $this->quickCraftSlots;
	}
	public function onOpen(Player $who){
		$holder = $this->getHolder();
		if ($who === $holder) {
			parent::onOpen($who);
			if ($who->getPlayerProtocol() >= Info::PROTOCOL_392 && $who->craftingType <= 0) {
				$pk = new ContainerOpenPacket();
				$pk->windowid = $who->getWindowId($this);
				$pk->type = -1;
				$pk->x = $holder->x;
				$pk->y = $holder->y;
				$pk->z = $holder->z;
				$who->dataPacket($pk);
			}
		}
	}

	public function onClose(Player $who){
		$holder = $this->getHolder();
		if ($who === $holder) {
			parent::onClose($who);
			if ($who->getPlayerProtocol() >= Info::PROTOCOL_392) {
				$pk = new ContainerClosePacket();
				$pk->windowid = $who->getWindowId($this);
				$who->dataPacket($pk);
			}
		}
	}
}
