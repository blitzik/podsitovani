<?php

namespace App\Subnetting\Presenters;

use \Nette\Application\UI\Form,
	\App\Subnetting\Model,
	\App\Subnetting\Model\Factories,
	\App\Subnetting\Model\Components,
	\App\Subnetting\Model\Calculators,
	\App\Subnetting\Exceptions\LogicExceptions;

	class VlsmPresenter extends BasePresenter
	{
		/**
		 *
		 * @var \App\Subnetting\Model\Calculators\VLSMCalculator
		 */
		private $vlsmCalculator;

		public function renderDefault()
		{
			$this->template->calculator = $this->vlsmCalculator;
		}

		protected function createComponentNetworkInfo()
		{
			$networkInfo = new Components\NetworkInfo($this->vlsmCalculator->getNetwork());

			return $networkInfo;
		}

		protected function createComponentCalculatorForm()
		{
			$factory = new Factories\Forms\CalculatorFormFactory();

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
					$ipAddress = new Model\IpAddress($ip);
					$subnetMask = new Model\SubnetMask($mask);

					$network = new Model\Network($ipAddress, $subnetMask);

					$VLSMCalculator = new Calculators\VLSMCalculator($network, $values['hosts']);

					$this->vlsmCalculator = $VLSMCalculator;

			} catch (LogicExceptions\InvalidIpAddressException $ip) {

				$this->flashMessage('IP adresa, kterou jste zadali, nemá platný formát.', 'errors');
				return;
			} catch (LogicExceptions\InvalidSubnetMaskFormatException $sm) {

				$this->flashMessage('Maska podsítě nemá platný formát.', 'errors');
				return;
			} catch (LogicExceptions\InvalidPrefixException $ipe) {

				$this->flashMessage('Prefix nemá platný formát.', 'errors');
				return;
			} catch (LogicExceptions\PrefixOutOfRangeException $p) {

				$this->flashMessage('Prefix lze zadat pouze v rozmezí 1 - 30', 'errors');
				return;
			} catch (LogicExceptions\InvalidNumberOfHostsException $inoh) {

				$this->flashMessage('Neplatný formát zadaných hostů', 'errors');
				return;
			} catch (LogicExceptions\SpecialSubnetMaskException $sm) {

				$link = $this->link('Mask:default');

				$this->flashMessage('Tuto masku <a href="' .$link. '">nelze využít</a> pro podsíťování.', 'errors');
				return;
			}

		}

	}