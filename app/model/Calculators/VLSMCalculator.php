<?php

namespace App\Subnetting\Model\Calculators;

use App\Subnetting\Model,
	App\Subnetting\Exceptions\LogicExceptions,
	App\Subnetting\Model\Utils\IP;

	class VLSMCalculator extends Calculator
	{
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

		public function __construct(Model\Network $network, $networksHosts)
		{
			$this->network = $network;

			$hosts = $this->separateNumberOfHosts($networksHosts);
			if (!$this->areHostsValid($hosts)) {
				throw new LogicExceptions\InvalidNumberOfHostsException('Only whole numbers bigger than 0 are allowed.');
			}

			$this->networkHosts = $this->prepareValidNumberOfHosts($hosts);
			rsort($this->networkHosts);
		}

		/**
		 *
		 * @param int $offset
		 * @param int $length
		 * @return Array
		 */
		private function calculateSubnetworks($offset, $length)
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
				$totalAddresses += IP::calcBlockOfAddresses($this->networkHosts[$i]);
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
			$subnetMask = new Model\SubnetMask($cidr);

			return  new Model\Subnetwork($ipAddress, $subnetMask, $hosts);
		}

		/**
		 *
		 * @return int
		 */
		public function getTotalNumberOfHostsInBlocks()
		{
			$boa = $this->getTotalNumberOfBlockAddresses();

			return (int)($boa - (2 * count($this->networkHosts)));
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
		public function getTotalNumberOfBlockAddresses()
		{
			$blockOfAddresses = 0;
			foreach ($this->networkHosts as $hosts) {
				$blockOfAddresses += pow(2, (ceil(log($hosts, 2))));
			}

			return (int)$blockOfAddresses;
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
		 *
		 * @return array
		 */
		public function getSubnetworks($offset, $length)
		{
			return $this->calculateSubnetworks($offset, $length);
		}

		/**
		 *
		 * @param array $hosts
		 * @return array
		 */
		private function prepareValidNumberOfHosts(array $hosts)
		{
			return array_map(function ($host) { return $host + 2;}, $hosts);
		}

		/**
		 *
		 * @param array $hosts
		 * @return boolean Returns TRUE if hosts have valid format, otherwise FALSE
		 */
		private function areHostsValid(array $hosts)
		{
			foreach ($hosts as $host) {
				$host = trim($host);
				if (!ctype_digit($host) OR $host == 0) {
					return FALSE;
				}
			}

			return TRUE;
		}

		/**
		 *
		 * @param String $hosts
		 * @return array
		 */
		private function separateNumberOfHosts($hosts)
		{
			return explode(',', $hosts);
		}

		/**
		 *
		 * @param int $number
		 * @return int
		 */
		private function calcCIDRbasedOnNumberOfHosts($number)
		{
			return (int)(32 - ceil(log($number, 2)));
		}

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
			$percentage = $this->getTotalNumberOfGivenHosts() / ($this->getTotalNumberOfBlockAddresses() - (2 * count($this->networkHosts))) * 100;

			return number_format($percentage, 1, ',', ' ');
		}

	}