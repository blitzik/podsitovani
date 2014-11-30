<?php

namespace Model;

	class NetworkFactory
	{
		/**
		 *
		 * @param String $ipAddress
		 * @param String|Int $mask
		 * @return \Model\Network
		 * @throws \LogicExceptions\InvalidIpAddressException
		 * @throws \LogicExceptions\InvalidSubnetMaskException
		 */
		public function createNetwork($ipAddress, $mask)
		{
			$ip = new IpAddress($ipAddress);
			$subnetMask = new SubnetMask($mask);

			return new Network($ip, $subnetMask);
		}

	}