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


		public function __construct(Model\IpAddress $ipAddress,
								Model\SubnetMask $subnetMask,
								Model\SubnetMask $mask)
		{
			$this->ipAddress = $ipAddress;
			$this->subnetMask = $subnetMask;
			$this->mask = $mask;

			if ($subnetMask->getPrefix() >= $mask->getPrefix()) {
				throw new \App\Subnetting\Exceptions\LogicExceptions\CIDRSubnetMaskRangeException;
			}
		}

		public function calculateSubnets()
		{
			$hosts = $this->mask->getNumberOfHostsProvidedByMask();
			$numberOfNetworks = $this->getNumberOfSubNetworks();

			$base = (new Model\Network($this->ipAddress, $this->subnetMask))->getNetworkAddress();

			$subnets = array();
			$subnets[0] = new Model\Subnetwork($base, $this->mask, $hosts);
			for ($s = 1; $s < $numberOfNetworks; $s++) {

				$subnets[$s] = new Model\Subnetwork($this->findNextAddress($subnets[$s - 1]->getBroadcastAddress()), $this->mask, $hosts);

			}

			return $subnets;
		}

		public function getNumberOfSubNetworks()
		{
			return pow(2, $this->mask->getPrefix() - $this->subnetMask->getPrefix());
		}

	}