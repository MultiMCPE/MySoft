<?php

namespace pocketmine\block;

class StickyPiston extends Piston {
	
	public $id = self::STICKY_PISTON;
	
	public function __construct($meta = 0) {
		parent::__construct($meta);
	}
	
	public function retract($tile, $extendSide, $deep) {
		$tile->namedtag['Progress'] = 0;
		$tile->namedtag['State'] = 0;
		$extendBlock = $this->getSide($extendSide);
		$movingBlock = $extendBlock->getSide($extendSide);
		if (!is_null($oldTile  = $this->level->getTile($movingBlock))) {
			$oldTile->updatePosition($extendBlock->x, $extendBlock->y, $extendBlock->z);
		}	
		if ($movingBlock instanceof Solid) {
			$this->getLevel()->setBlock($movingBlock, Block::get(self::AIR), true, true, $deep);
			$this->getLevel()->setBlock($extendBlock, $movingBlock, true, true, $deep);
		} else {
			$this->getLevel()->setBlock($extendBlock, Block::get(self::AIR), true, true, $deep);
		}
		$tile->spawnToAll();
	}
}
