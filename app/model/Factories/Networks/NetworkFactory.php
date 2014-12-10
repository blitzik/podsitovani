<?php

namespace App\Subnetting\Model\Factories\Networks;

use App\Subnetting\Model,
    App\Subnetting\Exceptions\LogicExceptions;

	class NetworkFactory
	{
		/**
		 *
		 * @param String $ipAddress
		 * @param String|Int $mask
		 * @return Model\Network
		 * @throws LogicExceptions\InvalidIpAddressException
		 * @throws LogicExceptions\InvalidSubnetMaskException
		 */
		public function createNetwork($ipAddress, $mask)
		{
			$ip = new Model\IpAddress($ipAddress);
			$subnetMask = new Model\SubnetMask($mask);

			return new Model\Network($ip, $subnetMask);
		}

	}