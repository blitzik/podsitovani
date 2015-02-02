<?php

namespace App\Subnetting\Exceptions\LogicExceptions;

	class InvalidIpAddressException extends \LogicException
	{
	}

	class InvalidSubnetMaskException extends \LogicException
	{
	}

		class InvalidSubnetMaskFormatException extends InvalidSubnetMaskException
		{
		}

		class SpecialSubnetMaskException extends InvalidSubnetMaskException
		{
		}

		class InvalidPrefixException extends InvalidSubnetMaskException
		{
		}

		class PrefixOutOfRangeException extends InvalidSubnetMaskException
		{
		}

	class InvalidCIDRFormatException extends \LogicException
	{
	}

	class CIDRSubnetMaskRangeException extends \LogicException
	{
	}

	class InvalidHostsFormatException extends \LogicException
	{
	}

	class InvalidNumberOfHostsException extends \LogicException
	{
	}