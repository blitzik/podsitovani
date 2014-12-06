<?php

namespace App\Presenters;

use \Nette\Application\UI\Form;

	class VlsmPresenter extends BasePresenter
	{
		/**
		 *
		 * @var \Model\VLSMCalculator
		 */
		private $vlsmCalculator;

		public function renderDefault()
		{
			$this->template->calculator = $this->vlsmCalculator;
		}

		protected function createComponentNetworkInfo()
		{
			$networkInfo = new \Model\Components\NetworkInfo($this->vlsmCalculator->getNetwork());

			return $networkInfo;
		}

		protected function createComponentCalculatorForm()
		{
			$factory = new \Model\Forms\CalculatorFormFactory();

			$form = $factory->create();

			$form->onSuccess[] = $this->processSubmit;

			$form->getElementPrototype()->id = 'calcForm';

			return $form;
		}

		public function processSubmit(Form $form)
		{
			$values = $form->getValues();

			$ip = $values['ip'];
			$mask = $values['mask'];

			try {
					$ipAddress = new \Model\IpAddress($ip);
					$subnetMask = new \Model\SubnetMask($mask);

					$network = new \Model\Network($ipAddress, $subnetMask);

					$VLSMCalculator = new \Model\VLSMCalculator($network, $values['hosts']);

					$this->vlsmCalculator = $VLSMCalculator;

			} catch (\LogicExceptions\InvalidIpAddressException $ip) {

				$this->flashMessage('IP adresa, kterou jste zadali, nemá platný formát.', 'errors');
				return;
			} catch (\LogicExceptions\InvalidSubnetMaskException $sm) {

				$this->flashMessage('Maska podsítě nemá platný formát.', 'errors');
				return;
			} catch (\LogicExceptions\InvalidNumberOfHostsException $noh) {

				$this->flashMessage('Do hostů lze uvádět jen celá čísla větší než 0.', 'errors');
				return;
			}

		}

	}