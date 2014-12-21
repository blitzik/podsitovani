<?php

namespace App\Subnetting\Model\Factories\Forms;

use Nette\Application\UI\Form;

	class CalculatorFormFactory extends \Nette\Object
	{
		/**
		 * @return Form
		 */
		public function create($mask = NULL, $mask2 = NULL)
		{
			$form = new Form();

			$form->addText('ip', 'IP Adresa:', 11, 16)
					->setRequired('Vyplňte IP adresu.')
					->getControlPrototype()->class = 'ipaddress';

			$form->addSelect('mask', 'Maska:', $this->masksForSelect($mask))
					->setPrompt('Vyberte masku')
					->setRequired('Zvolte masku podsítě')
					->getControlPrototype()->class = 'subnetmask';

			$form->addSelect('mask2', 'Rozdělení dle masky:', $this->masksForSelect($mask2))
					->setPrompt('Vyberte masku')
					//->setRequired('Zvolte masku podsítě')
					->getControlPrototype()->class = 'subnetmask';

			$form->addText('hosts', 'Počet hostů:', 35)
					->setRequired('Uveďtě hosty alespoň pro jednu podsíť.')
					->getControlPrototype()->class = 'hosts';

			$form->addSubmit('send', 'Spočítat');

			return $form;
		}

		private function masksForSelect($start)
		{
			if ($start == NULL) return NULL;

			$masks = array();
			for ($i = $start; $i >= 1; $i--) {
				$masks[$i] = $this->prepareMask($i);
			}

			return $masks;
		}

		public function maskForCIDR($start)
		{
			if ($start == NULL) return NULL;

			$masks = array();
			for ($i = $start + 1; $i <= 30; $i++) {
				$masks[$i] = $this->prepareMask($i);
			}

			krsort($masks);

			return $masks;
		}

		private function prepareMask($prefix)
		{
			$mask = new \App\Subnetting\Model\SubnetMask($prefix);

			return ('/' .$mask->getPrefix(). ' [' .$mask->getAddress(). ']');
		}

	}