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
		 *
		 * @var Calculators\CIDRCalculator
		 */
		private $cidrCalculator;

		private $results;

		public function actionCalc()
		{
			if ($this->session->hasSection(self::SESSION_SECTION)) {

				$cidr = $this->session->getSection(self::SESSION_SECTION);

				$this['calculatorForm']['ip']->setDefaultValue($cidr->ip);
				$this['calculatorForm']['mask']->setDefaultValue($cidr->mask);
				$this['calculatorForm']['mask2']->setDefaultValue($cidr->mask2);

				$this->cidrCalculator = new Calculators\CIDRCalculator(new Model\IpAddress($cidr->ip),
																		new Model\SubnetMask($cidr->mask),
																		new Model\SubnetMask($cidr->mask2));

				$paginator = $this['paginator']->getPaginator();
				$paginator->setItemCount($this->cidrCalculator->getNumberOfSubNetworks());

				$this->results = $this->cidrCalculator->calculateSubnets($paginator->getOffset(), $paginator->getLength());

			}
		}

		public function renderCalc()
		{
			$this->template->_form = $this['calculatorForm'];
			$this->template->calculator = $this->cidrCalculator;
			$this->template->results = $this->results;
		}

		protected function createComponentPaginator()
		{
			$vp = new \Components\VisualPaginator(TRUE);
			$vp->getPaginator()->setItemsPerPage(15);

			return $vp;
		}

		protected function createComponentNetworkInfo()
		{
			$networkInfo = new Components\NetworkInfo($this->cidrCalculator->getNetwork());

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
			$this->session->getSection(self::SESSION_SECTION)->remove();
			$this->flashMessage('Kalkulátor byl úspěšně vyresetován.', 'success');
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
				$cidrCalculator = new Calculators\CIDRCalculator(new Model\IpAddress($values['ip']),
																new Model\SubnetMask($values['mask']),
																new Model\SubnetMask($values['mask2']));

				$cidr = $this->session->getSection(self::SESSION_SECTION);

				$cidr->ip = $values['ip'];
				$cidr->mask = $values['mask'];
				$cidr->mask2 = $values['mask2'];

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