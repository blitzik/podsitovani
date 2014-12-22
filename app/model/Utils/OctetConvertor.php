<?php

namespace App\Subnetting\Model\Utils;

use \Nette\Utils\Validators,
    App\Subnetting\Model\Address;

	final class OctetConvertor
	{
		private function __construct(){}

		/**
		 *
		 * @param Address $ipAddress
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
		 * @return string Decimal format of IP address
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
		 * @param Address $ipAddress
		 * @return array
		 */
		public static function separateOctets(Address $ipAddress)
		{
			return explode('.', $ipAddress->getAddress());
		}

		/**
		 *
		 * @param int $number
		 * @return string Binary representation of number with leading zeros
		 * @throws \InvalidArgumentException
		 */
		public static function convertNumberToBinary($number)
		{
			if (!Validators::isNumericInt($number)) {
				throw new \InvalidArgumentException('Only integer numbers are allowed.');
			}

			$bin = decbin($number);

			return str_pad($bin, 8, '0', STR_PAD_LEFT);
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