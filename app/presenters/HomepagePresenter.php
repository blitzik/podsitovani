<?php

namespace App\Presenters;

use Nette\Application\UI\Form;

	class HomepagePresenter extends BasePresenter
	{
		/**
		 *
		 * @var \Model\Network
		 */
		private $network;

		public function renderDefault()
		{
			$this->template->network = $this->network;
		}

		protected function createComponentNetworkInfo()
		{
			$ni = new \Model\Components\NetworkInfo($this->network);

			return $ni;
		}

		protected function createComponentNetworkForm()
		{
			$factory = new \Model\Forms\CalculatorFormFactory();

			$form = $factory->create();

			unset($form['hosts']);

			$form['send']->caption = 'Zobrazit';

			$form->onSuccess[] = $this->processSubmit;

			return $form;
		}

		public function processSubmit(Form $form)
		{
			$values = $form->getValues();

			try {
					$ipAddress = new \Model\IpAddress($values['ip']);
					$subnetMask = new \Model\SubnetMask($values['mask']);

					$network = new \Model\Network($ipAddress, $subnetMask);

					$this->network = $network;

			} catch (\LogicExceptions\InvalidIpAddressException $ip) {

				$this->flashMessage('IP adresa, kterou jste zadali, nemá platný formát.', 'errors');
				return;
			} catch (\LogicExceptions\InvalidSubnetMaskException $sm) {

				$this->flashMessage('Maska podsítě nemá platný formát.', 'errors');
				return;
			}
		}

	}