<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\JsonNBTParser;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\utils\TextFormat;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\entity\Ageable;
use pocketmine\entity\Animal;
use pocketmine\entity\ArmorStand;
use pocketmine\entity\Arrow;
use pocketmine\entity\Attachable;
use pocketmine\entity\Axolotl;
use pocketmine\entity\BaseEntity;
use pocketmine\entity\Bat;
use pocketmine\entity\Blaze;
use pocketmine\entity\BlazeFireball;
use pocketmine\entity\BlueWitherSkull;
use pocketmine\entity\Boat;
use pocketmine\entity\Boss;
use pocketmine\entity\BottleOEnchanting;
use pocketmine\entity\Camera;
use pocketmine\entity\Car;
use pocketmine\entity\CaveSpider;
use pocketmine\entity\Chalkboard;
use pocketmine\entity\Chicken;
use pocketmine\entity\Colorable;
use pocketmine\entity\Cow;
use pocketmine\entity\Creature;
use pocketmine\entity\Creeper;
use pocketmine\entity\Damageable;
use pocketmine\entity\Donkey;
use pocketmine\entity\Dragon;
use pocketmine\entity\DragonFireBall;
use pocketmine\entity\Effect;
use pocketmine\entity\Egg;
use pocketmine\entity\ElderGuardian;
use pocketmine\entity\EnderCrystal;
use pocketmine\entity\EnderDragon;
use pocketmine\entity\EnderPearl;
use pocketmine\entity\Enderman;
use pocketmine\entity\Endermite;
use pocketmine\entity\EvocationFangs;
use pocketmine\entity\Evoker;
use pocketmine\entity\ExperienceOrb;
use pocketmine\entity\Explosive;
use pocketmine\entity\FallingSand;
use pocketmine\entity\FireBall;
use pocketmine\entity\FireworkRocket;
use pocketmine\entity\FishingHook;
use pocketmine\entity\FloatingText;
use pocketmine\entity\FlyingAnimal;
use pocketmine\entity\FlyingEntity;
use pocketmine\entity\Ghast;
use pocketmine\entity\GhastFireball;
use pocketmine\entity\Giant;
use pocketmine\entity\Guardian;
use pocketmine\entity\Hanging;
use pocketmine\entity\Herobrine;
use pocketmine\entity\Horse;
use pocketmine\entity\Human;
use pocketmine\entity\Husk;
use pocketmine\entity\Illager;
use pocketmine\entity\Illusioner;
use pocketmine\entity\InstantEffect;
use pocketmine\entity\IronGolem;
use pocketmine\entity\Item;
use pocketmine\entity\JumpingEntity;
use pocketmine\entity\Koni;
use pocketmine\entity\LavaSlime;
use pocketmine\entity\LearnToCodeMascot;
use pocketmine\entity\LeashKnot;
use pocketmine\entity\Lightning;
use pocketmine\entity\Living;
use pocketmine\entity\Llama;
use pocketmine\entity\MagmaCube;
use pocketmine\entity\Minecart;
use pocketmine\entity\MinecartChest;
use pocketmine\entity\MinecartCommandBlock;
use pocketmine\entity\MinecartHopper;
use pocketmine\entity\MinecartTNT;
use pocketmine\entity\Monster;
use pocketmine\entity\Mooshroom;
use pocketmine\entity\Mule;
use pocketmine\entity\NPC;
use pocketmine\entity\NPCHuman;
use pocketmine\entity\Ocelot;
use pocketmine\entity\Painting;
use pocketmine\entity\Parrot;
use pocketmine\entity\Pig;
use pocketmine\entity\PigZombie;
use pocketmine\entity\PolarBear;
use pocketmine\entity\PrimedTNT;
use pocketmine\entity\Projectile;
use pocketmine\entity\ProjectileSource;
use pocketmine\entity\Rabbit;
use pocketmine\entity\RideEntity;
use pocketmine\entity\Rideable;
use pocketmine\entity\Sheep;
use pocketmine\entity\Shulker;
use pocketmine\entity\ShulkerBullet;
use pocketmine\entity\Silverfish;
use pocketmine\entity\Skeleton;
use pocketmine\entity\SkeletonHorse;
use pocketmine\entity\Skin;
use pocketmine\entity\Slime;
use pocketmine\entity\SnowGolem;
use pocketmine\entity\Snowball;
use pocketmine\entity\Spider;
use pocketmine\entity\SplashPotion;
use pocketmine\entity\Squid;
use pocketmine\entity\Stray;
use pocketmine\entity\Tameable;
use pocketmine\entity\Vehicle;
use pocketmine\entity\Vex;
use pocketmine\entity\Villager;
use pocketmine\entity\Vindicator;
use pocketmine\entity\WalkingEntity;
use pocketmine\entity\WaterAnimal;
use pocketmine\entity\Witch;
use pocketmine\entity\Wither;
use pocketmine\entity\WitherSkeleton;
use pocketmine\entity\Wolf;
use pocketmine\entity\XPOrb;
use pocketmine\entity\Zombie;
use pocketmine\entity\ZombieHorse;
use pocketmine\entity\ZombieVillager;

