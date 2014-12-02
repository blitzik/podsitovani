<?php

namespace Model;

	class VLSMCalculator
	{
		/**
		 *
		 * @var Network
		 */
		private $network;

		/**
		 *
		 * @var Array
		 */
		private $networkHosts = array();

		/**
		 *
		 * @var array Array of Networks
		 */
		private $subnetworks = array();

		public function __construct(Network $network, $networksHosts)
		{
			$this->network = $network;

			$hosts = $this->separateNumberOfHosts($networksHosts);
			if (!$this->areHostsValid($hosts)) {
				throw new \LogicExceptions\InvalidNumberOfHostsException('Only whole numbers bigger than 0 are allowed.');
			}

			$this->networkHosts = $this->prepareValidNumberOfHosts($hosts);
			rsort($this->networkHosts);

			$this->calculateSubnetworks();
		}

		private function calculateSubnetworks()
		{
			$cidr = $this->calcCIDRbasedOnNumberOfHosts($this->networkHosts[0]);
			$subnetMask = new SubnetMask($cidr);
			$this->subnetworks[0] = new Subnetwork($this->network->getNetworkAddress(), $subnetMask, $this->networkHosts[0]);

			for ($i = 1; $i < count($this->networkHosts); $i++) {

				$cidr = $this->calcCIDRbasedOnNumberOfHosts($this->networkHosts[$i]);
				$subnetMask = new SubnetMask($cidr);
				$this->subnetworks[$i] = new Subnetwork($this->findNextNetworkAddress($this->subnetworks[$i - 1]->getBroadcastAddress()),
												$subnetMask, $this->networkHosts[$i]);
			}
		}

		/*public function getTotalNumberOfHosts()
		{
			$boa = $this->getTotalNumberOfBlockAddresses();

			return $boa - (2 * count($this->networkHosts));
		}*/

		public function getTotalNumberOfBlockAddresses()
		{
			$blockOfAddresses = 0;
			foreach ($this->networkHosts as $hosts) {
				$blockOfAddresses += pow(2, (ceil(log($hosts, 2))));
			}

			return $blockOfAddresses;
		}

		public function isNetworkRangeBigEnough()
		{
			if ($this->network->getNumberOfValidHosts() < $this->getTotalNumberOfHosts()) {
				return FALSE;
			}

			return TRUE;
		}

		public function getNetwork()
		{
			return $this->network;
		}

		public function getNetworkHosts()
		{
			return $this->networkHosts;
		}


		/**
		 *
		 * @return array
		 */
		public function getSubnetworks()
		{
			return $this->subnetworks;
		}

		/**
		 *
		 * @param \Model\IpAddress $broadcastAddress
		 * @return \Model\IpAddress
		 */
		private function findNextNetworkAddress(IpAddress $broadcastAddress)
		{
			$nextAddress = long2ip(ip2long($broadcastAddress->getAddress()) + 1);

			return new IpAddress($nextAddress);
		}

		/**
		 *
		 * @param array $hosts
		 * @return array
		 */
		private function prepareValidNumberOfHosts(array $hosts)
		{
			return array_map(function ($host) { return $host + 2;}, $hosts);
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
				if (!ctype_digit($host) OR $host == 0) {
					return FALSE;
				}
			}

			return TRUE;
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
		 * @param int $number
		 * @return int
		 */
		private function calcCIDRbasedOnNumberOfHosts($number)
		{
			return (int)(32 - ceil(log($number, 2)));
		}

		public function getAllResults()
		{
			return $this->subnetworks;
		}

		public function constructResultsTable()
		{
			$result = '<table border="1">';
			$result .= '<tr><th>Síť</th><th>Alokovaný blok adres</th><th>Prefix</th><th>Maska</th><th>Adresa sítě</th><th>Broadcast</th></tr>';

				for ($i = 0; $i < count($this->subnetworks); $i++){
					$network = $this->subnetworks[$i];
					$result .= '<tr>';

						$result .= '<td>' .$this->networkHosts[$i]. '</td>'.
							'<td>' .($network->getNumberOfValidHosts() + 2). '</td>'.
						     '<td>' .$network->getSubnetMask()->getCIDR(). '</td>'.
						     '<td>' .$network->getSubnetMask()->getAddress(). '</td>'.
						     '<td>' .$network->getNetworkAddress()->getAddress(). '</td>'.
						     '<td>' .$network->getBroadcastAddress()->getAddress(). '</td>';

					$result .= '</tr>';

				}

			$result .= '</table>';

			return $result;
		}

	}