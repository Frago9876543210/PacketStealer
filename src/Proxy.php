<?php

declare(strict_types=1);


namespace Frago9876543210\PacketStealer;


use pocketmine\network\mcpe\NetworkCompression;
use pocketmine\network\mcpe\PacketStream;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use raklib\protocol\{Packet, Datagram, EncapsulatedPacket, UnconnectedPing, UnconnectedPong, OpenConnectionRequest1};
use raklib\server\UDPServerSocket;

class Proxy extends UDPServerSocket{
	/** @var Address */
	private $serverAddress;
	/** @var Address */
	private $clientAddress;
	/** @var bool */
	private $sessionCreated = false;
	/** @var Datagram[][] */
	private $splitPackets = [];

	public function __construct(Address $bindAddress, Address $serverAddress){
		RakNetPool::init();
		PacketPool::init();

		parent::__construct($bindAddress);
		$this->serverAddress = $serverAddress;

		while(true){
			$this->tick();
		}
	}

	public function tick() : void{
		if($this->readPacket($buffer, $source, $port) !== false){
			if(($packet = RakNetPool::getPacket($buffer)) !== null){
				$this->handlePacket($packet, new Address($source, $port));
			}
		}
	}

	private function handlePacket(Packet $packet, Address $address) : void{
		if(!$this->sessionCreated){
			if($packet instanceof UnconnectedPing){
				$this->clientAddress = $address;
				$this->sendToServer($packet);
			}elseif($packet instanceof UnconnectedPong){
				$this->sendToClient($packet);
			}elseif($packet instanceof OpenConnectionRequest1){
				$this->clientAddress = $address;
				$this->sessionCreated = true;
				$this->sendToServer($packet);
			}
		}else{
			if($packet instanceof Datagram){
				$this->handleDatagram($packet, $this->serverAddress->equals($address));
			}

			if($this->serverAddress->equals($address)){
				$this->sendToClient($packet);
			}else{
				$this->sendToServer($packet);
			}
		}
	}

	private function handleDatagram(Datagram $datagram, bool $serverSide) : void{
		foreach($datagram->packets as $pk){
			$this->handleEncapsulatedPacket($pk, $serverSide);
		}
	}

	private function handleEncapsulatedPacket(EncapsulatedPacket $packet, bool &$serverSide) : void{
		if($packet->hasSplit && ($packet = $this->handleSplit($packet)) === null){
			return;
		}
		if($packet->buffer !== "" && $packet->buffer{0} === "\xfe"){
			$this->handleBatch($packet->buffer, $serverSide);
		}
	}

	private function handleSplit(EncapsulatedPacket $packet) : ?EncapsulatedPacket{
		if($packet->splitCount >= 128 or $packet->splitIndex >= 128 or $packet->splitIndex < 0){
			return null;
		}

		if(!isset($this->splitPackets[$packet->splitID])){
			if(count($this->splitPackets) >= 4){
				return null;
			}
			$this->splitPackets[$packet->splitID] = [$packet->splitIndex => $packet];
		}else{
			$this->splitPackets[$packet->splitID][$packet->splitIndex] = $packet;
		}

		if(count($this->splitPackets[$packet->splitID]) === $packet->splitCount){
			$pk = new EncapsulatedPacket();
			$pk->buffer = "";

			$pk->reliability = $packet->reliability;
			$pk->messageIndex = $packet->messageIndex;
			$pk->sequenceIndex = $packet->sequenceIndex;
			$pk->orderIndex = $packet->orderIndex;
			$pk->orderChannel = $packet->orderChannel;

			for($i = 0; $i < $packet->splitCount; ++$i){
				$pk->buffer .= $this->splitPackets[$packet->splitID][$i]->buffer;
			}

			$pk->length = strlen($pk->buffer);
			unset($this->splitPackets[$packet->splitID]);

			return $pk;
		}

		return null;
	}

	private function handleBatch(string $payload, bool &$serverSide) : void{
		try{
			$stream = new PacketStream(NetworkCompression::decompress(substr($payload, 1)));
		}catch(\Exception $e){
			return;
		}
		while(!$stream->feof()){
			$this->handleDataPacket(PacketPool::getPacket($stream->getString()), $serverSide);
		}
	}

	private function getClassName(object $class) : ?string{
		try{
			return (new \ReflectionClass($class))->getShortName();
		}catch(\ReflectionException $e){
			return null;
		}
	}

	private function handleDataPacket(DataPacket $packet, bool &$serverSide) : void{
		echo $this->getClassName($packet) . PHP_EOL;
	}

	private function sendToServer(Packet $packet) : void{
		$this->writePacket($packet->buffer, $this->serverAddress->ip, $this->serverAddress->port);
	}

	private function sendToClient(Packet $packet) : void{
		$this->writePacket($packet->buffer, $this->clientAddress->ip, $this->clientAddress->port);
	}
}