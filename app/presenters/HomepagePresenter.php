<?php

namespace App\Presenters;

use \Nette\Application\UI\Form;

	class HomepagePresenter extends BasePresenter
	{
		/**
		 *
		 * @var \Model\Network
		 */
		private $network;

		public function actionDefault()
		{

		}

		public function renderDefault()
		{
			/*$ip = new \Model\IpAddress('172.32.0.0');
			$mask = new \Model\SubnetMask('255.255.128.0');
			$network = new \Model\Network($ip, $mask);*/

			$this->template->network = $this->network;
		}

		protected function createComponentCalculatorForm()
		{
			$form = new Form();

			$form->addText('ip', 'IP Adresa:', 11, 16)
					->setRequired('Vyplňte IP adresu.');

			$form->addText('mask', 'Maska/Prefix:', 11, 16)
					->setRequired('Vyplňte Prefix nebo Masku podsítě.');

			$form->addText('hosts', 'Počet hostů:', 29)
					->setRequired('Uveďtě hosty alespoň pro jednu podsíť.');

			$form->addSubmit('send', 'Spočítat');

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

					$VLSMNetwork = new \Model\VLSMNetworks($network, $values['hosts']);

					$this->network = $VLSMNetwork;

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