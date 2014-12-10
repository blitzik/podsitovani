<?php

namespace App\Subnetting\Model\Factories\Forms;

use Nette\Application\UI\Form;

	class CalculatorFormFactory
	{
		/**
		 * @return Form
		 */
		public function create()
		{
			$form = new Form();

			$form->addText('ip', 'IP Adresa:', 11, 16)
					->setRequired('Vyplňte IP adresu.');

			$form->addText('mask', 'Maska/Prefix:', 11, 16)
					->setRequired('Vyplňte Prefix nebo Masku podsítě.');

			$form->addText('hosts', 'Počet hostů:', 29)
					->setRequired('Uveďtě hosty alespoň pro jednu podsíť.');

			$form->addSubmit('send', 'Spočítat');

			return $form;
		}

	}