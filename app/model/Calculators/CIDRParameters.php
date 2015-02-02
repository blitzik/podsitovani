<?php

namespace App\Subnetting\Model\Calculators;

	use App\Subnetting\Exceptions\LogicExceptions\CIDRSubnetMaskRangeException;
	use App\Subnetting\Model\SubnetMask;

	class CIDRParameters extends Parameters
	{
		/**
		 * @var SubnetMask
		 */
		private $subnetMask2;


		public function __construct($ipAddress, $subnetMask, $mask)
		{
			parent::__construct($ipAddress, $subnetMask);

			$this->subnetMask2 = new SubnetMask($mask);

			if ($this->subnetMask->getPrefix() >= $this->subnetMask2->getPrefix()) {
				throw new CIDRSubnetMaskRangeException;
			}
		}


		/**
		 * @return SubnetMask
		 */
		public function getSubnetMask2()
		{
			return $this->subnetMask2;
		}

	}