<?php

namespace App\Subnetting\Presenters;

use \Nette\Application\UI\Form,
	\App\Subnetting\Model,
	\App\Subnetting\Model\Components,
	\App\Subnetting\Model\Calculators,
	\App\Subnetting\Exceptions\LogicExceptions;

	class VlsmPresenter extends BasePresenter
	{
		/**
		 *
		 * @var Calculators\VLSMCalculator
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
			$form = $this->calculatorFormFactory->create(30);

			$form->onSuccess[] = $this->processSubmit;

			$form->getElementPrototype()->id = 'calcForm';

			unset($form['mask2']);

			return $form;
		}

		public function processSubmit(Form $form)
		{
			$values = $form->getValues();

			try {
				$network = new Model\Network(new Model\IpAddress($values['ip']),
										new Model\SubnetMask($values['mask']));

				$VLSMCalculator = new Calculators\VLSMCalculator($network, $values['hosts']);

				$this->vlsmCalculator = $VLSMCalculator;

			} catch (LogicExceptions\InvalidIpAddressException $ip) {

				$form->addError('IP adresa nemá platný formát.');
				return;
			/*} catch (LogicExceptions\InvalidSubnetMaskFormatException $sm) {

				$this->flashMessage('Maska podsítě nemá platný formát.', 'errors');
				return;
			} catch (LogicExceptions\InvalidPrefixException $ipe) {

				$this->flashMessage('Prefix nemá platný formát.', 'errors');
				return;
			} catch (LogicExceptions\PrefixOutOfRangeException $p) {

				$this->flashMessage('Prefix lze zadat pouze v rozmezí 1 - 30', 'errors');
				return;*/
			} catch (LogicExceptions\InvalidNumberOfHostsException $inoh) {

				$form->addError('Neplatný formát zadaných hostů');
				return;
			}/* catch (LogicExceptions\SpecialSubnetMaskException $sm) {

				$link = $this->link('Mask:default');

				$this->flashMessage('Tuto masku <a href="' .$link. '">nelze využít</a> pro podsíťování.', 'errors');
				return;
			}*/

		}

	}