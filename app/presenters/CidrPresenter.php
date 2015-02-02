<?php

namespace App\Subnetting\Presenters;

use App\Subnetting\Model,
    App\Subnetting\Model\Calculators,
    App\Subnetting\Exceptions\LogicExceptions,
    \Nette\Application\UI\Form,
    App\Subnetting\Model\Components;

	class CidrPresenter extends CalculatorPresenter
	{
		const SESSION_SECTION = 'cidr';

		/**
		 * @var Model\Components\ISubnetworksControlFactory
		 * @inject
		 */
		public $subnetworksControlFactory;

		/**
		 *
		 * @var Calculators\Parameters
		 */
		private $parameters;

		public function actionCalc()
		{
			if ($this->session->hasSection(self::SESSION_SECTION)) {

				$cidr = $this->session->getSection(self::SESSION_SECTION);

				$this['calculatorForm']['ip']->setDefaultValue($cidr->parameters->getNetwork()->getIpAddress());
				$this['calculatorForm']['mask']->setDefaultValue($cidr->parameters->getSubnetMask()->getPrefix());
				$this['calculatorForm']['mask2']->setDefaultValue($cidr->parameters->getSubnetMask2()->getPrefix());

				$this->parameters = $cidr->parameters;
			}
		}

		public function renderCalc()
		{
			$this->template->_form = $this['calculatorForm'];
			$this->template->isSet = isset($this->parameters) ? TRUE : FALSE;
		}

		public function createComponentSubnetworks()
		{
			$subnetworks = $this->subnetworksControlFactory->create($this->parameters);

			return $subnetworks;
		}

		protected function createComponentNetworkInfo()
		{
			$networkInfo = new Components\NetworkInfoControl($this->parameters->getNetwork());

			return $networkInfo;
		}

		public function handleSecondMaskChange($value)
		{
			if ($value) {

				$items = $this->calculatorFormFactory->maskForCIDR($value);
				$this['calculatorForm']['mask2']->setPrompt('Vyberte masku')
				    							->setItems($items);
			} else {

				$this['calculatorForm']['mask2']->setPrompt('Vyberte masku')
				    							->setItems(array());
			}

			$this->redrawControl('secondMask');
		}

		protected function createComponentCalculatorForm()
		{
			$form = $this->calculatorFormFactory->create(29, 30);

			$form->addSubmit('reset', 'Reset')
			    	->setValidationScope(FALSE)
			    	->onClick[] = $this->processReset;

			$form->onSuccess[] = $this->processSubmit;

			$form->getElementPrototype()->id = 'calcForm';

			unset($form['hosts']);

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
			$values = $form->getHttpData();
			unset($values['send'], $values['do']);

			if ($values['mask2'] == NULL) {
				$form->addError('Vyberte druhou masku.');
				return;
			}

			try {
				$parameters = new Calculators\CIDRParameters($values['ip'], $values['mask'], $values['mask2']);

				$cidr = $this->session->getSection(self::SESSION_SECTION);

				$cidr->parameters = $parameters;

				$cidr->setExpiration(0);

				$this->redirect('this');

			} catch (LogicExceptions\InvalidIpAddressException $ip) {

				$form->addError('IP adresa nemá platný formát.');
				return;
			} catch (LogicExceptions\CIDRSubnetMaskRangeException $cidr) {

				$form->addError('Nelze zasahovat do síťové části IP adresy.');
				return;
			}
		}

	}