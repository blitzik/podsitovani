<?php

namespace App\Subnetting\Model;

use App\Subnetting\Model\Utils\IP,
	App\Subnetting\Exceptions\LogicExceptions;

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

		/**
		 *
		 * @return IpAddress
		 */
		protected function findNetworkAddress()
		{
			return $this->getIpAddress();
		}

		/**
		 *
		 * @return IpAddress
		 */
		protected function findBroadcastAddress()
		{
			$hosts = $this->getSubnetMask()->getNumberOfHostsProvidedByMask() - 1;

			$network = IP::ip2long($this->getNetworkAddress());

			return new IpAddress(IP::long2ip($network + $hosts));
		}

		/**
	 	 * @param $hosts
	 	 * @return int
	 	 * @throws InvalidNumberOfHostsException
	 	 */
		private function checkNumberOfHosts($hosts)
		{
			$hosts = trim($hosts);
			if (!ctype_digit($hosts) OR $hosts == 0) {
				throw new InvalidNumberOfHostsException('Invalid number of hosts. Only whole number bigger than 0 is allowed. "' .$hosts. '" given.');
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

			return IP::calcBlockOfAddresses($hosts);
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

		public function getAmountOfUsedAddressSpace()
		{
			return number_format((($this->hosts / $this->blockOfAddresses) * 100), 1, ',', ' ');
		}

	}