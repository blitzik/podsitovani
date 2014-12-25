<?php

namespace App\Subnetting\Model\Components;

use Nette\Application\UI\Control,
	App\Subnetting\Model;

	class NetworkInfoControl extends Control
	{
		/**
		 *
		 * @var Model\Network
		 */
		private $network;

		public function __construct(Model\Network $network)
		{
			$this->network = $network;
		}

		public function render()
		{
			$template = $this->template;
			$template->setFile(__DIR__ . '/info.latte');

			$template->network = $this->network;

			$template->render();
		}

	}