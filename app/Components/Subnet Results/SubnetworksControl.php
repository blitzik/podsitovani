<?php

namespace App\Subnetting\Model\Components;

use App\Subnetting\Exceptions\LogicExceptions\PrefixOutOfRangeException;
use App\Subnetting\Model\Calculators\CalculatorFactory;
use App\Subnetting\Model\Calculators\ICalculator;
use App\Subnetting\Model\Calculators\Parameters;
use Components\IPaginatorFactory;
use Nette\Application\UI\Control;

	class SubnetworksControl extends Control
	{
		/**
		 * @var ICalculator
		 */
		private $calculator;


		/**
		 * @var IPaginatorFactory
		 */
		private $paginatorFactory;


		public function __construct(Parameters $parameters, IPaginatorFactory $pf, CalculatorFactory $calculatorFactory)
		{
			$this->paginatorFactory = $pf;

			$this->calculator = $calculatorFactory->createCalculator($parameters);
		}

		public function createComponentPaginator()
		{
		    $vp = $this->paginatorFactory->create();
			$vp->getPaginator()->setItemsPerPage(15);

			return $vp;
		}

		public function render()
		{
			$template = $this->template;

			$template->setFile(__DIR__ . '/template.latte');

			$paginator = $this['paginator']->getPaginator();
			$paginator->setItemCount($this->calculator->getNumberOfSubnetworks());

			$template->calculator = $this->calculator;

			try {
					$template->results = $this->calculator->calculateSubnetworks($paginator->getOffset(), $paginator->getLength());

			} catch (PrefixOutOfRangeException $p) {
				$template->setFile(__DIR__ . '/error.latte');
			}

			//dump($this->calculator->getTotalNumberOfAddressesInBlocks());

			$template->render();
		}
	}