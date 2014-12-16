<?php

namespace App\Subnetting\Model\Utils;

use \Nette\Utils\Validators,
    App\Subnetting\Model\Address;

	final class OctetConvertor
	{
		private function __construct(){}

		/**
		 *
		 * @param App\Subnetting\Model\Address $ipAddress
		 * @return String Binary format of IP address
		 */
		public static function convertIpFromDecimalToBinary(Address $ipAddress)
		{
			$ipString = '';
			foreach (self::separateOctets($ipAddress) as $octet) {
				$ipString .= self::convertNumberToBinary($octet) . '.';
			}

			return substr($ipString, 0,-1);
		}

		/**
		 *
		 * @param string $ipAddress
		 * @return String Decimal format of IP address
		 */
		public static function convertIpFromBinaryToDecimal($ipAddress)
		{
			$ip = '';
			foreach (explode('.', $ipAddress) as $octet) {
				$ip .= self::convertBinaryToNumber($octet) . '.';
			}

			return substr($ip, 0, -1);
		}

		/**
		 *
		 * @param \App\Subnetting\Model\Address $ipAddress
		 * @return array
		 */
		public static function separateOctets(Address $ipAddress)
		{
			return explode('.', $ipAddress->getAddress());
		}

		/**
		 *
		 * @param int $number
		 * @return int
		 * @throws \InvalidArgumentException
		 */
		public static function convertNumberToBinary($number)
		{
			if (!Validators::isNumericInt($number)) {
				throw new \InvalidArgumentException('Only integer numbers are allowed.');
			}

			$bit = 0;
			$sequenceOfBits = '';
			for ($i = 7; $i >= 0; $i--) {

				$remainder = $number - pow(2, $i);

				if ($remainder >= 0) {
					$number -= pow(2, $i);
					$bit = 1;
				}

				if ($remainder < 0) {
					$bit = 0;
				}

			$sequenceOfBits .= $bit;
			}

			return $sequenceOfBits;
		}

		/**
		 *
		 * @param String $binary
		 * @return int
		 */
		public static function convertBinaryToNumber($binary)
		{
			return bindec($binary);
		}

	}