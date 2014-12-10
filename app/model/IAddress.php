<?php

namespace App\Subnetting\Model;

	interface IAddress
	{
		public function getAddress();

		public function getAddressInBinary();
	}