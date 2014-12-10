<?php

namespace App\Subnetting\Model;

	abstract class Address
	{
		/**
		 *
		 * @var String
		 */
		protected $address;

		/**
		 *
		 * @var String
		 */
		protected $binaryAddress;

		public function __construct($address)
		{
			if (!$this->hasIPaddressValidFormat($address)) {
				throw new \LogicExceptions\InvalidIpAddressException('Address ' .$address. ' is NOT a valid IPv4.');
			}

			$this->address = $address;

			$this->binaryAddress = OctetConvertor::convertIpFromDecimalToBinary($this);

		}

		protected function hasIPaddressValidFormat($address)
		{
			if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === FALSE) {
				return FALSE;
			}

			return TRUE;
		}

		/**
		 *
		 * @return IpAddress
		 */
		public function getAddress()
		{
			return $this->address;
		}

		/**
		 *
		 * @return String IP address in binary format
		 */
		public function getAddressInBinary()
		{
			return $this->binaryAddress;
		}

	}