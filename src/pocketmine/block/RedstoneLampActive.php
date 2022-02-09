<?php

namespace pocketmine\block;

class RedstoneLampActive extends RedstoneLamp {
    
    public $id = self::REDSTONE_LAMP_ACTIVE;
    
    public function getLightLevel() {
        return 15;
    }
    
}
