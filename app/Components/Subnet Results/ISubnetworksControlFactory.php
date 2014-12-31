<?php

namespace App\Subnetting\Model\Components;

	interface ISubnetworksControlFactory
	{
		/**
		 * @return SubnetworksControl
		 */
		public function create();
	}