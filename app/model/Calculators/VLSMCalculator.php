<?php

namespace App\Subnetting\Model\Calculators;

use App\Subnetting\Model,
	App\Subnetting\Exceptions\LogicExceptions,
	App\Subnetting\Model\Utils\IP;

	class VLSMCalculator extends Calculator implements ICalculator
	{
		/**
		 * @var Model\IpAddress
		 */
		private $ipAddress;


		/**
		 * @var Model\SubnetMask
		 */
		private $subnetMask;

		/**
		 *
		 * @var Model\Network
		 */
		private $network;

		/**
		 *
		 * @var Array
		 */
		private $networkHosts = array();

		/**
		 *
		 * @var array Array of Networks
		 */
		private $subnetworks = array();


		public function __construct(VLSMParameters $parameters)
		{
			$this->ipAddress = $parameters->ipAddress;
			$this->subnetMask = $parameters->subnetMask;
			$this->network = $parameters->network;
			$this->networkHosts = $parameters->networksHosts;
		}

		/**
		 *
		 * @param int $offset
		 * @param int $length
		 * @return Array
		 */
		public function calculateSubnetworks($offset, $length)
		{
			$baseAddress = IP::ip2long($this->network->getNetworkAddress());
			$startAddress = IP::long2ip($baseAddress + $this->calcBlocksFromBeginningToOffset($offset));

			$this->subnetworks[0] = $this->calcNextSubnet(new Model\IpAddress($startAddress), $this->networkHosts[$offset]);

			for ($i = 1; $i < $length; $i++) {
				$this->subnetworks[$i] = $this->calcNextSubnet($this->findNextAddress($this->subnetworks[$i - 1]->getBroadcastAddress()), $this->networkHosts[$offset + $i]);
			}

			return $this->subnetworks;
		}

		/**
		 *
		 * @param int $offset
		 * @return int
		 */
		protected function calcBlocksFromBeginningToOffset($offset)
		{
			$totalAddresses = 0;
			for ($i = 0; $i < $offset; $i++) {
				$totalAddresses += IP::calcNumberOfAddressesInBlock($this->networkHosts[$i]);
			}

			return $totalAddresses;
		}

		/**
		 *
		 * @param \App\Subnetting\Model\IpAddress $ipAddress
		 * @param int $hosts
		 * @return \App\Subnetting\Model\Subnetwork
		 */
		private function calcNextSubnet(\App\Subnetting\Model\IpAddress $ipAddress, $hosts)
		{
			$cidr = $this->calcCIDRbasedOnNumberOfHosts($hosts);
			// TODO: tady to taky umre
			$subnetMask = new Model\SubnetMask($cidr);

			return  new Model\Subnetwork($ipAddress, $subnetMask, $hosts);
		}

		/**
		 *
		 * @param int $number
		 * @return int
		 */
		public function calcCIDRbasedOnNumberOfHosts($number)
		{
			return (int)(32 - ceil(log($number, 2)));
		}

		/**
		 *
		 * @return int
		 */
		public function getTotalNumberOfHostsInBlocks()
		{
			$boa = $this->getTotalNumberOfAddressesInBlocks();

			return (int)($boa - (2 * count($this->networkHosts)));
		}

		#TODO
		/*
				IP: 192.168.0.10 | M: 255.255.255.0
				Hosts: 2147483644

				IP: 192.168.0.10 | M: 255.255.255.0
				Hosts: 314, 45, 19, 1340000000

				Chyba, protože maska v podsíti nepobere tolik hostů
		 */

		/**
		 * @return Model\SubnetMask
		 */
		public function getRecommendedSubnetMask()
		{
			$numberOfHostsProvidedByMask = $this->network->getSubnetMask()->getNumberOfHostsProvidedByMask();
			if ($numberOfHostsProvidedByMask < $this->getTotalNumberOfAddressesInBlocks()) {

				$cidr = 32 - ceil(log(IP::calcNumberOfAddressesInBlock($this->getTotalNumberOfAddressesInBlocks()), 2));
				// $cidr vyjde 0 nebo bude zápornej = maska to nepobere a chcipne to vyjímkou
				return new Model\SubnetMask($cidr);
			}

			return $this->network->getSubnetMask();
		}

		/**
		 *
		 * @return int
		 */
		public function getTotalNumberOfGivenHosts()
		{
			return array_sum($this->networkHosts) - (2 * count($this->networkHosts));
		}

		/**
		 *
		 * @return int
		 */
		public function getTotalNumberOfAddressesInBlocks()
		{
			$addressesInBlocks = 0;
			foreach ($this->networkHosts as $hosts) {
				$addressesInBlocks += IP::calcNumberOfAddressesInBlock($hosts);
			}

			return $addressesInBlocks;
		}

		/**
		 *
		 * @return array
		 */
		/*public function getSubnetworks($offset, $length)
		{
			return $this->calculateSubnetworks($offset, $length);
		}*/

		/**
		 *
		 * @return float Percentage of Network address space
		 */
		public function getNetworkAddressSpaceUsed()
		{
			$percentage = $this->getTotalNumberOfHostsInBlocks() / $this->network->getNumberOfValidHosts() * 100;

			return number_format($percentage, 1, ',', ' ');
		}

		/**
		 *
		 * @return float Percentage of Subnetwork address space
		 */
		public function getSubnettedNetworkAddressSpaceUsed()
		{
			$percentage = $this->getTotalNumberOfGivenHosts() / ($this->getTotalNumberOfAddressesInBlocks() - (2 * count($this->networkHosts))) * 100;

			return number_format($percentage, 1, ',', ' ');
		}


		/**
		 *
		 * @return boolean
		 */
		public function isNetworkRangeBigEnough()
		{
			if ($this->network->getNumberOfValidHosts() < $this->getTotalNumberOfHostsInBlocks()) {
				return FALSE;
			}

			return TRUE;
		}

		/**
		 *
		 * @return Network
		 */
		public function getNetwork()
		{
			return $this->network;
		}

		/**
		 *
		 * @return array
		 */
		public function getNetworkHosts()
		{
			return $this->networkHosts;
		}

		/**
		 * @return int
		 */
		public function getNumberOfSubnetworks()
		{
			return count($this->networkHosts);
		}

	}