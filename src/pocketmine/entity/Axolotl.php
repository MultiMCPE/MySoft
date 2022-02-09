<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\entity;

use pocketmine\block\StainedGlass;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\Level;
use pocketmine\nbt\tag\Compound;
use pocketmine\level\format\FullChunk;

class Axolotl extends Animal implements Colorable
{
    const NETWORK_ID = 114;

    const DATA_COLOR_INFO = 16;

    public $width = 0.625;
    public $length = 1.4375;
    public $height = 1.8;

    public function getName()
    {
        return "Axolotl";
    }

    public function __construct(FullChunk $level, Compound $nbt)
    {
        if(!isset($nbt->Color)){
            $nbt->Color = new ByteTag("Color", self::getRandomColor());
        }
        
        parent::__construct($level, $nbt);

        $this->setDataProperty(self::DATA_COLOR_INFO, self::DATA_TYPE_BYTE, $this->getColor());
    }

    public static function getRandomColor()
    {
        $rand = "";
        $rand .= str_repeat(StainedGlass::WHITE . " ", 20);
        $rand .= str_repeat(StainedGlass::ORANGE . " ", 5);
        $rand .= str_repeat(StainedGlass::MAGENTA . " ", 5);
        $rand .= str_repeat(StainedGlass::LIGHT_BLUE . " ", 5);
        $rand .= str_repeat(StainedGlass::YELLOW . " ", 5);
        $rand .= str_repeat(StainedGlass::GRAY . " ", 10);
        $rand .= str_repeat(StainedGlass::LIGHT_GRAY . " ", 10);
        $rand .= str_repeat(StainedGlass::CYAN . " ", 5);
        $rand .= str_repeat(StainedGlass::PURPLE . " ", 5);
        $rand .= str_repeat(StainedGlass::BLUE . " ", 5);
        $rand .= str_repeat(StainedGlass::BROWN . " ", 5);
        $rand .= str_repeat(StainedGlass::GREEN . " ", 5);
        $rand .= str_repeat(StainedGlass::RED . " ", 5);
        $rand .= str_repeat(StainedGlass::BLACK . " ", 10);
        $arr = explode(" ", $rand);
        return intval($arr[mt_rand(0, count($arr) - 1)]);
    }

    public function getColor()
    {
        return (int)$this->namedtag["Color"];
    }

    public function setColor(int $color)
    {
        $this->namedtag->Color = new ByteTag("Color", $color);
    }

    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = Axolotl::NETWORK_ID;
        $pk->x = $this->x;
        $pk->y = $this->y;
        $pk->z = $this->z;
        $pk->speedX = $this->motionX;
        $pk->speedY = $this->motionY;
        $pk->speedZ = $this->motionZ;
        $pk->yaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }

    public function getDrops()
    {
        $drops = [
            ItemItem::get(ItemItem::RAW_FISH, 0, mt_rand(0, 2))
        ];
        return $drops;
    }
}
