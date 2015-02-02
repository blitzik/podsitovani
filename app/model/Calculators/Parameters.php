<?php

namespace App\Subnetting\Model\Calculators;

	use App\Subnetting\Model\IpAddress;
	use App\Subnetting\Model\Network;
	use App\Subnetting\Model\SubnetMask;

	abstract class Parameters extends \Nette\Object
	{
		/**
		 * @var IpAddress
		 */
		protected $ipAddress;


		/**
		 * @var SubnetMask
		 */
		protected $subnetMask;


		/**
		 * @var Network
		 */
		protected $network;

		public function __construct($ipAddress, $subnetMask)
		{
			$this->ipAddress = new IpAddress($ipAddress);
			$this->subnetMask = new SubnetMask($subnetMask);

			$this->network = new Network($this->ipAddress, $this->subnetMask);
		}


		/**
		 * @return IpAddress
		 */
		public function getIpAddress()
		{
			return $this->ipAddress;
		}


		/**
		 * @return SubnetMask
		 */
		public function getSubnetMask()
		{
			return $this->subnetMask;
		}


		/**
		 * @return Network
		 */
		public function getNetwork()
		{
			return $this->network;
		}

	}