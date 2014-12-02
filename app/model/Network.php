<?php

namespace Model;

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
		 * @return \Model\IpAddress
		 */
		protected function calcFirstValidHostAddress()
		{
			return new IpAddress(long2ip(sprintf('%u', ip2long($this->networkAddress->getAddress())) + 1));
		}

		/**
		 *
		 * @return \Model\IpAddress
		 */
		protected function calcLastValidHostAddress()
		{
			return new IpAddress(long2ip(sprintf('%u', ip2long($this->networkAddress->getAddress())) + $this->getNumberOfValidHosts()));
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
		 * @return \Model\IpAddress
		 */
		public function getFirstValidHost()
		{
			return $this->firstValidHost;
		}

		/**
		 *
		 * @return \Model\IpAddress
		 */
		public function getLastValidHost()
		{
			return $this->lastValidHost;
		}

		/**
		 *
		 * @return \Model\IpAddress
		 */
		public function getIpAddress()
		{
			return $this->ipAddress;
		}

		/**
		 *
		 * @return \Model\SubnetMask
		 */
		public function getSubnetMask()
		{
			return $this->subnetMask;
		}

		/**
		 *
		 * @return \Model\IpAddress
		 */
		public function getNetworkAddress()
		{
			return $this->networkAddress;
		}

		/**
		 *
		 * @return \Model\IpAddress
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
			$networkFactory = new NetworkFactory;

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
		 * @param \Model\IpAddress $ipAddress
		 * @return boolean
		 */
		public function isIPFromNetwork(IpAddress $ipAddress)
		{
			$firstDec = sprintf('%u', ip2long($this->networkAddress));
			$lastDec = sprintf('%u', ip2long($this->broadcastAddress));

			$ipDec = sprintf('%u', ip2long($ipAddress->getAddress()));

			return (($ipDec >= $firstDec AND $ipDec <= $lastDec) ? TRUE : FALSE);
		}

		public function __toString()
		{
			$output = 'IP: ' .$this->ipAddress->getAddress(). '/' .$this->subnetMask->getCIDR(). '<br>'
			                 .$this->ipAddress->getAddressInBinary(). '<br>'

					.'Subnet Mask: ' .$this->subnetMask->getAddress(). '<br>'
								  .$this->subnetMask->getAddressInBinary(). '<br>'

					.'Network Address: ' .$this->networkAddress->getAddress(). '<br>'
									 .$this->networkAddress->getAddressInBinary(). '<br>'

					.'Broadcast Address: ' .$this->broadcastAddress->getAddress(). '<br>'
									   .$this->broadcastAddress->getAddressInBinary(). '<br>';

			$range = $this->getNetworkRangeOfAddresses();

			$output .= 'Range of valid hosts: ' .$range['first']->getAddress(). ' - ' .$range['last']->getAddress(). '<br>'
					 .'Number of hosts: ' .$this->getNumberOfValidHosts();
			return $output;
		}

	}