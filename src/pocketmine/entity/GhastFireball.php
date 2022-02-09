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

use pocketmine\level\Level;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\nbt\tag\Compound;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\level\Explosion;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\level\format\FullChunk;

class GhastFireball extends Projectile
{
    const NETWORK_ID = 85;

    public $width = 1.0;
    public $height = 1.0;
    
    protected $damage = 4;
    protected $drag = 0.01;
    protected $gravity = 0.05;
    protected $isCritical;
    protected $canExplode = false;

    public function __construct(FullChunk $level, Compound $nbt, Entity $shootingEntity = null, bool $critical = false)
    {
        parent::__construct($level, $nbt, $shootingEntity);
        
        $this->isCritical = $critical;
    }

    public function isExplode()
    {
        return $this->canExplode;
    }

    public function setExplode($bool)
    {
        $this->canExplode = $bool;
    }

    public function onUpdate($currentTick)
    {
        if ($this->closed) {
            return false;
        }

        $this->timings->startTiming();
        $hasUpdate = parent::onUpdate($currentTick);
        if (!$this->hadCollision and $this->isCritical) {
            /*$this->level->addParticle(new CriticalParticle($this->add(
                $this->width / 2 + mt_rand(-100, 100) / 500,
                $this->height / 2 + mt_rand(-100, 100) / 500,
                $this->width / 2 + mt_rand(-100, 100) / 500)));*/
        } elseif ($this->onGround) {
            $this->isCritical = false;
        }

        if ($this->age > 1200 or $this->isCollided) {
            if ($this->isCollided and $this->canExplode) {
                $this->server->getPluginManager()->callEvent($ev = new ExplosionPrimeEvent($this, 2.8));
                if (!$ev->isCancelled()) {
                    $explosion = new Explosion($this, $ev->getForce(), $this->shootingEntity);
                    if ($ev->isBlockBreaking()) {
                        $explosion->explodeB();
                    }
                    $explosion->explodeB();
                }
            }
            $this->kill();
            $hasUpdate = true;
        }
        $this->timings->stopTiming();
        return $hasUpdate;
    }

    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->eid = $this->getId();
        $pk->type = GhastFireball::NETWORK_ID;
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
}
