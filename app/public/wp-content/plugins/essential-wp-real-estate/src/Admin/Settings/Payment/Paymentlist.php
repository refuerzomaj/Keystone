<?php
namespace Essential\Restate\Admin\Settings\Payment;

use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Front\Purchase\Payments\Clpaymentsquery;
use Essential\Restate\Front\Purchase\Gateways\Gateways;
use Essential\Restate\Common\Customer\Customer;


class Paymentlist extends \WP_List_Table {

	use Traitval;

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.4
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 1.4.1
	 */
	public $base_url;

	/**
	 * Total number of payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $total_count;

	/**
	 * Total number of complete payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $complete_count;

	/**
	 * Total number of pending payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $pending_count;

	/**
	 * Total number of processing payments
	 *
	 * @var int
	 * @since 2.8
	 */
	public $processing_count;

	/**
	 * Total number of refunded payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $refunded_count;

	/**
	 * Total number of failed payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $failed_count;

	/**
	 * Total number of revoked payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $revoked_count;

	/**
	 * Total number of abandoned payments
	 *
	 * @var int
	 * @since 1.6
	 */
	public $abandoned_count;



	public function __construct() {
		 global $status, $page;
		parent::__construct();
		$this->get_payment_counts();
		$this->process_bulk_action();
		$this->base_url = admin_url( 'edit.php?post_type=cl_cpt&page=cl-payment-history' );
	}

	public function advanced_filters() {
		$start_date       = isset( $_GET['start-date'] ) ? cl_sanitization( $_GET['start-date'] ) : null;
		$end_date         = isset( $_GET['end-date'] ) ? cl_sanitization( $_GET['end-date'] ) : null;
		$status           = isset( $_GET['status'] ) ? cl_sanitization( $_GET['status'] ) : '';
		$callgateways     = new Gateways();
		$all_gateways     = $callgateways->cl_get_payment_gateways();
		$gateways         = array();
		$selected_gateway = isset( $_GET['gateway'] ) ? cl_sanitization( $_GET['gateway'] ) : 'all';

		if ( ! empty( $all_gateways ) ) {
			$gateways['all'] = __( 'All Gateways', 'essential-wp-real-estate' );

			foreach ( $all_gateways as $slug => $admin_label ) {
				$gateways[ $slug ] = $admin_label['admin_label'];
			}
		}

		/**
		 * Allow gateways that aren't registered the standard way to be displayed in the dropdown.
		 *
		 * @since 2.8.11
		 */
		$gateways = apply_filters( 'cl_payments_table_gateways', $gateways );
		?>
		<div id="cl-payment-filters">
			<span id="cl-payment-date-filters">
				<span>
					<label for="start-date"><?php _e( 'Start Date:', 'essential-wp-real-estate' ); ?></label>
					<input type="text" id="start-date" name="start-date" class="cl_datepicker" value="<?php echo esc_attr( $start_date ); ?>" placeholder="mm/dd/yyyy" />
				</span>
				<span>
					<label for="end-date"><?php _e( 'End Date:', 'essential-wp-real-estate' ); ?></label>
					<input type="text" id="end-date" name="end-date" class="cl_datepicker" value="<?php echo esc_attr( $end_date ); ?>" placeholder="mm/dd/yyyy" />
				</span>
			</span>
			<span id="cl-payment-gateway-filter">
				<?php
				if ( ! empty( $gateways ) ) {
					echo WPERECCP()->admin->settings_instances->cl_admin_select_callback(
						array(
							'options'          => $gateways,
							'name'             => 'gateway',
							'id'               => 'gateway',
							'selected'         => $selected_gateway,
							'show_option_all'  => false,
							'show_option_none' => false,
						)
					);
				}
				?>
			</span>
			<span id="cl-payment-after-core-filters">
				<?php do_action( 'cl_payment_advanced_filters_after_fields' ); ?>
				<input type="submit" class="button-secondary" value="<?php _e( 'Apply', 'essential-wp-real-estate' ); ?>" />
			</span>
			<?php if ( ! empty( $status ) ) : ?>
				<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>" />
			<?php endif; ?>
			<?php if ( ! empty( $start_date ) || ! empty( $end_date ) || 'all' !== $selected_gateway ) : ?>
				<a href="<?php echo admin_url( 'edit.php?post_type=cl_cpt&page=cl-payment-history' ); ?>" class="button-secondary"><?php _e( 'Clear Filter', 'essential-wp-real-estate' ); ?></a>
			<?php endif; ?>
			<?php do_action( 'cl_payment_advanced_filters_row' ); ?>
			<?php $this->search_box( __( 'Search', 'essential-wp-real-estate' ), 'cl-payments' ); ?>
		</div>

		<?php
	}