class SummonCommand extends VanillaCommand {

	/**
	 * SummonCommand constructor.
	 *
	 * @param $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.summon.description",
			"%commands.summon.usage"
		);
		$this->setPermission("pocketmine.command.summon");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $currentAlias
	 * @param array         $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) != 1 and count($args) != 4 and count($args) != 5){
			$sender->sendMessage($sender->getServer()->getLanguage()->translateString("commands.generic.usage", [$this->usageMessage]));
			return true;
		}

		$x = 0;
		$y = 0;
		$z = 0;
		if(count($args) == 4 or count($args) == 5){            //position is set
			//TODO:simpilify them to one piece of code
			//Code for setting $x
			if(is_numeric($args[1])){                            //x is given directly
				$x = $args[1];
			}elseif(strcmp($args[1], "~") >= 0){    //x is given with a "~"
				$offset_x = trim($args[1], "~");
				if($sender instanceof Player){            //using in-game
					$x = is_numeric($offset_x) ? ($sender->x + $offset_x) : $sender->x;
				}else{                                                            //using in console
					$sender->sendMessage(TextFormat::RED . "You must specify a position where the entity is spawned to when using in console");
					return false;
				}
			}else{                                                                //other circumstances
				$sender->sendMessage(TextFormat::RED . "Argument error");
				return false;
			}

			//Code for setting $y
			if(is_numeric($args[2])){                            //y is given directly
				$y = $args[2];
			}elseif(strcmp($args[2], "~") >= 0){    //y is given with a "~"
				$offset_y = trim($args[2], "~");
				if($sender instanceof Player){            //using in-game
					$y = is_numeric($offset_y) ? ($sender->y + $offset_y) : $sender->y;
					$y = min(128, max(0, $y));
				}else{                                                            //using in console
					$sender->sendMessage(TextFormat::RED . "You must specify a position where the entity is spawned to when using in console");
					return false;
				}
			}else{                                                                //other circumstances
				$sender->sendMessage(TextFormat::RED . "Argument error");
				return false;
			}

			//Code for setting $z
			if(is_numeric($args[3])){                            //z is given directly
				$z = $args[3];
			}elseif(strcmp($args[3], "~") >= 0){    //z is given with a "~"
				$offset_z = trim($args[3], "~");
				if($sender instanceof Player){            //using in-game
					$z = is_numeric($offset_z) ? ($sender->z + $offset_z) : $sender->z;
				}else{                                                            //using in console
					$sender->sendMessage(TextFormat::RED . "You must specify a position where the entity is spawned to when using in console");
					return false;
				}
			}else{                                                                //other circumstances
				$sender->sendMessage(TextFormat::RED . "Argument error");
				return false;
			}
		}    //finish setting the location

		if(count($args) == 1){
			if($sender instanceof Player){
				$x = (int) $sender->x;
				$y = (int) $sender->y;
				$z = (int) $sender->z;
			}else{
				$sender->sendMessage(TextFormat::RED . "You must specify a position where the entity is spawned to when using in console");
				return false;
			}
		} //finish setting the location

		$level = ($sender instanceof Player) ? $sender->getLevel() : $sender->getServer()->getDefaultLevel();
		$nbt = new Compound("", [
			"Pos" => new Enum("Pos", [
				new DoubleTag("", $x),
				new DoubleTag("", $y),
				new DoubleTag("", $z)
			]),
			"Motion" => new Enum("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new Enum("Rotation", [
				new FloatTag("", lcg_value() * 360),
				new FloatTag("", 0)
			]),
		]);

		$class = "pocketmine\\entity\\" . ucfirst(strtolower($args[0]));
		$entity = Entity::createEntity($class::NETWORK_ID, $level->getChunk($x >> 4, $z >> 4), $nbt);
		if($entity instanceof Entity){
			$entity->spawnToAll();
			$sender->sendMessage("Призвано существо " . ucfirst(strtolower($args[0])) . " на ($x, $y, $z)");
			return true;
		}else{
			$sender->sendMessage(TextFormat::RED . "Не удалось призвать $type");
			return false;
		}
	}
}
