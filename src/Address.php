<?php

declare(strict_types=1);


namespace Frago9876543210\PacketStealer;


use raklib\utils\InternetAddress;

class Address extends InternetAddress{

	public function __construct(string $address, int $port, int $version = 4){
		parent::__construct(gethostbyname($address), $port, $version);
	}
}