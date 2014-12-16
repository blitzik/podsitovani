<?php

namespace App\Subnetting\Presenters;

use App\Subnetting\Model,
	App\Subnetting\Model\Calculators,
	App\Subnetting\Exceptions\LogicExceptions,
	\Nette\Application\UI\Form;

	class CidrPresenter extends BasePresenter
	{
		/**
		 *
		 * @var Calculators\CIDRCalculator
		 */
		private $cidrCalculator;

		public function renderDefault($page)
		{
			$this->template->_form = $this['calculatorForm'];

			$this->template->calculator = $this->cidrCalculator;
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

			$form->onSuccess[] = $this->processSubmit;

			unset($form['hosts']);

			return $form;
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
				$cidrCalculator = new Calculators\CIDRCalculator(new Model\IpAddress($values['ip']),
														new Model\SubnetMask($values['mask']),
														new Model\SubnetMask($values['mask2']));

				$this->cidrCalculator = $cidrCalculator;

			} catch (LogicExceptions\InvalidIpAddressException $ip) {

				$form->addError('IP adresa nemá platný formát.');
				return;
			} catch (LogicExceptions\CIDRSubnetMaskRangeException $cidr) {

				$form->addError('Nelze zasahovat do síťové části IP adresy.');
				return;
			}


		}
	}