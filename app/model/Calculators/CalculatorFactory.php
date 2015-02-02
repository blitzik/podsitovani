<?php

namespace App\Subnetting\Model\Calculators;

	use Nette\Object;
	use App\Subnetting\Model\Calculators;

	class CalculatorFactory extends Object
	{
		/**
		 * @param Parameters $parameters
		 * @return mixed
		 */
		public function createCalculator(Parameters $parameters)
		{
			$parameterName = $parameters->getReflection()->getShortName();
			$calculatorName = preg_replace('~([A-Z]+)Parameters$~', '$1Calculator', $parameterName);

			$calculator = 'App\Subnetting\Model\Calculators\\' . $calculatorName;

			return new $calculator($parameters);
		}
	}