<?php

declare(strict_types=1);


namespace Frago9876543210\PacketStealer;


use raklib\protocol\{ACK, AdvertiseSystem, ConnectedPing, ConnectedPong, ConnectionRequest,
	ConnectionRequestAccepted, Datagram, DisconnectionNotification, IncompatibleProtocolVersion,
	NACK, NewIncomingConnection, OpenConnectionReply1, OpenConnectionReply2, OpenConnectionRequest1,
	OpenConnectionRequest2, Packet, UnconnectedPing, UnconnectedPingOpenConnections, UnconnectedPong};

class RakNetPool{
	/** @var Packet[] */
	private static $pool = [];

	public static function init(){
		self::registerPacket(new ACK());
		self::registerPacket(new AdvertiseSystem());
		self::registerPacket(new ConnectedPing());
		self::registerPacket(new ConnectedPong());
		self::registerPacket(new ConnectionRequest());
		self::registerPacket(new ConnectionRequestAccepted());
		self::registerPacket(new DisconnectionNotification());
		self::registerPacket(new IncompatibleProtocolVersion());
		self::registerPacket(new NACK());
		self::registerPacket(new NewIncomingConnection());
		self::registerPacket(new OpenConnectionReply1());
		self::registerPacket(new OpenConnectionReply2());
		self::registerPacket(new OpenConnectionRequest1());
		self::registerPacket(new OpenConnectionRequest2());
		self::registerPacket(new UnconnectedPing());
		self::registerPacket(new UnconnectedPingOpenConnections());
		self::registerPacket(new UnconnectedPong());
	}

	/**
	 * @param Packet $packet
	 */
	public static function registerPacket(Packet $packet) : void{
		self::$pool[$packet::$ID] = clone $packet;
	}

	/**
	 * @param string $buffer
	 * @return null|Packet
	 */
	public static function getPacket(string $buffer) : ?Packet{
		$packet = isset(self::$pool[($pid = ord($buffer{0}))]) ? clone self::$pool[$pid] : new Datagram();
		if(!$packet instanceof Packet || ($packet instanceof Datagram && $pid >= 0x8f)){
			return null;
		}
		$packet->setBuffer($buffer);
		try{
			@$packet->decode();
		}catch(\Throwable $e){
			return null;
		}
		return $packet;
	}
}