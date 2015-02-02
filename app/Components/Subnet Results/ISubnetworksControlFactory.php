<?php

namespace App\Subnetting\Model\Components;

	use App\Subnetting\Model\Calculators\Parameters;

	interface ISubnetworksControlFactory
	{
		/**
		 * @return SubnetworksControl
		 */
		public function create(Parameters $parameters);
	}