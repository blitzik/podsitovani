<?php

namespace App\Subnetting\Model;

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
			$ip = ip2long($this->ipAddress->getAddress());
			$sm = ip2long($this->subnetMask->getAddress());

			return new IpAddress(long2ip($ip & $sm));
		}

		/**
		 *
		 * @return IpAddress
		 */
		protected function findBroadcastAddress()
		{
			$networkAddress = ip2long($this->networkAddress->getAddress());
			$mask = ip2long($this->subnetMask->getAddress());

			return new IpAddress(long2ip($networkAddress + (~$mask)));
		}

		/**
		 *
		 * @return IpAddress
		 */
		protected function calcFirstValidHostAddress()
		{
			return new IpAddress(long2ip(sprintf('%u', ip2long($this->networkAddress->getAddress())) + 1));
		}

		/**
		 *
		 * @return IpAddress
		 */
		protected function calcLastValidHostAddress()
		{
			$networkAddress = sprintf('%u', ip2long($this->firstValidHost->getAddress()));

			return new IpAddress(long2ip($networkAddress	+ $this->getNumberOfValidHosts() - 1));
		}

		/**
		 *
		 * @return Integer
		 */
		public function getNumberOfValidHosts()
		{
			$invertedMask = ip2long($this->subnetMask->getWildCard()->getAddress());

			return (int)$invertedMask - 1;
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
		 * @param IpAddress $ipAddress
		 * @return boolean
		 */
		public function isIPFromNetwork(IpAddress $ipAddress)
		{
			$firstDec = sprintf('%u', ip2long($this->networkAddress));
			$lastDec = sprintf('%u', ip2long($this->broadcastAddress));

			$ipDec = sprintf('%u', ip2long($ipAddress->getAddress()));

			return (($ipDec >= $firstDec AND $ipDec <= $lastDec) ? TRUE : FALSE);
		}

	}