<?php

namespace App\Subnetting\Model\Components;

use App\Subnetting\Model\Calculators\ICalculator;
use Components\IPaginatorFactory;
use Components\VisualPaginator;
use Nette\Application\UI\Control;
use Nette\InvalidArgumentException;

	class SubnetworksControl extends Control
	{
		/**
		 * @var ICalculator|NULL
		 */
		private $calculator;


		/**
		 * @var IPaginatorFactory
		 */
		private $paginatorFactory;

		public function __construct(IPaginatorFactory $pf)
		{
			$this->paginatorFactory = $pf;
		}

		public function setCalculator($calculator)
		{
			if (!($calculator == NULL OR $calculator instanceof ICalculator)) {
				throw new InvalidArgumentException;
			}

			$this->calculator = $calculator;
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

			if ($this->calculator) {
				$paginator = $this['paginator']->getPaginator();
				$paginator->setItemCount($this->calculator->getNumberOfSubnetworks());
				$template->results = $this->calculator->calculateSubnetworks($paginator->getOffset(), $paginator->getLength());
			}

			$template->calculator = $this->calculator;

			$template->render();
		}
	}