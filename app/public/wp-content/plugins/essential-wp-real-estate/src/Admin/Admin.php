<?php
namespace Essential\Restate\Admin;

use Essential\Restate\Admin\Menu\Menu;
use Essential\Restate\Admin\MetaBoxes\MetaBoxes;
use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Admin\Settings\Settings;
use Essential\Restate\Admin\Settings\Archive;
use Essential\Restate\Admin\Settings\Single;
use Essential\Restate\Front\Purchase\Discount\DiscountAction;
use Essential\Restate\Admin\Adminnotice\Adminnotice;

class Admin {

	use Traitval;

	public $menu_instance;
	public $settings_instances;
	public $listing_instance;
	public $archive_instance;
	public $discount_action;
	public $adminnotice;

	protected function initialize() {
		$this->define_constants();
		new MetaBoxes();
		$this->init_classes();
	}

	private function init_classes() {
		$this->menu_instance      = new Menu();
		$this->settings_instances = new Settings();
		$this->archive_instance   = new Archive();
		$this->single_instance    = new Single();
		$this->discount_action    = new DiscountAction();
		$this->adminnotice        = new Adminnotice();
	}
	private function define_constants() {
		define( 'WPERESDS_ADMIN_TEMPLATE_DIR', WPERESDS_TEMPLATES_DIR . '/admin' );
	}
}
