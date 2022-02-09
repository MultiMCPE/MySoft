<?php

/**
 *
 *  ____       _                          _
 * |  _ \ _ __(_)___ _ __ ___   __ _ _ __(_)_ __   ___
 * | |_) | '__| / __| '_ ` _ \ / _` | '__| | '_ \ / _ \
 * |  __/| |  | \__ \ | | | | | (_| | |  | | | | |  __/
 * |_|   |_|  |_|___/_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * Prismarine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Prismarine Team
 * @link   https://github.com/PrismarineMC/Prismarine
 *
 *
 */

namespace pocketmine\inventory;

use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Enum;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\protocol\TileEntityDataPacket;
use pocketmine\network\protocol\ContainerClosePacket;
use pocketmine\network\protocol\Info;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;

class WindowInventory extends CustomInventory{

    protected $customName = "";

    public function __construct(Player $player, string $customName = "") {
        $this->customName = $customName;
        $holder = new WindowHolder($player->getFloorX(), $player->getFloorY() - 3, $player->getFloorZ(), $this);
        parent::__construct($player, InventoryType::get(InventoryType::CHEST));
    }

    public function onOpen(Player $who){
        $this->holder = $holder = new WindowHolder($who->getFloorX(), $who->getFloorY() - 3, $who->getFloorZ(), $this);
        $pk = new UpdateBlockPacket();
        $pk->records[] = [$holder->x, $holder->z, $holder->y, Block::CHEST, 0, UpdateBlockPacket::FLAG_ALL];
        $who->dataPacket($pk);

        $c = new Compound("", [
            new StringTag("id", Tile::CHEST),
            new IntTag("x", (int) $holder->x),
            new IntTag("y", (int) $holder->y),
            new IntTag("z", (int) $holder->z)
        ]);
        if($this->customName !== ""){
            $c->CustomName = new StringTag("CustomName", TextFormat::RESET.$this->customName);
        }
        $nbt = new NBT(NBT::LITTLE_ENDIAN);
        $nbt->setData($c);
        if($who->getProtocol() >= Info::PROTOCOL_120){
          //$nbt->Items->setTagType(NBT::TAG_Compound);
          Tile::createTile("Chest", $who->getLevel()->getChunk($who->x >> 4, $who->z >> 4), $c);
        }else{
          $pk = new TileEntityDataPacket();
          $pk->x = $holder->x;
          $pk->y = $holder->y;
          $pk->z = $holder->z;
          $pk->namedtag = $nbt->write(true);
          $who->dataPacket($pk);
        }
        parent::onOpen($who);
        $this->sendContents($who);
    }

    public function onClose(Player $who){
        $holder = $this->holder;
        $chunk = $who->getLevel()->getChunk(($holder->x >> 4), ($holder->z >> 4));
        $v3 = new Vector3($holder->x, $holder->y, $holder->z);
        $tile = $who->getLevel()->getTile($v3);
        if($tile instanceof Tile){
          $chunk->removeTile($tile);
        }
        if($who->getProtocol() >= Info::PROTOCOL_120){
          $pk = new ContainerClosePacket();
          $Pk->windowid = 10;
          $who->dataPacket($pk);
        }
        $pk = new UpdateBlockPacket();
        $pk->records[] = [$holder->x, $holder->z, $holder->y, $who->getLevel()->getBlockIdAt($holder->x, $holder->y, $holder->z), $who->getLevel()->getBlockDataAt($holder->x, $holder->y, $holder->z), UpdateBlockPacket::FLAG_ALL];
        $who->dataPacket($pk);
    }

    public function getVector(Player $player){
      return new Vector3($player->x, $player->y, $player->z);
    }
}
