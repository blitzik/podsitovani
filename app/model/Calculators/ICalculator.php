<?php

namespace App\Subnetting\Model\Calculators;

	interface ICalculator
	{
		/**
		 * @param $offset
		 * @param $length
		 * @return array
		 */
		public function calculateSubnetworks($offset, $length);


		/**
		 * @return int
		 */
		public function getNumberOfSubnetworks();
	}