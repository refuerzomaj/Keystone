<?php
namespace Essential\Restate\Front;

use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Front\Loader\TemplateHooks;
use Essential\Restate\Front\Provider\ListingProvider;
use Essential\Restate\Front\Provider\Query;
use Essential\Restate\Front\Models\Listingsaction;
use Essential\Restate\Front\Session\Session;
use Essential\Restate\Front\Purchase\Cart\Cart;
use Essential\Restate\Front\Purchase\Cart\Fees;
use Essential\Restate\Front\Purchase\Tax\Tax;
use Essential\Restate\Front\Error\Error;
use Essential\Restate\Front\Purchase\Gateways\Gateways;
use Essential\Restate\Front\Purchase\Checkout\Checkout;
use Essential\Restate\Front\Country\Country;
use Essential\Restate\Front\Purchase\Discount\Discount;
use Essential\Restate\Front\Purchase\Discount\DiscountAction;
use Essential\Restate\Front\Purchase\Gateways\PaypalStandard\PaypalStandard;
use Essential\Restate\Front\Purchase\Gateways\Stripe\Stripe;
use Essential\Restate\Front\Purchase\Gateways\Manual;

class Front {

	use Traitval;

	public $provider;
	public $listingsaction;
	public $error;
	public $template_hooks;
	public $query;
	public $session;
	public $checkout;
	public $gateways;
	public $cart;
	public $fees;
	public $tax;
	public $tokenizer;
	public $country;
	public $discount;
	public $paypalstandard;
	public $manual;

	protected function initialize() {
		$this->define_constants();
		$this->error            = Error::getInstance();
		$this->template_hooks   = TemplateHooks::getInstance();
		$this->listing_provider = ListingProvider::getInstance();
		$this->paypalstandard   = PaypalStandard::getInstance();
		$this->Stripe           = Stripe::getInstance();
		$this->listingsaction   = Listingsaction::getInstance();
		$this->query            = Query::getInstance();
		$this->session          = Session::getInstance();
		$this->gateways         = Gateways::getInstance();
		$this->checkout         = Checkout::getInstance();
		$this->fees             = Fees::getInstance();
		$this->tax              = Tax::getInstance();
		$this->country          = Country::getInstance();
		$this->cart             = Cart::getInstance();
		$this->discount         = discount::getInstance();
		$this->discountaction   = DiscountAction::getInstance();
		$this->manual           = Manual::getInstance();
	}

	private function define_constants() {
		define( 'WPERESDS_FRONT_TEMPLATE_DIR', WPERESDS_TEMPLATES_DIR . '/frontend' );
	}
}
