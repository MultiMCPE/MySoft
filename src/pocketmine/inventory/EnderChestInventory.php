<?php
namespace pocketmine\inventory;
use pocketmine\entity\Human;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\Enum;
use pocketmine\network\protocol\TileEventPacket;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\nbt\NBT;
class EnderChestInventory extends ContainerInventory {
	private $owner;
	public function __construct(Human $owner, $contents = null){
		$this->owner = $owner;
		$pos = new Position($owner->getX(), $owner->getY(), $owner->getZ(), $owner->getLevel());
		parent::__construct(new FakeBlockMenu($this, $pos), InventoryType::get(InventoryType::ENDER_CHEST));
		if($contents !== null){
			if($contents instanceof Enum){ //Saved data to be loaded into the inventory
				foreach($contents as $item){
					$this->setItem($item["Slot"], NBT::getItemHelper($item));
				}
			}else{
				throw new \InvalidArgumentException("Expecting Enum, received " . gettype($contents));
			}
		}
	}
	public function getOwner(){
		return $this->owner;
	}
	public function openAt(Position $pos){
		$this->getHolder()->setComponents($pos->x, $pos->y, $pos->z);
		$this->getHolder()->setLevel($pos->getLevel());
		$this->owner->addWindow($this);
	}
	public function getHolder(){
		return $this->holder;
	}
	public function onOpen(Player $who){
		parent::onOpen($who);
		if(count($this->getViewers()) === 1){
			$pk = new TileEventPacket();
			$pk->x = $this->getHolder()->getX();
			$pk->y = $this->getHolder()->getY();
			$pk->z = $this->getHolder()->getZ();
			$pk->case1 = 1;
			$pk->case2 = 2;
			if(($level = $this->getHolder()->getLevel()) instanceof Level){
				Server::broadcastPacket($level->getUsingChunk($this->getHolder()->getX() >> 4, $this->getHolder()->getZ() >> 4), $pk);
			}
		}
	}
	public function onClose(Player $who){
		if(count($this->getViewers()) === 1){
			$pk = new TileEventPacket();
			$pk->x = $this->getHolder()->getX();
			$pk->y = $this->getHolder()->getY();
			$pk->z = $this->getHolder()->getZ();
			$pk->case1 = 1;
			$pk->case2 = 0;
			if(($level = $this->getHolder()->getLevel()) instanceof Level){
				Server::broadcastPacket($level->getUsingChunk($this->getHolder()->getX() >> 4, $this->getHolder()->getZ() >> 4), $pk);
			}
		}
		parent::onClose($who);
	}
}