<?php

namespace App\Subnetting\Model;

use \Nette\Utils\Validators,
	App\Subnetting\Model\Utils\IP,
	App\Subnetting\Exceptions\LogicExceptions;

	class SubnetMask extends Address implements IAddress
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
			if ($this->hasIPaddressValidFormat($subnetMask)) {

				$this->address = $subnetMask;
				$this->prefix = (int)$this->mask2cidr($this);

			} else {

				$this->prefix = str_replace('/', '', $subnetMask);
				$this->address = $this->cidr2mask($this->prefix);
			}

			$this->binaryAddress = Utils\OctetConvertor::convertIpFromDecimalToBinary($this);
			$this->wildCard = $this->createWildCard($this);
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
		 * @return int
		 */
		public function getNumberOfHostsProvidedByMask()
		{
			$invertedMask = IP::ip2long($this->wildCard) + 1;

			return $invertedMask;
		}


		/**
		 * @param SubnetMask $mask
		 * @return IpAddress
		 */
		private function createWildCard(SubnetMask $mask)
		{
			return new IpAddress(IP::logic_not($mask));
		}


		/**
		 * @param SubnetMask $mask
		 * @return int
		 * @throws LogicExceptions\SpecialSubnetMaskException
		 * @throws LogicExceptions\InvalidSubnetMaskFormatException
		 */
		private function mask2cidr(SubnetMask $mask)
		{
			if ($mask->getAddress() == '255.255.255.255' OR
			    $mask->getAddress() == '255.255.255.254') {
				throw new LogicExceptions\SpecialSubnetMaskException();
			}

			$long = IP::ip2long($mask);
			$base = IP::ip2long('255.255.255.255');
			$result = 32 - log(($long ^ $base) + 1, 2);

			if ($this->isWholeNumber($result) === FALSE) {
				throw new LogicExceptions\InvalidSubnetMaskFormatException('Address ' .$mask. ' is NOT a valid Subnet Mask.');
			}

			return $result;
		}

		/**
		 *
		 * @param int $cidr
		 * @return string IP address as a string
		 * @throws LogicExceptions\InvalidPrefixException
		 * @throws LogicExceptions\PrefixOutOfRangeException
		 */
		private function cidr2mask($cidr)
		{
			if (!$this->hasPrefixFormat($cidr)) {
				throw new LogicExceptions\InvalidPrefixException();
			}

			if (!$this->isPrefixValid($cidr)) {
				throw new LogicExceptions\PrefixOutOfRangeException('Prefix can be only between 1 - 30. '. $cidr. ' given!');
			}

			$c = pow(2, 32 - (int)$cidr) - 1;
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
			if (!preg_match('~^\/?\d+$~', $cidr)) {
				return FALSE;
			}

			return TRUE;
		}

		/**
		 *
		 * @param string $cidr
		 * @return boolean
		 */
		private function isPrefixValid($cidr)
		{
			if (!preg_match('~^\/?([1-9]|1[0-9]{1}|2[0-9]{1}|30)$~', $cidr)) {
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

			return (abs($number - round($number)) < 0.000000001) ? TRUE : FALSE;
		}

		/**
		 * @return string
		 */
		public function __toString()
		{
			return $this->address;
		}

	}
