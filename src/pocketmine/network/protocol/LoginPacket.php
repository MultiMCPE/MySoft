<?php

namespace pocketmine\network\protocol;

use pocketmine\utils\{BinaryStream, UUID, Binary, Utils};

class LoginPacket extends PEPacket {

	const NETWORK_ID = Info::LOGIN_PACKET;
	const PACKET_NAME = "LOGIN_PACKET";

	public $username; // Player name
	
	public $protocol1; // Protocol
	public $originalProtocol; // Protocol (original)
	public $isValidProtocol = true; // valid protocol
	
	public $clientSecret; // Client id
	public $clientId; // Client id
	public $clientUUID; // UUID
	
	public $serverAddress; // Server address && port
	
	public $skin = ""; // skinData
	public $skinName; // SkinName or SkinId
	public $skinGeometryName = ""; // geometryName
	public $skinGeometryData = ""; // geometryData
	public $capeData = ""; // capeData
	public $premiumSkin = ""; // premiumSkin
	public $additionalSkinData = []; // additionalSkinData
	
	public $inventoryType;
	public $xuid;
	public $languageCode;
	public $clientVersion;
	public $identityPublicKey;
	public $platformChatId;
	
	public $osType; // DeviceOS
	public $deviceModel; // DeviceModel
	
	public $chainData = []; // clientData
	public $clientDataJwt; // clientData
	public $clientData = []; // clientData

	public function decode($playerProtocol) {
		$acceptedProtocols = Info::ACCEPTED_PROTOCOLS;
		
		// header: protocolID, Subclient Sender, Subclient Receiver
		$this->getVarInt(); // header: 1 byte for protocol < 280, 1-2 for 280
		$tmpData = Binary::readInt(substr($this->getBuffer(), $this->getOffset(), 4));
		if ($tmpData == 0) {
			$this->getShort();
		}
		
		$this->protocol1 = $this->getInt();
		if (!in_array($this->protocol1, $acceptedProtocols)) {
			$this->isValidProtocol = false;
			return;
		}
		
		if ($this->protocol1 < Info::PROTOCOL_120) {
			$this->getByte();
		}
		
		$buffer = new BinaryStream($this->getString());
		$this->chainData = json_decode($buffer->get($buffer->getLInt()), true);

		$hasExtraData = false;
		
		foreach($this->chainData["chain"] as $chain){
			$webtoken = Utils::decodeJWT($chain);
			if(isset($webtoken["extraData"])){
				if($hasExtraData){
					throw new \Exception("Found 'extraData' multiple times in key chain");
				}
				$hasExtraData = true;
				if(isset($webtoken["extraData"]["displayName"])){
					$this->username = $webtoken["extraData"]["displayName"];
				}
				if(isset($webtoken["extraData"]["identity"])){
			    	$this->clientUUID = UUID::fromString($webtoken["extraData"]["identity"]);
				}
				$this->xuid = $webtoken["extraData"]["XUID"] ?? '';
			}
			$this->identityPublicKey = $webtoken["identityPublicKey"] ?? "";
		}
		
		$this->clientDataJwt = $buffer->get($buffer->getLInt());
		$this->clientData = Utils::decodeJWT($this->clientDataJwt);

		$this->clientId = $this->clientData["ClientRandomId"] ?? null;
		$this->clientSecret = $this->clientId;
		$this->serverAddress = $this->clientData["ServerAddress"] ?? null;
		
		$this->skinName = $this->clientData['SkinId'] ?? "CustomID";
		$this->skin = base64_decode($this->clientData['SkinData']);
		$this->skinGeometryName = $this->clientData['SkinGeometryName'] ?? "";
		if (isset($this->clientData['SkinGeometry'])) {
			$this->skinGeometryData = base64_decode($this->clientData['SkinGeometry']);
		} elseif (isset($this->clientData['SkinGeometryData'])) {
			$this->skinGeometryData = base64_decode($this->clientData['SkinGeometryData']);
			if (strpos($this->skinGeometryData, 'null') === 0) {
				$this->skinGeometryData = '';
			}
		}
		if (isset($this->clientData['CapeData'])) {
			$this->capeData = base64_decode($this->clientData['CapeData']);
		}
		$this->premiumSkin = $this->clientData["PremiumSkin"] ?? "";
		
        $this->osType = $this->clientData['DeviceOS'] ?? -1;
        $this->deviceModel = $this->clientData["DeviceModel"] ?? "";
		$this->inventoryType = $this->clientData['UIProfile'] ?? -1;
		$this->languageCode = $this->clientData['LanguageCode'] ?? "unknown";
		$this->clientVersion = $this->clientData['GameVersion'] ?? "unknown";
        $this->platformChatId = $this->clientData["PlatformOnlineId"] ?? "";
        
		$this->originalProtocol = $this->protocol1;
		$this->protocol1 = self::convertProtocol($this->protocol1);
		
		$additionalSkinDataList = [
			'PlayFabId','AnimatedImageData', 'CapeId', 'CapeImageHeight', 'CapeImageWidth', 'CapeOnClassicSkin', 'PersonaSkin', 'PremiumSkin', 'SkinAnimationData', 'SkinImageHeight', 'SkinImageWidth', 'SkinResourcePatch'	
		];
		$additionalSkinData = [];
		foreach ($additionalSkinDataList as $propertyName) {
			if (isset($this->clientData[$propertyName])) {
				$additionalSkinData[$propertyName] = $this->clientData[$propertyName];
			}
		}
		if (isset($additionalSkinData['AnimatedImageData'])) {
			foreach ($additionalSkinData['AnimatedImageData'] as &$animation) {
				$animation['Image'] = base64_decode($animation['Image']);
			}
		}
		if (isset($additionalSkinData['SkinResourcePatch'])) {
			$additionalSkinData['SkinResourcePatch'] = base64_decode($additionalSkinData['SkinResourcePatch']);
		}
		if (isset($this->clientData["PersonaPieces"])) {
			$additionalSkinData['PersonaPieces'] = $this->clientData["PersonaPieces"];
		}
		if (isset($this->clientData["ArmSize"])) {
			$additionalSkinData['ArmSize'] = $this->clientData["ArmSize"];
		}
		if (isset($this->clientData["SkinColor"])) {
			$additionalSkinData['SkinColor'] = $this->clientData["SkinColor"];
		}
		if (isset($this->clientData["PieceTintColors"])) {
			$additionalSkinData['PieceTintColors'] = $this->clientData["PieceTintColors"];
		}
		$this->additionalSkinData = $additionalSkinData;
		$this->checkSkinData($this->skin, $this->skinGeometryName, $this->skinGeometryData, $this->additionalSkinData);
	}
	
	public function encode($playerProtocol) {}
}