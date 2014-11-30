<?php

namespace Model;

	class IpAddress extends Address implements \IAddress
	{

		public function __construct($ipAddress)
		{
			parent::__construct($ipAddress);
		}

		public function __toString() {
			return $this->address;
		}

	}