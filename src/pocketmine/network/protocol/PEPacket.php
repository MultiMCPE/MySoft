<?php
/*
 _      _          _____    _____    ___  _____
| \    / |  \  /  |        |     |  |       |
|  \  /  |   \/   |_____   |     | _|__     |
|   \/   |   /          |  |     |  |       |
|        |  /     ______|  |_____|  |       |
*/
namespace pocketmine\network\protocol;

use pocketmine\item\Item;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\multiversion\BlockPallet;

abstract class PEPacket extends DataPacket {

	const CLIENT_ID_MAIN_PLAYER = 0;
	const CLIENT_ID_SERVER = 0;

	public $senderSubClientID = self::CLIENT_ID_SERVER;

	public $targetSubClientID = self::CLIENT_ID_MAIN_PLAYER;

	abstract public function encode($playerProtocol);

	abstract public function decode($playerProtocol);

	/**
	 * !IMPORTANT! Should be called at first line in decode
	 * @param integer $playerProtocol
	 */
	protected function getHeader($playerProtocol = 0) {
		if ($playerProtocol >= Info::PROTOCOL_280) {
			$header = $this->getSignedVarInt();
			$subclientIds = $header >> 10;
			$this->senderSubClientID = $subclientIds & 0x03;
			$this->targetSubClientID = ($subclientIds >> 2) & 0x03;
		} else if ($playerProtocol >= Info::PROTOCOL_120) {
			$this->getByte(); // packetID
			$this->senderSubClientID = $this->getByte();
			$this->targetSubClientID = $this->getByte();
			if ($this->senderSubClientID > 4 || $this->targetSubClientID > 4) {
				throw new \Exception(get_class($this) . ": Packet decode headers error");
			}
		} else {
			$this->getByte(); // packetID
		}
	}

	/**
	 * !IMPORTANT! Should be called at first line in encode
	 * @param integer $playerProtocol
	 */
	public function reset($playerProtocol = 0) {
		if ($playerProtocol < Info::PROTOCOL_280) {
			parent::reset();
			$this->putByte(self::$packetsIds[$playerProtocol][$this::PACKET_NAME]);
			if ($playerProtocol >= Info::PROTOCOL_120) {
				$this->putByte($this->senderSubClientID);
				$this->putByte($this->targetSubClientID);
			}
		} else {
			parent::reset();
			$packetID = self::$packetsIds[$playerProtocol][$this::PACKET_NAME];
			$header = ($this->targetSubClientID << 12) | ($this->senderSubClientID << 10) | $packetID;
			$this->putVarInt($header);
		}
	}

