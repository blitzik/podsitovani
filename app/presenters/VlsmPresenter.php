<?php

namespace App\Subnetting\Presenters;

use \Nette\Application\UI\Form,
	App\Subnetting\Model,
	App\Subnetting\Model\Calculators,
	App\Subnetting\Exceptions\LogicExceptions,
	App\Subnetting\Model\Components\NetworkInfoControl;
use Tracy\Debugger;

class VlsmPresenter extends CalculatorPresenter
	{
		const SESSION_SECTION = 'vlsm';

		/**
		 *
		 * @var Model\Factories\Networks\NetworkFactory
		 * @inject
		 */
		public $networkFactory;

		/**
		 *
		 * @var Calculators\VLSMCalculator
		 */
		private $vlsmCalculator;

		private $results;

		public function actionCalc()
		{
			if ($this->session->hasSection(self::SESSION_SECTION)) {

				$vlsm = $this->session->getSection(self::SESSION_SECTION);

				$this['calculatorForm']['ip']->setDefaultValue($vlsm->calculator->getNetwork()->getIpAddress());
				$this['calculatorForm']['mask']->setDefaultValue($vlsm->calculator->getNetwork()->getSubnetMask()->getPrefix());
				$this['calculatorForm']['hosts']->setDefaultValue($vlsm->hosts);

				$this->vlsmCalculator = $vlsm->calculator;
			}
		}

		public function renderCalc()
		{
			$this->template->calculator = $this->vlsmCalculator;
		}

		public function createComponentSubnetworks()
		{
		    $subnetworks = new Model\Components\SubnetworksControl($this->vlsmCalculator);

			return $subnetworks;
		}

		protected function createComponentNetworkInfo()
		{
			$networkInfo = new NetworkInfoControl($this->vlsmCalculator->getNetwork());

			return $networkInfo;
		}

		protected function createComponentCalculatorForm()
		{
			$form = $this->calculatorFormFactory->create(30);

			$form->addSubmit('reset', 'Reset')
					->setValidationScope(FALSE)
					->onClick[] = $this->processReset;

			$form->onSuccess[] = $this->processSubmit;

			$form->getElementPrototype()->id = 'calcForm';

			unset($form['mask2']);

			return $form;
		}

		public function processReset(\Nette\Forms\Controls\Button $form)
		{
			$this->session->getSection(self::SESSION_SECTION)->remove();
			unset($this['subnetworks']['paginator']);

			$this->flashMessage('Kalkulátor byl úspěšně vyresetován.', 'success');
			$this->redirect('this');
		}

		public function processSubmit(Form $form)
		{
			$values = $form->getValues();

			try {
				$network = $this->networkFactory->createNetwork($values['ip'], $values['mask']);

				$VLSMCalculator = new Calculators\VLSMCalculator($network, $values['hosts']);

				$vlsm = $this->session->getSection(self::SESSION_SECTION);

				$vlsm->calculator = $VLSMCalculator;
				$vlsm->hosts = $values['hosts'];

				$vlsm->setExpiration(0);

				$this->redirect('this');

			} catch (LogicExceptions\InvalidIpAddressException $ip) {

				$form->addError('IP adresa nemá platný formát.');
				return;
			} catch (LogicExceptions\InvalidNumberOfHostsException $inoh) {

				$form->addError('Neplatný formát zadaných hostů');
				return;
			}
		}

	}