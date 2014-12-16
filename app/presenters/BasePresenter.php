<?php

namespace App\Subnetting\Presenters;

use App\Subnetting\Model\Factories\Forms\CalculatorFormFactory;

	abstract class BasePresenter extends \Nette\Application\UI\Presenter
	{
		/**
		 *
		 * @var Forms\CalculatorFormFactory
		 */
		protected $calculatorFormFactory;

		protected function startup()
		{
			parent::startup();

			$this->calculatorFormFactory = new CalculatorFormFactory();
		}
	}
