<?php

namespace App\Subnetting\Model;

use App\Subnetting\Model\Utils\IP;

	class Network
	{
		/**
		 *
		 * @var IpAddress
		 */
		private $ipAddress;

		/**
		 *
		 * @var SubnetMask
		 */
		private $subnetMask;

		/**
		 *
		 * @var IpAddress
		 */
		private $networkAddress;

		/**
		 *
		 * @var IpAddress
		 */
		private $broadcastAddress;

		/**
		 *
		 * @var IpAddress
		 */
		private $firstValidHost;

		/**
		 *
		 * @var IpAddress
		 */
		private $lastValidHost;


		public function __construct(Address $ipAddress, SubnetMask $mask)
		{
			$this->ipAddress = $ipAddress;
			$this->subnetMask = $mask;

			$this->networkAddress = $this->findNetworkAddress();
			$this->broadcastAddress = $this->findBroadcastAddress();

			$this->firstValidHost = $this->calcFirstValidHostAddress();
			$this->lastValidHost = $this->calcLastValidHostAddress();
		}

		/**
		 *
		 * @return IpAddress
		 */
		protected function findNetworkAddress()
		{
			return new IpAddress(IP::logic_and($this->ipAddress, $this->subnetMask));
		}

		/**
		 *
		 * @return IpAddress
		 */
		protected function findBroadcastAddress()
		{
			return new IpAddress(IP::logic_or($this->subnetMask->getWildCard(), $this->networkAddress));
		}

		/**
		 *
		 * @return IpAddress
		 */
		protected function calcFirstValidHostAddress()
		{
			return new IpAddress(IP::long2ip(IP::ip2long($this->networkAddress) + 1));
		}

		/**
		 *
		 * @return IpAddress
		 */
		protected function calcLastValidHostAddress()
		{
			return new IpAddress(IP::long2ip(IP::ip2long($this->broadcastAddress) - 1));
		}

		/**
		 *
		 * @return Integer
		 */
		public function getNumberOfValidHosts()
		{
			return $this->subnetMask->getNumberOfHostsProvidedByMask() - 2;
		}

		/**
		 *
		 * @return IpAddress
		 */
		public function getFirstValidHost()
		{
			return $this->firstValidHost;
		}

		/**
		 *
		 * @return IpAddress
		 */
		public function getLastValidHost()
		{
			return $this->lastValidHost;
		}

		/**
		 *
		 * @return IpAddress
		 */
		public function getIpAddress()
		{
			return $this->ipAddress;
		}

		/**
		 *
		 * @return SubnetMask
		 */
		public function getSubnetMask()
		{
			return $this->subnetMask;
		}

		/**
		 *
		 * @return IpAddress
		 */
		public function getNetworkAddress()
		{
			return $this->networkAddress;
		}

		/**
		 *
		 * @return IpAddress
		 */
		public function getBroadcastAddress()
		{
			return $this->broadcastAddress;
		}

		/**
		 *
		 * @return boolean
		 */
		public function isPrivate()
		{
			$networkFactory = new Factories\Networks\NetworkFactory();

			$privateNetworks = array(
			    $networkFactory->createNetwork('10.0.0.0', '/8'),
			    $networkFactory->createNetwork('172.16.0.0', '/12'),
			    $networkFactory->createNetwork('192.168.0.0', '/16'),
			);

			foreach ($privateNetworks as $network) {
				if ($network->isIPFromNetwork($this->ipAddress)) {
					return TRUE;
				}
			}

			return FALSE;
		}

		/**
		 *
		 * @param Address $ipAddress
		 * @return boolean
		 */
		public function isIPFromNetwork(Address $ipAddress)
		{
			$firstDec = IP::ip2long($this->networkAddress);
			$lastDec = IP::ip2long($this->broadcastAddress);

			$ipDec = IP::ip2long($ipAddress);

			if ($ipAddress ) {

			}

			return (($ipDec >= $firstDec AND $ipDec <= $lastDec) ? TRUE : FALSE);
		}

	}