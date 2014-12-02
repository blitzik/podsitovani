<?php

namespace Model;

	class Subnetwork extends Network
	{
		/**
		 *
		 * @var int
		 */
		private $hosts;

		/**
		 *
		 * @var int
		 */
		private $blockOfAddresses;

		public function __construct(Address $ipAddress, SubnetMask $mask, $numberOfNeededHosts)
		{
			$this->hosts = $this->checkNumberOfHosts($numberOfNeededHosts);
			$this->blockOfAddresses = $this->calcBlockOfAddress($numberOfNeededHosts);

			parent::__construct($ipAddress, $mask);
		}


		protected function findNetworkAddress()
		{
			return $this->getIpAddress();
		}

		protected function findBroadcastAddress()
		{
			$networkAddress = ip2long($this->getNetworkAddress()->getAddress());
			$broadcast = long2ip($networkAddress + $this->blockOfAddresses - 1);

			return new IpAddress($broadcast);
		}

		protected function calcLastValidHostAddress()
		{
			return new IpAddress(long2ip(ip2long($this->getBroadcastAddress()->getAddress()) - 1));
		}

		/**
		 *
		 * @param int $hosts
		 * @return int
		 * @throws \LogicExceptions\InvalidNumberOfHostsException
		 */
		private function checkNumberOfHosts($hosts)
		{
			$hosts = trim($hosts);
			if (!ctype_digit($hosts) OR $hosts == 0) {
				throw new \LogicExceptions\InvalidNumberOfHostsException('Invalid number of hosts. Only whole number bigger than 0 is allowed. "' .$hosts. '" given.');
			}

			return (int)$hosts;
		}

		/**
		 *
		 * @param int $hosts
		 * @return int
		 */
		private function calcBlockOfAddress($hosts)
		{
			$hosts = $this->checkNumberOfHosts($hosts);

			return (int)pow(2, (ceil(log($hosts, 2))));
		}

		/**
		 *
		 * @return boolean
		 */
		public function isNetworkRangeBigEnough()
		{
			if ($this->getNumberOfValidHosts() >= ($this->blockOfAddresses - 2)) {
				return TRUE;
			}

			return FALSE;
		}

		/**
		 *
		 * @return int
		 */
		public function getHosts()
		{
			return $this->hosts;
		}

		/**
		 *
		 * @return int
		 */
		public function getBlockOfAddresses()
		{
			return $this->blockOfAddresses;
		}

	}