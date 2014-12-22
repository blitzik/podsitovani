<?php

namespace App\Subnetting\Model\Utils;

use App\Subnetting\Model\Address;

	final class IP
	{
		private function __construct(){}

		/**
		 *
		 * @param Address $address
		 * @return float A proper address representation
		 */
		public static function ip2long($address)
		{
			return (float)sprintf('%u', ip2long($address));
		}

		/**
		 *
		 * @param int|float $proper_address A proper address representation
		 * @return string Returns the Internet IP address as a string
		 * @throws \InvalidArgumentException
		 */
		public static function long2ip($proper_address)
		{
			return long2ip($proper_address);
		}

		/**
		 *
		 * @param Address $a
		 * @param Address $b
		 * @return string Returns the Internet IP address as a string
		 */
		public static function logic_and(Address $a, Address $b)
		{
			$long_a = self::ip2long($a);
			$long_b = self::ip2long($b);

			return self::long2ip($long_a & $long_b);
		}

		/**
		 *
		 * @param Address $a
		 * @param Address $b
		 * @return string Returns the Internet IP address as a string
		 */
		public static function logic_or(Address $a, Address $b)
		{
			$long_a = self::ip2long($a);
			$long_b = self::ip2long($b);

			return self::long2ip($long_a | $long_b);
		}

		/**
		 *
		 * @param Address $address
		 * @return string Returns the Internet IP address as a string
		 */
		public static function logic_not(Address $address)
		{
			$a = (float)self::ip2long($address);
			$result = ~$a;

			return self::long2ip($result);
		}

		/**
		 *
		 * @param int $hosts
		 * @return int
		 */
		public static function calcBlockOfAddresses($hosts)
		{
			return (int)pow(2, (ceil(log($hosts, 2))));
		}

	}