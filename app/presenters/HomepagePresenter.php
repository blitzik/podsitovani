<?php

namespace App\Subnetting\Presenters;

use Nette\Application\UI\Form,
    App\Subnetting\Model,
    App\Subnetting\Exceptions\LogicExceptions;

	class HomepagePresenter extends BasePresenter
	{
		/**
		 *
		 * @var \App\Subnetting\Model\Network
		 */
		private $network;



		public function renderDefault()
		{
			$this->template->network = $this->network;
		}

		protected function createComponentNetworkInfo()
		{
			$ni = new Model\Components\NetworkInfo($this->network);

			return $ni;
		}

		protected function createComponentNetworkForm()
		{
			$form = $this->calculatorFormFactory->create(30);

			$form['send']->caption = 'Zobrazit';

			unset($form['mask2'], $form['hosts']);

			$form->onSuccess[] = $this->processSubmit;

			return $form;
		}

		public function processSubmit(Form $form)
		{
			$values = $form->getValues();

			try {
				$this->network = new Model\Network(new Model\IpAddress($values['ip']),
											new Model\SubnetMask($values['mask']));

			} catch (LogicExceptions\InvalidIpAddressException $ip) {

				$form->addError('IP adresa nemá platný formát.');
				return;
			}/* catch (LogicExceptions\InvalidSubnetMaskFormatException $sm) {

				$this->flashMessage('Maska podsítě nemá platný formát.', 'errors');
				return;
			} catch (LogicExceptions\InvalidPrefixException $ipe) {

				$this->flashMessage('Prefix nemá platný formát.', 'errors');
				return;
			} catch (LogicExceptions\PrefixOutOfRangeException $p) {

				$this->flashMessage('Prefix lze zadat pouze v rozmezí 1 - 30', 'errors');
				return;
			} catch (LogicExceptions\SpecialSubnetMaskException $sm) {

				$link = $this->link('Mask:default');

				$this->flashMessage('Tuto masku <a href="' .$link. '">nelze využít</a> pro podsíťování.', 'errors');
				return;
			}*/
		}

	}