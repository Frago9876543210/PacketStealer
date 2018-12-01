<?php

declare(strict_types=1);

namespace Frago9876543210\PacketStealer {

	/** @noinspection PhpIncludeInspection */
	require_once "vendor/autoload.php";

	function addressFromString(string $string) : Address{
		list($host, $port) = explode(":", $string);
		return new Address($host, (int) $port);
	}

	if(count($opts = array_values(getopt("", ["bind:", "server:"]))) !== 2){
		die("Usage: php src/PacketStealer.php --bind x.x.x.x:port --server x.x.x.x:port");
	}
	foreach($opts as $opt){
		if(!strpos($opt, ":")){
			die("Invalid address format!");
		}
	}
	list($bind, $server) = $opts;
	new Proxy(addressFromString($bind), addressFromString($server));
}