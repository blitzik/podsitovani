<?php

namespace App\Subnetting\Presenters;

use \Nette\Application\UI\Form,
	\App\Subnetting\Model,
	\App\Subnetting\Model\Components,
	\App\Subnetting\Model\Calculators,
	\App\Subnetting\Exceptions\LogicExceptions,
	App\Subnetting\Model\Utils\IP;

	class VlsmPresenter extends CalculatorPresenter
	{
		/**
		 *
		 * @var Calculators\VLSMCalculator
		 */
		private $vlsmCalculator;

		private $results;

		const SESSION_SECTION = 'vlsm';

		public function actionCalc()
		{
			if ($this->session->hasSection(self::SESSION_SECTION)) {

				$vlsm = $this->session->getSection(self::SESSION_SECTION);

				$this['calculatorForm']['ip']->setDefaultValue($vlsm->ip);
				$this['calculatorForm']['mask']->setDefaultValue($vlsm->mask);
				$this['calculatorForm']['hosts']->setDefaultValue($vlsm->hosts);

				$network = new Model\Network(new Model\IpAddress($vlsm->ip), new Model\SubnetMask($vlsm->mask));
				$this->vlsmCalculator = new Calculators\VLSMCalculator($network, $vlsm->hosts);

				$paginator = $this['paginator']->getPaginator();
				$paginator->setItemCount(count($this->vlsmCalculator->getNetworkHosts()));
				$this->results = $this->vlsmCalculator->getSubnetworks($paginator->getOffset(), $paginator->getLength());
			}
		}

		public function renderCalc()
		{
			$this->template->calculator = $this->vlsmCalculator;
			$this->template->results = $this->results;
		}

		protected function createComponentPaginator()
		{
			$vp = new \Components\VisualPaginator(TRUE);
			$vp->getPaginator()->setItemsPerPage(10);

			return $vp;
		}

		protected function createComponentNetworkInfo()
		{
			$networkInfo = new Components\NetworkInfo($this->vlsmCalculator->getNetwork());

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
			$this->flashMessage('Kalkulátor byl úspěšně vyresetován.', 'success');
			$this->redirect('this');
		}

		public function processSubmit(Form $form)
		{
			$values = $form->getValues();

			try {
				$network = new Model\Network(new Model\IpAddress($values['ip']),
										new Model\SubnetMask($values['mask']));

				$VLSMCalculator = new Calculators\VLSMCalculator($network, $values['hosts']);

				$vlsm = $this->session->getSection(self::SESSION_SECTION);

				$vlsm->ip = $values['ip'];
				$vlsm->mask = $values['mask'];
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