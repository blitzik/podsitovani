<?php

namespace Model;

use \Nette\Utils\Validators;

	class SubnetMask extends Address implements \IAddress
	{

		/**
		 *
		 * @var Integer
		 */
		private $prefix;

		/**
		 *
		 * @var IpAddress
		 */
		private $wildCard;

		public function __construct($subnetMask)
		{
			if ($this->hasPrefixFormat($subnetMask)) {

				$this->prefix = str_replace('/', '', $subnetMask);
				$this->address = $this->cidr2mask($this->prefix);

			} else {

				if (!$this->hasIPaddressValidFormat($subnetMask)) {
					throw new \LogicExceptions\InvalidSubnetMaskException('Address ' .$subnetMask. ' is NOT a valid Subnet Mask / Prefix.');
				}
				$this->prefix = (int)$this->mask2cidr($subnetMask);
				$this->address = $subnetMask;
			}

			$this->binaryAddress = OctetConvertor::convertIpFromDecimalToBinary($this);
			$this->wildCard = $this->createWildCard($this->address);
		}

		/**
		 *
		 * @return int
		 */
		public function getPrefix()
		{
			return $this->prefix;
		}
		
		/**
		 * 
		 * @return IpAddress
		 */
		public function getWildCard()
		{
			return $this->wildCard;
		}

		/**
		 * 
		 * @param String $subnetMask
		 * @return IpAddress
		 */
		private function createWildCard($subnetMask)
		{
			$mask = ip2long($subnetMask);
			$wildCard = ~$mask;

			return new IpAddress(long2ip($wildCard));
		}

		/**
		 *
		 * @param String $mask (255.255.224.0)
		 * @return Int
		 * @throws \LogicExceptions\InvalidSubnetMaskException
		 */
		private function mask2cidr($mask)
		{
			$long = ip2long($mask);
			$base = ip2long('255.255.255.255');
			$result = 32 - log(($long ^ $base) + 1, 2);

			if ($this->isWholeNumber($result) === FALSE) {
				throw new \LogicExceptions\InvalidSubnetMaskException('Address ' .$mask. ' is NOT a valid Subnet Mask.');
			}

			return $result;
		}

		/**
		 *
		 * @param int $cidr
		 * @return String Subnet mask
		 */
		private function cidr2mask($cidr)
		{
			$c = pow(2, 32 - $cidr) - 1;
			$x = ~$c;

			return long2ip($x);
		}

		/**
		 *
		 * @param string $cidr
		 * @return boolean
		 */
		private function hasPrefixFormat($cidr)
		{
			if (!preg_match('~^[\/]?([1-9]|1[0-9]{1}|2[0-9]{1}|3[012]{1})$~', $cidr)) {
				return FALSE;
			}

			return TRUE;
		}

		/**
		 *
		 * @param int $number
		 * @return boolean
		 */
		private function isWholeNumber($number)
		{
			if (!Validators::is($number, 'number') OR is_nan($number) OR is_infinite($number)) {
				return FALSE;
			}

			return (abs($number - round($number)) < 0.0001) ? TRUE : FALSE;
		}

		public function __toString()
		{
			return $this->address;
		}

	}
