<?php

namespace WTS_EAE\Modules\PieChart;

use WTS_EAE\Base\Module_Base;

class Module extends Module_Base {

	public function get_widgets() {
		return [
			'PieChart',
		];
	}

	public function get_name() {
		return 'eae-pie-chart';
	}

}
