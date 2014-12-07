<?php

namespace Model;

	class VLSMCalculator
	{
		/**
		 *
		 * @var Network
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

		public function __construct(Network $network, $networksHosts)
		{
			$this->network = $network;

			$hosts = $this->separateNumberOfHosts($networksHosts);
			if (!$this->areHostsValid($hosts)) {
				throw new \LogicExceptions\InvalidNumberOfHostsException('Only whole numbers bigger than 0 are allowed.');
			}

			$this->networkHosts = $this->prepareValidNumberOfHosts($hosts);
			rsort($this->networkHosts);

			$this->calculateSubnetworks();
		}

		private function calculateSubnetworks()
		{
			$cidr = $this->calcCIDRbasedOnNumberOfHosts($this->networkHosts[0]);
			$subnetMask = new SubnetMask($cidr);
			$this->subnetworks[0] = new Subnetwork($this->network->getNetworkAddress(), $subnetMask, $this->networkHosts[0]);

			for ($i = 1; $i < count($this->networkHosts); $i++) {

				$cidr = $this->calcCIDRbasedOnNumberOfHosts($this->networkHosts[$i]);
				$subnetMask = new SubnetMask($cidr);
				$this->subnetworks[$i] = new Subnetwork($this->findNextNetworkAddress($this->subnetworks[$i - 1]->getBroadcastAddress()),
												$subnetMask, $this->networkHosts[$i]);
			}
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
		public function getSubnetworks()
		{
			return $this->subnetworks;
		}

		/**
		 *
		 * @param \Model\IpAddress $broadcastAddress
		 * @return \Model\IpAddress
		 */
		private function findNextNetworkAddress(IpAddress $broadcastAddress)
		{
			$nextAddress = long2ip(ip2long($broadcastAddress->getAddress()) + 1);

			return new IpAddress($nextAddress);
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