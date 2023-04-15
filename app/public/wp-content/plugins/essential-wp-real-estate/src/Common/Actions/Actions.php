<?php
namespace Essential\Restate\Common\Actions;

use Essential\Restate\Traitval\Traitval;

class Actions {

	use Traitval;

	public function initialize() {
		add_action( 'init', array( $this, 'cl_get_actions' ) );
		add_action( 'init', array( $this, 'cl_post_actions' ) );
		add_action( 'init', array( $this, 'add_cl_user_role' ), 1 );
		add_action( 'admin_init', array( $this, 'listing_user_redirect' ) );
		add_action( 'admin_init', array( $this, 'allow_cl_user_media_uploads' ) );
		add_action( 'after_setup_theme', array( $this, 'remove_admin_bar' ) );
		add_action( 'template_redirect', array( $this, 'cl_delayed_get_actions' ) );
		add_action( 'template_redirect', array( $this, 'cl_delayed_post_actions' ) );
		add_filter( 'the_content', array( $this, 'cl_filter_success_page_content' ), 99999 );
		// Widget sortcode
		add_action( 'listing-enquiry-form', array( $this, 'cl_enquiry_form' ) );
		// Limit media library access
		add_filter( 'ajax_query_attachments_args', array( $this, 'show_current_user_attachments' ) );
		$this->cl_delayed_actions_list();
		$this->cl_is_delayed_action();
		add_action( 'cl_checkout_form_top', array( $this, 'cl_show_payment_icons' ) );
		add_action( 'cl_checkout_form_top', array( $this, 'cl_discount_field' ), -1 );
		add_action( 'cl_checkout_form_top', array( $this, 'cl_agree_to_terms_js' ) );
		add_action( 'wp_head', array( $this, 'cl_checkout_meta_tags' ) );
	}
	public function cl_enquiry_form() {
		global $post, $author, $author_name;

		$curauth = ( isset( $_GET['author_name'] ) ) ? get_user_by( 'slug', $author_name ) : get_userdata( intval( $author ) );
		if ( ! empty( $curauth ) ) {
			$author_id = $curauth->ID;
		} else {
			$author_id = $post->post_author;
		}

		$wp_user_avatar = get_user_meta( $author_id, 'wp_user_avatar', true );

		$to = '';
		if ( isset( $author_id ) && ! empty( $author_id ) ) {
			$to = get_the_author_meta( 'user_email', $author_id );
		}

		if ( empty( $to ) ) {
			$to = get_option( 'admin_email' );
		}

		if ( ! is_admin() ) {
			$first_name = get_the_author_meta( 'first_name', $author_id );
			$last_name  = get_the_author_meta( 'last_name', $author_id );
			$username   = get_the_author_meta( 'display_name', $author_id );
			if ( ! empty( $first_name ) && ! empty( $last_name ) ) {
				$author_name = $first_name . ' ' . $last_name;
			} else {
				$author_name = $username;
			}
		}
		if ( is_admin() ) { ?>
			<div class="widget_cont">
				<span class="heading"><?php echo esc_html__( 'Clasyfied Enquiry Widget', 'essential-wp-real-estate' ); ?></span>
				<span class="info"><?php echo esc_html__( 'Click to edit', 'essential-wp-real-estate' ); ?></span>
			</div>
			<?php
		} else {
			?>
			<div class="sidebar-widgets p-4">
				<div class="sides-widget-header">
					<div class="agent-photo">
						<?php
						if ( $wp_user_avatar ) {
							$avatar_url = wp_get_attachment_image_url( $wp_user_avatar, '60x60' );
							echo '<img src="' . esc_url( $avatar_url ) . '" class="author-avater-img" width="100" height="100" alt="img">';
						} else {
							echo get_avatar( $author_id, 60 );
						}
						?>
					</div>
					<div class="sides-widget-details">
						<h4><?php echo esc_html( $author_name ); ?></h4>
						<?php
						if ( get_the_author_meta( 'phone', $author_id ) ) {
							?>
							<span><i class="lni-phone-handset"></i><?php echo get_the_author_meta( 'phone', $author_id ); ?></span>
							<?php
						}
						?>
					</div>
				</div>

				<div id="listing-equiry-form" class="sides-widget-body simple-form">
					<form action="#" method="post">
						<input type="hidden" name="created_for" class="form-control" value="<?php echo esc_attr( $author_id ); ?>">
						<div class="form-group">
							<label><?php _e( 'Name', 'essential-wp-real-estate' ); ?></label>
							<input type="text" name="name" required class="form-control" placeholder="<?php _e( 'Your Name', 'essential-wp-real-estate' ); ?>">
						</div>
						<div class="form-group">
							<label><?php _e( 'Email', 'essential-wp-real-estate' ); ?></label>
							<input type="email" name="email" required class="form-control" placeholder="<?php _e( 'Your Email', 'essential-wp-real-estate' ); ?>">
						</div>
						<div class="form-group">
							<label><?php _e( 'Phone No.', 'essential-wp-real-estate' ); ?></label>
							<input type="text" name="phone" class="form-control" placeholder="<?php _e( '+001-234-5678', 'essential-wp-real-estate' ); ?>">
						</div>
						<div class="form-group">
							<label><?php _e( 'Description', 'essential-wp-real-estate' ); ?></label>
							<textarea class="form-control" required name="message" placeholder="<?php _e( 'I\'m interested in this property.', 'essential-wp-real-estate' ); ?>"></textarea>
						</div>
						<?php wp_nonce_field( 'wperesds-enquiry-form', 'wperesds_enquiry' ); ?>
						<input type="hidden" name="action" value="cl_enquiry">
						<input type="hidden" name="listing_email" value="<?php echo esc_attr( $to ); ?>">
						<div id="message"></div>
						<button type="submit" class="btn btn-black btn-md rounded full-width"><?php _e( 'Send Message', 'essential-wp-real-estate' ); ?></button>
					</form>
				</div>
			</div>
			<?php
		}

	}

