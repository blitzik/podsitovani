<?php

namespace App\Subnetting\Presenters;

use \Nette\Application\UI\Form,
	App\Subnetting\Model,
	App\Subnetting\Model\Calculators,
	App\Subnetting\Exceptions\LogicExceptions,
	App\Subnetting\Model\Components\NetworkInfoControl;

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
	 	* @var Model\Components\ISubnetworksControlFactory
		* @inject
	 	*/
		public $subnetworksControlFactory;


		/**
		 * @var Calculators\Parameters
		 */
		private $parameters;

		public function actionCalc()
		{
			if ($this->session->hasSection(self::SESSION_SECTION)) {

				$vlsm = $this->session->getSection(self::SESSION_SECTION);

				$this['calculatorForm']['ip']->setDefaultValue($vlsm->parameters->getNetwork()->getIpAddress());
				$this['calculatorForm']['mask']->setDefaultValue($vlsm->parameters->getNetwork()->getSubnetMask()->getPrefix());
				$this['calculatorForm']['hosts']->setDefaultValue($vlsm->hosts);

				$this->parameters = $vlsm->parameters;
			}
		}

		public function renderCalc()
		{
			$this->template->isSet = isset($this->parameters) ? TRUE : FALSE;
		}

		public function createComponentSubnetworks()
		{
		    $subnetworks = $this->subnetworksControlFactory->create($this->parameters);

			return $subnetworks;
		}

		protected function createComponentNetworkInfo()
		{
			$networkInfo = new NetworkInfoControl($this->parameters->getNetwork());

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
			if ($this->session->hasSection(self::SESSION_SECTION)) {
				$this->session->getSection(self::SESSION_SECTION)->remove();
				unset($this['subnetworks']['paginator']);

				$this->flashMessage('Kalkulátor byl úspěšně vyresetován.', 'success');
			}

			$this->redirect('this');
		}

		public function processSubmit(Form $form)
		{
			$values = $form->getValues();

			try {

				$parameters = new Calculators\VLSMParameters($values->ip, $values->mask, $values->hosts);

				$vlsm = $this->session->getSection(self::SESSION_SECTION);

				$vlsm->parameters = $parameters;
				$vlsm->hosts = $values->hosts;

				$vlsm->setExpiration(0);

				$this->redirect('this');

			} catch (LogicExceptions\InvalidIpAddressException $ip) {

				$form->addError('IP adresa nemá platný formát.');
				return;
			} catch (LogicExceptions\InvalidHostsFormatException $ihf) {

				$form->addError('Neplatný formát zadaných hostů. Lze zadávat pouze přirozená čísla.');
				return;
			} catch (LogicExceptions\InvalidNumberOfHostsException $inoh) {

				$form->addError('Požadovaný počet hostů přesahuje rozsah adres IPv4.');
				return;
			}
		}
	}