<?php
namespace Essential\Restate\Admin\Settings\Payment;

use Essential\Restate\Admin\Settings\Payment\Paymentlist;

use Essential\Restate\Front\Purchase\Payments\Clpayment;
use Essential\Restate\Common\Customer\Customer;

use Essential\Restate\Traitval\Traitval;

class Payment {

	use Traitval;

	public function __construct() {
		add_filter( 'admin_title', array( $this, 'cl_view_order_details_title' ), 10, 2 );
		add_filter( 'get_edit_post_link', array( $this, 'cl_override_edit_post_for_payment_link' ), 10, 3 );
	}

	function cl_payment_history_page() {
		$cl_payment = get_post_type_object( 'cl_payment' );

		if ( isset( $_GET['view'] ) && 'view-order-details' == $_GET['view'] ) {
			$payment_id = absint( cl_sanitization( $_GET['id'] ) );
			$payment    = new Clpayment( $payment_id );

			// Sanity check... fail if purchase ID is invalid
			$payment_exists = $payment->ID;
			if ( empty( $payment_exists ) ) {
				die( __( 'The specified ID does not belong to a payment. Please try again', 'essential-wp-real-estate' ) );
			}
			$args                   = array();
			$args['payment_id']     = $payment_id;
			$args['number']         = $payment->number;
			$args['payment_meta']   = $payment->get_meta();
			$args['transaction_id'] = esc_attr( $payment->transaction_id );
			$args['cart_items']     = $payment->cart_details;
			$args['user_id']        = $payment->user_id;
			$args['payment_date']   = strtotime( $payment->date );
			$args['unlimited']      = $payment->has_unlimited_listing;
			$args['user_info']      = cl_get_payment_meta_user_info( $payment_id );
			$args['address']        = $payment->address;
			$args['gateway']        = $payment->gateway;
			$args['currency_code']  = $payment->currency;
			$args['customer']       = new Customer( $payment->customer_id );
			$args['payment']        = $payment;

			cl_get_template( 'order_details.php', $args, '', WPERESDS_TEMPLATES_DIR . '/admin/' );
		} elseif ( isset( $_GET['page'] ) && 'cl-payment-history' == $_GET['page'] ) {

			$payments_table = new Paymentlist();
			$payments_table->prepare_items();
			?>
			<div class="wrap">
				<h1><?php echo esc_html( $cl_payment->labels->menu_name ); ?></h1>
				<?php do_action( 'cl_payments_page_top' ); ?>
				<form id="cl-payments-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=cl_cpt&page=cl-payment-history' ); ?>">
					<input type="hidden" name="post_type" value="cl_cpt" />
					<input type="hidden" name="page" value="cl-payment-history" />

					<?php $payments_table->views(); ?>

					<?php $payments_table->advanced_filters(); ?>

					<?php $payments_table->display(); ?>
				</form>
				<?php do_action( 'cl_payments_page_bottom' ); ?>
			</div>
			<?php
		}
	}


	/**
	 * Payment History admin titles
	 *
	 * @since 1.6
	 *
	 * @param $admin_title
	 * @param $title
	 * @return string
	 */
	function cl_view_order_details_title( $admin_title, $title ) {
		if ( 'listing_page_cl-payment-history' != get_current_screen()->base ) {
			return $admin_title;
		}

		if ( ! isset( $_GET['cl-action'] ) ) {
			return $admin_title;
		}

		switch ( $_GET['cl-action'] ) :

			case 'view-order-details':
				$title = __( 'View Order Details', 'essential-wp-real-estate' ) . ' - ' . $admin_title;
				break;
			case 'edit-payment':
				$title = __( 'Edit Payment', 'essential-wp-real-estate' ) . ' - ' . $admin_title;
				break;
			default:
				$title = $admin_title;
				break;
		endswitch;

		return $title;
	}


	/**
	 * Intercept default Edit post links for payments and rewrite them to the View Order Details screen
	 *
	 * @since 1.8.3
	 *
	 * @param $url
	 * @param $post_id
	 * @param $context
	 * @return string
	 */
	function cl_override_edit_post_for_payment_link( $url, $post_id, $context ) {

		$post = get_post( $post_id );
		if ( ! $post ) {
			return $url;
		}

		if ( 'cl_payment' != $post->post_type ) {
			return $url;
		}

		$url = admin_url( 'edit.php?post_type=cl_cpt&page=cl-payment-history&view=view-order-details&id=' . esc_attr( $post_id ) );

		return $url;
	}
}