	public function show_current_user_attachments( $query ) {
		$current_user = wp_get_current_user();
		if ( ! $current_user ) {
			return;
		}
		$current_user_id     = $current_user->ID;
		$query['author__in'] = array(
			$current_user_id,
		);
		return $query;
	}

	public function allow_cl_user_media_uploads() {
		// Allow Contributors to Add Media
		if ( current_user_can( 'listing_user' ) && ! current_user_can( 'upload_files' ) ) {
			$listing_user = get_role( 'listing_user' );
			$listing_user->add_cap( 'upload_files' );
		}
	}

	public function add_cl_user_role() {
		$result = add_role(
			'listing_user',
			__( 'Listing User', 'essential-wp-real-estate' ),
			array(
				'read'         => true,  // true allows this capability
				'edit_posts'   => true,
				'delete_posts' => true, // Use false to explicitly deny
			)
		);
	}

	function listing_user_redirect() {
		if ( is_admin() && current_user_can( 'listing_user' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			wp_safe_redirect( get_page_link( cl_admin_get_option( 'dashboard_page' ) ) );
			exit;
		}
	}

	public function remove_admin_bar() {
		if ( current_user_can( 'listing_user' ) && ! is_admin() ) {
			show_admin_bar( false );
		}
	}

	function cl_agree_to_terms_js() {
		if ( cl_admin_get_option( 'show_agree_to_terms', false ) || cl_admin_get_option( 'show_agree_to_privacy_policy', false ) ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$(document.body).on('click', '.cl_terms_links', function(e) {
						//e.preventDefault();
						$(this).parent().prev('.cl-terms').slideToggle();
						$(this).parent().find('.cl_terms_links').toggle();
						return false;
					});
				});
			</script>
			<?php
		}
	}


	function cl_discount_field() {

		if ( isset( $_GET['payment-mode'] ) && cl_is_ajax_disabled() ) {
			return; // Only show before a payment method has been selected if ajax is disabled
		}

		if ( ! cl_is_checkout() ) {
			return;
		}

		if ( WPERECCP()->front->discountaction->cl_has_active_discounts() && WPERECCP()->front->cart->cl_get_cart_total() ) :

			$color = cl_admin_get_option( 'checkout_color', 'blue' );
			$color = ( $color == 'inherit' ) ? '' : $color;
			$style = cl_admin_get_option( 'button_style', 'button' );
			?>
			<fieldset id="cl_discount_code">
				<p id="cl_show_discount" style="display:none;">
					<?php _e( 'Have a discount code?', 'essential-wp-real-estate' ); ?> <a href="#" class="cl_discount_link"><?php echo _x( 'Click to enter it', 'Entering a discount code', 'essential-wp-real-estate' ); ?></a>
				</p>
				<p id="cl-discount-code-wrap" class="cl-cart-adjustment">
					<label class="cl-label" for="cl-discount">
						<?php _e( 'Discount', 'essential-wp-real-estate' ); ?>
					</label>
					<span class="cl-description"><?php _e( 'Enter a coupon code if you have one.', 'essential-wp-real-estate' ); ?></span>
					<span class="cl-discount-code-field-wrap">
						<input class="cl-input" type="text" id="cl-discount" name="cl-discount" placeholder="<?php _e( 'Enter discount', 'essential-wp-real-estate' ); ?>" />
						<input type="submit" class="cl-apply-discount cl-submit <?php echo esc_attr( $color ) . ' ' . esc_attr( $style ); ?>" value="<?php echo _x( 'Apply', 'Apply discount at checkout', 'essential-wp-real-estate' ); ?>" />
					</span>
					<span class="cl-discount-loader cl-loading" id="cl-discount-loader" style="display:none;"></span>
					<span id="cl-discount-error-wrap" class="cl_error cl-alert cl-alert-error" aria-hidden="true" style="display:none;"></span>
				</p>
			</fieldset>
			<?php
		endif;
	}


	function cl_show_payment_icons() {
		if ( WPERECCP()->front->gateways->cl_show_gateways() && did_action( 'cl_payment_mode_top' ) ) {
			return;
		}
		$payment_methods = cl_admin_get_option( 'accepted_cards', array() );
		if ( empty( $payment_methods ) ) {
			return;
		}
		echo '<div class="cl-payment-icons">';
		foreach ( $payment_methods as $key => $card ) {
			if ( cl_string_is_image_url( $key ) ) {
				echo '<img class="payment-icon" src="' . esc_url( $key ) . '" alt="' . esc_attr( $card ) . '"/>';
			} else {
				$card = strtolower( str_replace( ' ', '', $card ) );
				if ( has_filter( 'cl_accepted_payment_' . $card . '_image' ) ) {
					$image = apply_filters( 'cl_accepted_payment_' . $card . '_image', '' );
				} else {
					$image = cl_locate_template( 'images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $card . '.png', false );
					// Replaces backslashes with forward slashes for Windows systems
					$plugin_dir  = wp_normalize_path( WP_PLUGIN_DIR );
					$content_dir = wp_normalize_path( WP_CONTENT_DIR );
					$image       = wp_normalize_path( $image );
					$image       = str_replace( $plugin_dir, WP_PLUGIN_URL, $image );
					$image       = str_replace( $content_dir, WP_CONTENT_URL, $image );
				}
				if ( cl_is_ssl_enforced() || is_ssl() ) {
					$image = cl_enforced_ssl_asset_filter( $image );
				}
				echo '<img class="payment-icon" src="' . esc_url( $image ) . '" alt="' . esc_attr( $card ) . '"/>';
			}
		}
		echo '</div>';
	}


	/**
	 * cl_get_actions
	 *
	 * @return void
	 */
	public function cl_get_actions() {
		$key               = ! empty( $_GET['cl_action'] ) ? sanitize_key( $_GET['cl_action'] ) : false;
		$is_delayed_action = $this->cl_is_delayed_action( $key );
		if ( $is_delayed_action ) {
			return;
		}
		if ( ! empty( $key ) ) {
			do_action( "cl_{$key}", $_GET );
		}
	}


	/**
	 * present in $_POST is called using WordPress's do_action function. These
	 * functions are called on init.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function cl_post_actions() {
		$key               = ! empty( $_POST['cl_action'] ) ? sanitize_key( $_POST['cl_action'] ) : false;
		$is_delayed_action = $this->cl_is_delayed_action( $key );
		if ( $is_delayed_action ) {
			return;
		}
		if ( ! empty( $key ) ) {
			do_action( "cl_{$key}", $_POST );
		}
	}


	/**
	 * Call any actions that should have been delayed, in order to be sure that all necessary information
	 * has been loaded by WP Core.
	 *
	 * present in $_POST is called using WordPress's do_action function. These
	 * functions are called on template_redirect.
	 *
	 * @since 2.9.4
	 * @return void
	 */
	public function cl_delayed_get_actions() {
		$key               = ! empty( $_GET['cl_action'] ) ? sanitize_key( $_GET['cl_action'] ) : false;
		$is_delayed_action = $this->cl_is_delayed_action( $key );

		if ( ! $is_delayed_action ) {
			return;
		}

		if ( ! empty( $key ) ) {
			do_action( "cl_{$key}", $_GET );
		}
	}

	/**
	 * Call any actions that should have been delayed, in order to be sure that all necessary information
	 * has been loaded by WP Core.
	 *
	 * present in $_POST is called using WordPress's do_action function. These
	 * functions are called on template_redirect.
	 *
	 * @since 2.9.4
	 * @return void
	 */
	public function cl_delayed_post_actions() {
		$key               = ! empty( $_POST['cl_action'] ) ? sanitize_key( $_POST['cl_action'] ) : false;
		$is_delayed_action = $this->cl_is_delayed_action( $key );

		if ( ! $is_delayed_action ) {
			return;
		}

		if ( ! empty( $key ) ) {
			do_action( "cl_{$key}", $_POST );
		}
	}


	/**
	 *
	 * @since 2.9.4
	 *
	 * @return array
	 */
	public function cl_delayed_actions_list() {
		return (array) apply_filters(
			'cl_delayed_actions',
			array(
				'add_to_cart',
			)
		);
	}

	/**
	 * Determine if the requested action needs to be delayed or not.
	 *
	 * @since 2.9.4
	 *
	 * @param string $action
	 *
	 * @return bool
	 */
	public function cl_is_delayed_action( $action = '' ) {
		return in_array( $action, $this->cl_delayed_actions_list() );
	}

	function cl_filter_success_page_content( $content ) {
		if ( isset( $_GET['payment-confirmation'] ) && cl_is_success_page() ) {
			$p_confermation = cl_sanitization( $_GET['payment-confirmation'] );
			if ( has_filter( 'cl_payment_confirm_' . $p_confermation ) ) {
				$content = apply_filters( 'cl_payment_confirm_' . $p_confermation, $content );
			}
		}

		return $content;
	}

	function cl_checkout_meta_tags() {

		$pages   = array();
		$pages[] = cl_admin_get_option( 'success_page' );
		$pages[] = cl_admin_get_option( 'failure_page' );
		$pages[] = cl_admin_get_option( 'purchase_history_page' );

		if ( ! cl_is_checkout() && ! is_page( $pages ) ) {
			return;
		}

		echo '<meta name="cl-chosen-gateway" content="' . esc_attr( WPERECCP()->front->gateways->cl_get_chosen_gateway() ) . '"/>' . "\n";
		echo '<meta name="robots" content="noindex,nofollow" />' . "\n";
	}
}
