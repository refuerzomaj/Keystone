<?php
namespace Essential\Restate\Common;

use Essential\Restate\Common\Actions\Actions;
use Essential\Restate\Common\PostTypes\PostTypes;
use Essential\Restate\Front\Loader\Sidebars;
use Essential\Restate\Common\Emails\Emails;
use Essential\Restate\Common\Emails\Emailtags;
use Essential\Restate\Common\Emails\Emailtemplate;
use Essential\Restate\Common\PostTypes\Assign;
use Essential\Restate\Common\Currencies\Currencies;
use Essential\Restate\Common\Ajax\Ajax;
use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Common\Options\Options;
use Essential\Restate\Common\Customer\Dbcustomer;
use Essential\Restate\Common\Customer\Customermeta;
use Essential\Restate\Common\Formatting\Formatting;
use Essential\Restate\Common\Roles\Roles;
use Essential\Restate\Common\User\User;

class Common {

	use Traitval;

	public $posttypes_instance;
	public $assign_sidebars;
	public $assign_instance;
	public $emails;
	public $ajax_action;
	public $loggin_user;
	public $options;
	public $currencies;
	public $customer;
	public $formatting;
	public $roles;
	public $emailtemplate;
	public $user;

	protected function initialize() {
		$this->init_hooks();
	}

	public function init_hooks() {
		$this->posttypes_instance = PostTypes::getInstance();
		$this->actions            = Actions::getInstance();
		$this->assign_sidebars    = Sidebars::getInstance();
		$this->assign_instance    = Assign::getInstance();
		$this->emails             = Emails::getInstance();
		$this->emailtags          = Emailtags::getInstance();
		$this->loggin_user        = wp_get_current_user();
		$this->options            = Options::getInstance();
		$this->currencies         = Currencies::getInstance();
		$this->dbcustomer         = Dbcustomer::getInstance();
		$this->customermeta       = Customermeta::getInstance();
		$this->formatting         = Formatting::getInstance();
		$this->ajax_action        = Ajax::getInstance();
		$this->roles              = Roles::getInstance();
		$this->emailtemplate      = Emailtemplate::getInstance();
		$this->user               = User::getInstance();
	}
}
