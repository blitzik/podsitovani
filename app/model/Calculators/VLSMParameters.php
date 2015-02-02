<?php

namespace App\Subnetting\Model\Calculators;

	use App\Subnetting\Exceptions\LogicExceptions\InvalidHostsFormatException;
	use App\Subnetting\Exceptions\LogicExceptions\InvalidNumberOfHostsException;

	class VLSMParameters extends Parameters
	{
		/**
		 * @var array
		 */
		private $networksHosts;


		public function __construct($ipAddress, $mask, $networksHosts)
		{
			parent::__construct($ipAddress, $mask);

			$hosts = $this->separateNumberOfHosts($networksHosts);
			if (!$this->areHostsValid($hosts)) {
				throw new InvalidHostsFormatException('Only whole numbers are allowed.');
			}

			$this->networksHosts = $this->prepareValidNumberOfHosts($hosts);
			rsort($this->networksHosts);
		}

		/**
		 *
		 * @param array $hosts
		 * @return array
		 */
		private function prepareValidNumberOfHosts(array $hosts)
		{
			// +2 means -> Network and Broadcast Address
			$networksHosts = array_map(function ($host) { return $host + 2;}, $hosts);
			$numberOfHosts = array_sum($networksHosts);

			if ($numberOfHosts <= 0 OR $numberOfHosts > 2147483648) {
				throw new InvalidNumberOfHostsException('Only whole numbers between 0 and 2 147 483 647 are allowed.');
			}

			return $networksHosts;
		}

		/**
		 *
		 * @param String $hosts
		 * @return array
		 */
		private function separateNumberOfHosts($hosts)
		{
			return explode(',', $hosts);
		}

		/**
		 *
		 * @param array $hosts
		 * @return boolean Returns TRUE if hosts have valid format, otherwise FALSE
		 */
		private function areHostsValid(array $hosts)
		{
			foreach ($hosts as $host) {
				$host = trim($host);
				if (!ctype_digit($host)) {
					return FALSE;
				}
			}

			return TRUE;
		}

		/**
		 * @return array
		 */
		public function getNetworksHosts()
		{
			return $this->networksHosts;
		}

	}