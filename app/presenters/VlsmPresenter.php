<?php

namespace App\Presenters;

use \Nette\Application\UI\Form;

	class VlsmPresenter extends BasePresenter
	{
		/**
		 *
		 * @var \Model\VLSMCalculator
		 */
		private $vlsmNetwork;

		public function renderDefault()
		{
			/*$ip = new \Model\IpAddress('199.190.111.0');
			$mask = new \Model\SubnetMask('/24');
			$network = new \Model\Network($ip, $mask);
			$vlsm = new \Model\VLSMCalculator($network, '63,8,5,24,2,2,2');

			\Tracy\Debugger::dump($vlsm->getAllResults());*/

			$this->template->network = $this->vlsmNetwork;
		}

		protected function createComponentNetworkInfo()
		{
			$networkInfo = new \Model\Components\NetworkInfo($this->vlsmNetwork->getNetwork());

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

					$VLSMNetwork = new \Model\VLSMCalculator($network, $values['hosts']);

					$this->vlsmNetwork = $VLSMNetwork;

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