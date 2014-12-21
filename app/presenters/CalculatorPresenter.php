<?php

namespace App\Subnetting\Presenters;

use App\Subnetting\Model\Factories\Forms\CalculatorFormFactory;

	abstract class CalculatorPresenter extends BasePresenter
	{
		/**
		 *
		 * @var CalculatorFormFactory
		 * @inject
		 */
		public $calculatorFormFactory;
	}