	/**
	 * Show the search field
	 *
	 * @since 1.4
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( cl_sanitization($_REQUEST['orderby']) ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( cl_sanitization($_REQUEST['order']) ) . '" />';
		}
		?>
		<p class="search-box">
			<?php do_action( 'cl_payment_history_search' ); ?>
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?><br />
		</p>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @since 1.4
	 * @return array $views All the views available
	 */
	public function get_views() {
		$current          = isset( $_GET['status'] ) ? cl_sanitization( $_GET['status'] ) : '';
		$total_count      = '&nbsp;<span class="count">(' . esc_html( $this->total_count ) . ')</span>';
		$complete_count   = '&nbsp;<span class="count">(' . esc_html( $this->complete_count ) . ')</span>';
		$pending_count    = '&nbsp;<span class="count">(' . esc_html( $this->pending_count ) . ')</span>';
		$processing_count = '&nbsp;<span class="count">(' . esc_html( $this->processing_count ) . ')</span>';
		$refunded_count   = '&nbsp;<span class="count">(' . esc_html( $this->refunded_count ) . ')</span>';
		$failed_count     = '&nbsp;<span class="count">(' . esc_html( $this->failed_count ) . ')</span>';
		$abandoned_count  = '&nbsp;<span class="count">(' . esc_html( $this->abandoned_count ) . ')</span>';
		$revoked_count    = '&nbsp;<span class="count">(' . esc_html( $this->revoked_count ) . ')</span>';

		$views = array(
			'all'        => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( array( 'status', 'paged' ) ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'essential-wp-real-estate' ) . $total_count ),
			'publish'    => sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					array(
						'status' => 'publish',
						'paged'  => false,
					)
				),
				$current === 'publish' ? ' class="current"' : '',
				__( 'Completed', 'essential-wp-real-estate' ) . $complete_count
			),
			'pending'    => sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					array(
						'status' => 'pending',
						'paged'  => false,
					)
				),
				$current === 'pending' ? ' class="current"' : '',
				__( 'Pending', 'essential-wp-real-estate' ) . $pending_count
			),
			'processing' => sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					array(
						'status' => 'processing',
						'paged'  => false,
					)
				),
				$current === 'processing' ? ' class="current"' : '',
				__( 'Processing', 'essential-wp-real-estate' ) . $processing_count
			),
			'refunded'   => sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					array(
						'status' => 'refunded',
						'paged'  => false,
					)
				),
				$current === 'refunded' ? ' class="current"' : '',
				__( 'Refunded', 'essential-wp-real-estate' ) . $refunded_count
			),
			'revoked'    => sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					array(
						'status' => 'revoked',
						'paged'  => false,
					)
				),
				$current === 'revoked' ? ' class="current"' : '',
				__( 'Revoked', 'essential-wp-real-estate' ) . $revoked_count
			),
			'failed'     => sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					array(
						'status' => 'failed',
						'paged'  => false,
					)
				),
				$current === 'failed' ? ' class="current"' : '',
				__( 'Failed', 'essential-wp-real-estate' ) . $failed_count
			),
			'abandoned'  => sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					array(
						'status' => 'abandoned',
						'paged'  => false,
					)
				),
				$current === 'abandoned' ? ' class="current"' : '',
				__( 'Abandoned', 'essential-wp-real-estate' ) . $abandoned_count
			),
		);

		return apply_filters( 'cl_payments_table_views', $views );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.4
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		 $columns = array(
			 'cb'       => '<input type="checkbox" />', // Render a checkbox instead of text
			 'ID'       => __( 'ID', 'essential-wp-real-estate' ),
			 'email'    => __( 'Email', 'essential-wp-real-estate' ),
			 'details'  => __( 'Details', 'essential-wp-real-estate' ),
			 'amount'   => __( 'Amount', 'essential-wp-real-estate' ),
			 'date'     => __( 'Date', 'essential-wp-real-estate' ),
			 'customer' => __( 'Customer', 'essential-wp-real-estate' ),
			 'status'   => __( 'Status', 'essential-wp-real-estate' ),
			 'gateway'  => __( 'Gateway', 'essential-wp-real-estate' ),
		 );

		 return apply_filters( 'cl_payments_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @since 1.4
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		$columns = array(
			'ID'     => array( 'ID', true ),
			'amount' => array( 'amount', false ),
			'date'   => array( 'date', false ),
		);
		return apply_filters( 'cl_payments_table_sortable_columns', $columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'ID';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.4
	 *
	 * @param array  $payment Contains all the data of the payment
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $payment, $column_name ) {
		switch ( $column_name ) {
			case 'amount':
				$amount = $payment->total;
				$amount = ! empty( $amount ) ? $amount : 0;
				$value  = WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $amount ), cl_get_payment_currency_code( $payment->ID ) );
				break;
			case 'date':
				$date  = strtotime( $payment->date );
				$value = date_i18n( get_option( 'date_format' ), $date );
				break;
			case 'status':
				$payment = get_post( $payment->ID );
				$value   = cl_get_payment_status( $payment, true );
				break;
			case 'details':
				$value = '<a href="' . add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=cl_cpt&page=cl-payment-history&view=view-order-details' ) ) . '">' . __( 'View Order Details', 'essential-wp-real-estate' ) . '</a>';
				break;
			default:
				$value = isset( $payment->$column_name ) ? $payment->$column_name : '';
				break;
		}
		return apply_filters( 'cl_payments_table_column', $value, $payment->ID, $column_name );
	}

	/**
	 * Render the Email Column
	 *
	 * @since 1.4
	 * @param array $payment Contains all the data of the payment
	 * @return string Data shown in the Email column
	 */
	public function column_email( $payment ) {

		$row_actions = array();

		$email = cl_get_payment_user_email( $payment->ID );

		// Add search term string back to base URL
		$search_terms = ( isset( $_GET['s'] ) ? trim( cl_sanitization( $_GET['s'] ) ) : '' );
		if ( ! empty( $search_terms ) ) {
			$this->base_url = add_query_arg( 's', $search_terms, $this->base_url );
		}

		if ( cl_is_payment_complete( $payment->ID ) && ! empty( $email ) ) {
			$row_actions['email_links'] = '<a href="' . add_query_arg(
				array(
					'cl-action'   => 'email_links',
					'purchase_id' => $payment->ID,
				),
				$this->base_url
			) . '">' . __( 'Resend Purchase Receipt', 'essential-wp-real-estate' ) . '</a>';
		}

		$row_actions['delete'] = '<a href="' . wp_nonce_url(
			add_query_arg(
				array(
					'cl-action'   => 'delete_payment',
					'purchase_id' => $payment->ID,
				),
				$this->base_url
			),
			'cl_payment_nonce'
		) . '">' . __( 'Delete', 'essential-wp-real-estate' ) . '</a>';

		$row_actions = apply_filters( 'cl_payment_row_actions', $row_actions, $payment );

		if ( empty( $email ) ) {
			$email = __( '(unknown)', 'essential-wp-real-estate' );
		}

		$value = $email . $this->row_actions( $row_actions );

		return apply_filters( 'cl_payments_table_column', $value, $payment->ID, 'email' );
	}

	/**
	 * Render the checkbox column
	 *
	 * @since 1.4
	 * @param array $payment Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	public function column_cb( $payment ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'payment',
			$payment->ID
		);
	}

	/**
	 * Render the ID column
	 *
	 * @since 2.0
	 * @param array $payment Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	public function column_ID( $payment ) {
		return cl_get_payment_number( $payment->ID );
	}

	/**
	 * Render the Customer Column
	 *
	 * @since 2.4.3
	 * @param array $payment Contains all the data of the payment
	 * @return string Data shown in the User column
	 */
	public function column_customer( $payment ) {

		$customer_id = cl_get_payment_customer_id( $payment->ID );

		if ( ! empty( $customer_id ) ) {
			$customer = new Customer( $customer_id );
			$value    = '<a href="' . esc_url( admin_url( "edit.php?post_type=listing&page=cl-customers&view=overview&id=$customer_id" ) ) . '">' . esc_html( $customer->name ) . '</a>';
		} else {
			$email = cl_get_payment_user_email( $payment->ID );
			$value = '<a href="' . esc_url( admin_url( "edit.php?post_type=listing&page=cl-payment-history&s=$email" ) ) . '">' . __( '(customer missing)', 'essential-wp-real-estate' ) . '</a>';
		}
		return apply_filters( 'cl_payments_table_column', $value, $payment->ID, 'user' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @since 1.4
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'                 => __( 'Delete', 'essential-wp-real-estate' ),
			'set-status-publish'     => __( 'Set To Completed', 'essential-wp-real-estate' ),
			'set-status-pending'     => __( 'Set To Pending', 'essential-wp-real-estate' ),
			'set-status-processing'  => __( 'Set To Processing', 'essential-wp-real-estate' ),
			'set-status-refunded'    => __( 'Set To Refunded', 'essential-wp-real-estate' ),
			'set-status-revoked'     => __( 'Set To Revoked', 'essential-wp-real-estate' ),
			'set-status-failed'      => __( 'Set To Failed', 'essential-wp-real-estate' ),
			'set-status-abandoned'   => __( 'Set To Abandoned', 'essential-wp-real-estate' ),
			'set-status-preapproval' => __( 'Set To Preapproval', 'essential-wp-real-estate' ),
			'set-status-cancelled'   => __( 'Set To Cancelled', 'essential-wp-real-estate' ),
			'resend-receipt'         => __( 'Resend Email Receipts', 'essential-wp-real-estate' ),
		);

		return apply_filters( 'cl_payments_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 *
	 * @since 1.4
	 * @return void
	 */
	public function process_bulk_action() {
		$ids    = isset( $_GET['payment'] ) ? cl_sanitization( $_GET['payment'] ) : false;
		$action = $this->current_action();

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		if ( empty( $action ) ) {
			return;
		}

		foreach ( $ids as $id ) {
			// Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() ) {
				cl_delete_purchase( $id );
			}

			if ( 'set-status-publish' === $this->current_action() ) {
				cl_update_payment_status( $id, 'publish' );
			}

			if ( 'set-status-pending' === $this->current_action() ) {
				cl_update_payment_status( $id, 'pending' );
			}

			if ( 'set-status-processing' === $this->current_action() ) {
				cl_update_payment_status( $id, 'processing' );
			}

			if ( 'set-status-refunded' === $this->current_action() ) {
				cl_update_payment_status( $id, 'refunded' );
			}

			if ( 'set-status-revoked' === $this->current_action() ) {
				cl_update_payment_status( $id, 'revoked' );
			}

			if ( 'set-status-failed' === $this->current_action() ) {
				cl_update_payment_status( $id, 'failed' );
			}

			if ( 'set-status-abandoned' === $this->current_action() ) {
				cl_update_payment_status( $id, 'abandoned' );
			}

			if ( 'set-status-preapproval' === $this->current_action() ) {
				cl_update_payment_status( $id, 'preapproval' );
			}

			if ( 'set-status-cancelled' === $this->current_action() ) {
				cl_update_payment_status( $id, 'cancelled' );
			}

			if ( 'resend-receipt' === $this->current_action() ) {
				// need fix
				cl_email_purchase_receipt( $id, false );
			}

			do_action( 'cl_payments_table_do_bulk_action', $id, $this->current_action() );
		}
	}

	/**
	 * Retrieve the payment counts
	 *
	 * @since 1.4
	 * @return void
	 */
	public function get_payment_counts() {
		global $wp_query;

		$args = array();

		if ( isset( $_GET['user'] ) ) {
			$args['user'] = urldecode( cl_sanitization( $_GET['user'] ) );
		} elseif ( isset( $_GET['customer'] ) ) {
			$args['customer'] = absint( cl_sanitization( $_GET['customer'] ) );
		} elseif ( isset( $_GET['s'] ) ) {

			$is_user = strpos( $_GET['s'], strtolower( 'user:' ) ) !== false;

			if ( $is_user ) {
				$args['user'] = absint( trim( str_replace( 'user:', '', strtolower( $_GET['s'] ) ) ) );
				unset( $args['s'] );
			} else {
				$args['s'] = cl_sanitization( $_GET['s'] );
			}
		}

		if ( ! empty( $_GET['start-date'] ) ) {
			$args['start-date'] = urldecode( cl_sanitization( $_GET['start-date'] ) );
		}

		if ( ! empty( $_GET['end-date'] ) ) {
			$args['end-date'] = urldecode( cl_sanitization( $_GET['end-date'] ) );
		}

		if ( ! empty( $_GET['gateway'] ) && $_GET['gateway'] !== 'all' ) {
			$args['gateway'] = cl_sanitization( $_GET['gateway'] );
		}

		$payment_count          = cl_count_payments( $args );
		$this->complete_count   = $payment_count->publish;
		$this->pending_count    = $payment_count->pending;
		$this->processing_count = $payment_count->processing;
		$this->refunded_count   = $payment_count->refunded;
		$this->failed_count     = $payment_count->failed;
		$this->revoked_count    = $payment_count->revoked;
		$this->abandoned_count  = $payment_count->abandoned;

		foreach ( $payment_count as $count ) {
			$this->total_count += $count;
		}
	}

	/**
	 * Retrieve all the data for all the payments
	 *
	 * @since 1.4
	 * @return array $payment_data Array of all the data for the payments
	 */
	public function payments_data() {
		$per_page   = $this->per_page;
		$orderby    = isset( $_GET['orderby'] ) ? urldecode( cl_sanitization( $_GET['orderby'] ) ) : 'ID';
		$order      = isset( $_GET['order'] ) ? cl_sanitization( $_GET['order'] ) : 'DESC';
		$user       = isset( $_GET['user'] ) ? cl_sanitization( $_GET['user'] ) : null;
		$customer   = isset( $_GET['customer'] ) ? cl_sanitization( $_GET['customer'] ) : null;
		$status     = isset( $_GET['status'] ) ? cl_sanitization( $_GET['status'] ) : cl_get_payment_status_keys();
		$meta_key   = isset( $_GET['meta_key'] ) ? cl_sanitization( $_GET['meta_key'] ) : null;
		$year       = isset( $_GET['year'] ) ? cl_sanitization( $_GET['year'] ) : null;
		$month      = isset( $_GET['m'] ) ? cl_sanitization( $_GET['m'] ) : null;
		$day        = isset( $_GET['day'] ) ? cl_sanitization( $_GET['day'] ) : null;
		$search     = isset( $_GET['s'] ) ? cl_sanitization( $_GET['s'] ) : null;
		$start_date = isset( $_GET['start-date'] ) ? cl_sanitization( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] ) ? cl_sanitization( $_GET['end-date'] ) : $start_date;
		$gateway    = isset( $_GET['gateway'] ) ? cl_sanitization( $_GET['gateway'] ) : null;

		/**
		 * Introduced as part of #6063. Allow a gateway to specified based on the context.
		 *
		 * @since 2.8.11
		 *
		 * @param string $gateway
		 */
		$gateway = apply_filters( 'cl_payments_table_search_gateway', $gateway );

		if ( ! empty( $search ) ) {
			$status = 'any'; // Force all payment statuses when searching
		}

		if ( $gateway === 'all' ) {
			$gateway = null;
		}

		$args = array(
			'output'     => 'payments',
			'number'     => $per_page,
			'page'       => isset( $_GET['paged'] ) ? cl_sanitization( $_GET['paged'] ) : null,
			'orderby'    => $orderby,
			'order'      => $order,
			'user'       => $user,
			'customer'   => $customer,
			'status'     => $status,
			'meta_key'   => $meta_key,
			'year'       => $year,
			'month'      => $month,
			'day'        => $day,
			's'          => $search,
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'gateway'    => $gateway,
		);

		if ( is_string( $search ) && false !== strpos( $search, 'txn:' ) ) {

			$args['search_in_notes'] = true;
			$args['s']               = trim( str_replace( 'txn:', '', $args['s'] ) );
		}

		$p_query = new Clpaymentsquery( $args );

		return $p_query->get_payments();
	}


	public function prepare_items() {
		wp_reset_vars( array( 'action', 'payment', 'orderby', 'order', 's' ) );
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$data                  = $this->payments_data();
		$status                = isset( $_GET['status'] ) ? cl_sanitization( $_GET['status'] ) : 'any';
		$this->_column_headers = array( $columns, $hidden, $sortable );

		switch ( $status ) {
			case 'publish':
				$total_items = $this->complete_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'processing':
				$total_items = $this->processing_count;
				break;
			case 'refunded':
				$total_items = $this->refunded_count;
				break;
			case 'failed':
				$total_items = $this->failed_count;
				break;
			case 'revoked':
				$total_items = $this->revoked_count;
				break;
			case 'abandoned':
				$total_items = $this->abandoned_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
			default:
				$count       = wp_count_posts( 'cl_payment' );
				$total_items = $count->{$status};
		}

		$this->items = $data;
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