	public final static function convertProtocol($protocol) {
		switch ($protocol) {
			case Info::PROTOCOL_486:
			case Info::PROTOCOL_485:
			case Info::PROTOCOL_479:
			case Info::PROTOCOL_477:
			case Info::PROTOCOL_476:
			case Info::PROTOCOL_475:
			case Info::PROTOCOL_474:
				return Info::PROTOCOL_475;
			case Info::PROTOCOL_471:
			    return Info::PROTOCOL_471;
			case Info::PROTOCOL_465:
			    return Info::PROTOCOL_465;
			case Info::PROTOCOL_464:
			case Info::PROTOCOL_462:
			case Info::PROTOCOL_459:
			    return Info::PROTOCOL_459;
			case Info::PROTOCOL_456:
			case Info::PROTOCOL_455:
			    return Info::PROTOCOL_455;
			case Info::PROTOCOL_453:
			case Info::PROTOCOL_448:
			    return Info::PROTOCOL_448;
			case Info::PROTOCOL_441:
			case Info::PROTOCOL_440:
			case Info::PROTOCOL_437:
			case Info::PROTOCOL_435:
			case Info::PROTOCOL_434:
			case Info::PROTOCOL_433:
			    return Info::PROTOCOL_440;
			case Info::PROTOCOL_431:
			case Info::PROTOCOL_430:
			case Info::PROTOCOL_429:
			    return Info::PROTOCOL_431;
			case Info::PROTOCOL_428:
			    return Info::PROTOCOL_428;
			case Info::PROTOCOL_427:
			case Info::PROTOCOL_425:
			    return Info::PROTOCOL_425;
			case Info::PROTOCOL_424:
			case Info::PROTOCOL_423:
			case Info::PROTOCOL_422:
			    return Info::PROTOCOL_422;
			case Info::PROTOCOL_421:
			case Info::PROTOCOL_420:
			case Info::PROTOCOL_419:
				return Info::PROTOCOL_419;
			case Info::PROTOCOL_418:
				return Info::PROTOCOL_418;
			case Info::PROTOCOL_417:
			case Info::PROTOCOL_416:
			case Info::PROTOCOL_415:
				return Info::PROTOCOL_415;
			case Info::PROTOCOL_414:
			case Info::PROTOCOL_413:
			case Info::PROTOCOL_412:
			case Info::PROTOCOL_411:
			case Info::PROTOCOL_410:
			case Info::PROTOCOL_409:
			case Info::PROTOCOL_408:
			case Info::PROTOCOL_407:
				return Info::PROTOCOL_407;
			case Info::PROTOCOL_406:
			case Info::PROTOCOL_405:
			case Info::PROTOCOL_404:
			case Info::PROTOCOL_403:
			case Info::PROTOCOL_402:
			case Info::PROTOCOL_401:
				return Info::PROTOCOL_406;
				//return Info::PROTOCOL_401;
			case Info::PROTOCOL_400:
				return Info::PROTOCOL_400;
		    case Info::PROTOCOL_396:
		    case Info::PROTOCOL_395:
		    case Info::PROTOCOL_394:
			case Info::PROTOCOL_393:
				return Info::PROTOCOL_393;
			case Info::PROTOCOL_392:
				return Info::PROTOCOL_392;
			case Info::PROTOCOL_391:
			case Info::PROTOCOL_390:
			    return Info::PROTOCOL_390;
			case Info::PROTOCOL_389:
			case Info::PROTOCOL_388:
				return Info::PROTOCOL_389;
			case Info::PROTOCOL_387:
			case Info::PROTOCOL_386:
				return Info::PROTOCOL_386;
			case Info::PROTOCOL_385:
				return Info::PROTOCOL_385;
		    case Info::PROTOCOL_371:
			case Info::PROTOCOL_370:
				return Info::PROTOCOL_370;
			case Info::PROTOCOL_361:
				return Info::PROTOCOL_361;
			case Info::PROTOCOL_360:
				return Info::PROTOCOL_360;
			case Info::PROTOCOL_354:
				return Info::PROTOCOL_354;
			case Info::PROTOCOL_353:
			case Info::PROTOCOL_352:
			case Info::PROTOCOL_351:
				return Info::PROTOCOL_351;
			case Info::PROTOCOL_350:
				return Info::PROTOCOL_350;
			case Info::PROTOCOL_342:
				return Info::PROTOCOL_342;
			case Info::PROTOCOL_340:
				return Info::PROTOCOL_340;
			case Info::PROTOCOL_332:
				return Info::PROTOCOL_332;
			case Info::PROTOCOL_331:
				return Info::PROTOCOL_331;
			case Info::PROTOCOL_330:
				return Info::PROTOCOL_330;
			case Info::PROTOCOL_311:
			case Info::PROTOCOL_312:
			case Info::PROTOCOL_313:
				return Info::PROTOCOL_311;
			case Info::PROTOCOL_310:
				return Info::PROTOCOL_310;
			case Info::PROTOCOL_290:
			case Info::PROTOCOL_291:
				return Info::PROTOCOL_290;
			case Info::PROTOCOL_282:
				return Info::PROTOCOL_282;
			case Info::PROTOCOL_281:
			case Info::PROTOCOL_280:
				return Info::PROTOCOL_280;
			case Info::PROTOCOL_274:
				return Info::PROTOCOL_274;
			case Info::PROTOCOL_273:
				return Info::PROTOCOL_273;
			case Info::PROTOCOL_271:
				return Info::PROTOCOL_271;
			case Info::PROTOCOL_260:
			case Info::PROTOCOL_261:
			case Info::PROTOCOL_270:
				return Info::PROTOCOL_260;
			case Info::PROTOCOL_240:
			case Info::PROTOCOL_250:
				return Info::PROTOCOL_240;
			case Info::PROTOCOL_221:
			case Info::PROTOCOL_222:
			case Info::PROTOCOL_223:
			case Info::PROTOCOL_224:
				return Info::PROTOCOL_221;
			case Info::PROTOCOL_220:
				return Info::PROTOCOL_220;
			case Info::PROTOCOL_200:
				return Info::PROTOCOL_200;
			case Info::PROTOCOL_134:
			case Info::PROTOCOL_135:
			case Info::PROTOCOL_136:
			case Info::PROTOCOL_137:
			case Info::PROTOCOL_140:
			case Info::PROTOCOL_141:
			case Info::PROTOCOL_150:
			case Info::PROTOCOL_160:
			case Info::PROTOCOL_201:
				return Info::PROTOCOL_120;
			default:
				return Info::PROTOCOL_110;
		}
	}

	/** @var BlockPallet[] */
	private static $blockPalletes = [];

	public static function initPallet() {
		self::$blockPalletes = BlockPallet::initAll();
	}

	public static function getBlockIDByRuntime($runtimeId, $playerProtocol) {
		$pallet = self::getPallet($playerProtocol);
		return is_null($pallet) ? [ 0, 0, "" ] : $pallet->getBlockDataByRuntimeID($runtimeId);
	}

	public static function getBlockRuntimeID($id, $meta, $playerProtocol) {
		$pallet = self::getPallet($playerProtocol);
		if ($playerProtocol >= Info::PROTOCOL_419) {
			$meta = self::getActualMeta($id, $meta);
		}
		return is_null($pallet) ? 0 : $pallet->getBlockRuntimeIDByData($id, $meta);
	}
	private static function getActualMeta($id, $meta) {
		if ($id == Item::ITEM_FRAME_BLOCK) {
			$array = [3 => 8, 4 => 5, 5 => 4];
			return $array[$meta]??$meta;
		}
		if ($id == Item::LEAVE2 && $meta > 7) {
			return 7;
		}
		return $meta;
	}
	public static function getBlockPalletData($playerProtocol) {
		$pallet = self::getPallet($playerProtocol);
		return is_null($pallet) ? "" : $pallet->getDataForPackets();
	}

	/**
	 *
	 * @param type $playerProtocol
	 * @return BlockPallet
	 */
	public static function getPallet($playerProtocol) {
		foreach (self::$blockPalletes as $protocol => $pallet) {
			if ($playerProtocol >= $protocol) {
				return $pallet;
			}
		}
		return null;
	}

}
