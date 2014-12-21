<?php

namespace App\Subnetting\Model\Calculators;

use App\Subnetting\Model,
    App\Subnetting\Model\Utils\IP;

	abstract class Calculator
	{

		/**
		 *
		 * @param int $offset
		 * @return int
		 */
		abstract protected function calcBlocksFromBeginningToOffset($offset);

		/**
		 *
		 * @param Model\IpAddress $address
		 * @return Model\IpAddress
		 */
		protected function findNextAddress(Model\IpAddress $address)
		{
			$nextAddress = IP::long2ip(IP::ip2long($address->getAddress()) + 1);

			return new Model\IpAddress($nextAddress);
		}

		/**
		 *
		 * @param Model\IpAddress $address
		 * @return Model\IpAddress
		 */
		protected function findPreviousAddress(Model\IpAddress $address)
		{
			$previousAddress = IP::long2ip(IP::ip2long($address->getAddress()) - 1);

			return new Model\IpAddress($previousAddress);
		}

	}