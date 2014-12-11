<?php

namespace App\Subnetting\Presenters;

use App\Subnetting\Model;

	class MaskPresenter extends BasePresenter
	{
		/**
		 *
		 * @var array Array of SubnetMasks
		 */
		private $subnetMasks;

		public function actionDefault()
		{
			for ($i = 30; $i >= 1; $i--) {
				$this->subnetMasks[] = new Model\SubnetMask($i);
			}

		}

		public function renderDefault()
		{
			$this->template->masks = $this->subnetMasks;
		}

	}