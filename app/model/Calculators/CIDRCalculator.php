<?php

namespace App\Subnetting\Model\Calculators;

use App\Subnetting\Model,
    App\Subnetting\Model\Utils\IP;

	class CIDRCalculator extends Calculator
	{
		/**
		 *
		 * @var Model\IpAddress
		 */
		private $ipAddress;

		/**
		 *
		 * @var Model\SubnetMask
		 */
		private $subnetMask;

		/**
		 *
		 * @var Model\SubnetMask
		 */
		private $mask;

		/**
		 *
		 * @var Model\Network
		 */
		private $network;


		public function __construct(Model\IpAddress $ipAddress,
								Model\SubnetMask $subnetMask,
								Model\SubnetMask $mask)
		{
			$this->ipAddress = $ipAddress;
			$this->subnetMask = $subnetMask;
			$this->mask = $mask;
			$this->network = new Model\Network($ipAddress, $subnetMask);

			if ($subnetMask->getPrefix() >= $mask->getPrefix()) {
				throw new \App\Subnetting\Exceptions\LogicExceptions\CIDRSubnetMaskRangeException;
			}
		}

		public function calculateSubnets($offset, $length)
		{
			$hosts = $this->mask->getNumberOfHostsProvidedByMask();

			$base = $this->network->getNetworkAddress();

			$startAddress = new Model\IpAddress(IP::long2ip(IP::ip2long($base->getAddress()) + $this->calcBlocksFromBeginningToOffset($offset)));

			$subnets = array();
			$subnets[0] = new Model\Subnetwork($startAddress, $this->mask, $hosts);
			for ($s = 1; $s < $length; $s++) {
				$subnets[$s] = new Model\Subnetwork($this->findNextAddress($subnets[$s - 1]->getBroadcastAddress()), $this->mask, $hosts);
			}

			return $subnets;
		}

		/**
		 *
		 * @param int $offset
		 * @return int
		 */
		protected function calcBlocksFromBeginningToOffset($offset)
		{
			return ($this->mask->getNumberOfHostsProvidedByMask() * $offset);
		}

		/**
		 *
		 * @return int
		 */
		public function getNumberOfSubNetworks()
		{
			return pow(2, $this->mask->getPrefix() - $this->subnetMask->getPrefix());
		}


		/**
		 * @return int
		 */
		public function getNumberOfAddressesInSubnet()
		{
			return $this->mask->getNumberOfHostsProvidedByMask();
		}

		/**
		 *
		 * @return Model\Network
		 */
		public function getNetwork()
		{
			return $this->network;
		}

	}