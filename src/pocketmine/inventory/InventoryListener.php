<?php

namespace pocketmine\inventory;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\NBT;

class InventoryListener extends PluginBase implements Listener{

  public $tags;
  public $itemName;

  public function __construct(Player $player, $event){
    $this->data($player, $event);
  }

  public function data(Player $player, $event){
    if($event instanceof DataPacketReceiveEvent){
      $packet = $event->getPacket();
      if($player->getProtocol() < 120){
        if($packet instanceof ContainerSetSlotPacket){
          if($packet->windowid == 10){
            $this->itemName = $packet->item->getCustomName();
          }
        }
      }elseif($player->getProtocol() >= 120){
        if($packet->pname() == "INVENTORY_TRANSACTION_PACKET"){
          if($player->getCurrentWindow() instanceof WindowInventory){
            $this->tags = $packet->transactions[0]->oldItem->tags;
            $this->itemName = $this->getCustomName();
          }
        }
      }
    }
  }

  public function getData(){
    return $this->itemName;
  }

  private $cachedNBT = null;
  private static $cachedParser = null;

  public function getCustomName(){

    $tag = $this->getNamedTag();
    if(isset($tag->display)){
      $tag = $tag->display;
      if($tag instanceof Compound and isset($tag->Name) and $tag->Name instanceof StringTag){
        return $tag->Name->getValue();
      }
    }

    return "";
  }

  public function getNamedTag(){
    if($this->cachedNBT !== null){
      return $this->cachedNBT;
    }
    return $this->cachedNBT = self::parseCompound($this->tags);
  }

  private static function parseCompound($tag){
    if(self::$cachedParser === null){
      self::$cachedParser = new NBT(NBT::LITTLE_ENDIAN);
    }

    self::$cachedParser->read($tag);
    return self::$cachedParser->getData();
  }
}

?>
