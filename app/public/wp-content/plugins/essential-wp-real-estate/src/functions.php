<?php
use Essential\Restate\Front\cl_Logging\cl_Logging;
use Essential\Restate\Front\Purchase\Payments\Clpayment;
use Essential\Restate\Front\Models\Listingsaction;

if ( ! function_exists( 'cl_template_path' ) ) {
	function cl_template_path() {
		return apply_filters( 'cl_template_path', 'essential-wp-real-estate' );
	}
}

if ( ! function_exists( 'cl_get_template_with_dir' ) ) {
	function cl_get_template_with_dir( $slug, $dir = '' ) {
		$template = '';
		// defining pref args
		global $pref;
		$args['pref'] = $pref;

		// Look in yourtheme/slug-name and yourtheme/essential-wp-real-estate/slug-name
		if ( $dir ) {
			$template = locate_template( array( "{$dir}/{$slug}", cl_template_path() . "/{$dir}/{$slug}" ) );
		}
		// Get default slug-name
		if ( ! $template && $dir && file_exists( WPERESDS_FRONT_TEMPLATE_DIR . "/listings/{$dir}/{$slug}" ) ) {
			$template = WPERESDS_FRONT_TEMPLATE_DIR . "/listings/{$dir}/{$slug}";
		}
		// If template file doesn't exist, look in yourtheme/slug and yourtheme/essential-wp-real-estate/slug
		if ( ! $template ) {
			$template = locate_template( array( "{$slug}", cl_template_path() . "{$slug}" ), cl_template_path() . "{$dir}/{$slug}" );
		}
		// Allow 3rd party plugin filter template file from their plugin
		if ( $template ) {
			$template = apply_filters( 'cl_get_template_with_dir', $template, $slug, $dir );
		}
		if ( $template && file_exists( $template ) ) {
			load_template( $template, false, $args );
		}
		return $template;
	}
}




if ( ! function_exists( 'cl_get_template_part' ) ) {
	function cl_get_template_part( $slug, $name = '' ) {
		$template = '';
		// defining pref args
		global $pref;
		$args['pref'] = $pref;

		// Look in yourtheme/slug-name and yourtheme/essential-wp-real-estate/slug-name
		if ( $name ) {
			$template = locate_template( array( "{$slug}-{$name}", cl_template_path() . "/{$slug}-{$name}" ) );
		}
		// Get default slug-name
		if ( ! $template && $name && file_exists( WPERESDS_FRONT_TEMPLATE_DIR . "/listings/{$slug}-{$name}" ) ) {
			$template = WPERESDS_FRONT_TEMPLATE_DIR . "/listings/{$slug}-{$name}";
		}
		// If template file doesn't exist, look in yourtheme/slug and yourtheme/essential-wp-real-estate/slug
		if ( ! $template ) {
			$template = locate_template( array( "{$slug}", cl_template_path() . "{$slug}" ) );
		}
		// Allow 3rd party plugin filter template file from their plugin
		if ( $template ) {
			$template = apply_filters( 'cl_get_template_part', $template, $slug, $name );
		}
		if ( $template && file_exists( $template ) ) {
			load_template( $template, false, $args );
		}

		return $template;
	}
}


if ( ! function_exists( 'cl_get_template' ) ) {
	function cl_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}
		$located = cl_locate_template( $template_name, $template_path, $default_path );
		if ( ! file_exists( $located ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
			return;
		}
		$located = apply_filters( 'cl_get_template', $located, $template_name, $args, $template_path, $default_path );
		do_action( 'cl_before_template_part', $template_name, $template_path, $located, $args );
		if ( $located && file_exists( $located ) ) {
			include $located;
		}
		do_action( 'cl_after_template_part', $template_name, $template_path, $located, array() );
	}
}

if ( ! function_exists( 'cl_locate_template' ) ) {
	function cl_locate_template( $template_name, $template_path = '', $default_path = '' ) {

		if ( ! $template_path ) {
			$template_path = cl_template_path();
		}
		if ( ! $default_path ) {
			$default_path = WPERESDS_FRONT_TEMPLATE_DIR . '/listings/';
		}
		$template = null;
		// Look within passed path within the theme - this is priority
		$template = locate_template( array( trailingslashit( $template_path ) . $template_name, $template_name ) );
		// Get default CL mtemplate
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		return apply_filters( 'cl_locate_template', $template, $template_name, $template_path );
	}
}

function cl_get_theme_template_paths() {
	$template_dir = cl_get_theme_template_dir_name();

	$file_paths = array(
		1   => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10  => trailingslashit( get_template_directory() ) . $template_dir,
		100 => WPERESDS_ASSETS_CSS_DIR,
	);

	$file_paths = apply_filters( 'cl_template_paths', $file_paths );

	// sort the file paths based on priority
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );
}


function cl_get_theme_template_dir_name() {
	return trailingslashit( apply_filters( 'cl_templates_dir', 'cl_templates' ) );
}

// -- Trigger only when cl_admin_settings page value updated
// add_action('update_option_cl_admin_settings', 'init_wperesds_pages');

// -- Trigger only when Plugin activated
register_activation_hook( WPERESDS_FILE, 'init_wperesds_pages' );

/**
 * init_wperesds_pages
 *
 * @return void
 * @since 1.0.0
 */
function init_wperesds_pages() {
	// -- Check the cl_admin_settings option for value
	$cl_pages = get_option( 'cl_admin_settings' );

	$cl_pages_info = array(
		'purchase_page'         => array(
			'title'      => 'Primary Checkout Page',
			'short_code' => '[cl_listing_checkout]',
		),
		'success_page'          => array(
			'title'      => 'Success Page',
			'short_code' => '[cl_receipt]',
		),
		'failure_page'          => array(
			'title'      => 'Failed Transaction Page',
			'short_code' => '[failure_page]',
		),
		'cancel_payment_page'   => array(
			'title'      => 'Cancel Transaction Page',
			'short_code' => '[cancel_payment]',
		),
		'purchase_history_page' => array(
			'title'      => 'Purchase History Page',
			'short_code' => '[purchase_history]',
		),
		'register_user_page'    => array(
			'title'      => 'Register User Page',
			'short_code' => '[cl_register_user]',
		),
		'update_user_page'      => array(
			'title'      => 'User Profile',
			'short_code' => '[cl_update_user]',
		),
		'login_redirect_page'   => array(
			'title'      => 'Login Page',
			'short_code' => '[cl_admin_login]',
		),
		'compare_page'          => array(
			'title'      => 'Compare Page',
			'short_code' => '[cl_compare_listing]',
		),
		'dashboard_page'        => array(
			'title'      => 'Dashboard',
			'short_code' => '[cl_dashboard]',
		),
		'add_listing_page'      => array(
			'title'      => 'Add Listing Page',
			'short_code' => '[cl_add_listing]',
		),
		'edit_listing_page'     => array(
			'title'      => 'Edit Listing Page',
			'short_code' => '[cl_edit_listing]',
		),
		'checkout_page'         => array(
			'title'      => 'Checkout Page',
			'short_code' => '[cl_listing_checkout]',
		),
		'cart_page'             => array(
			'title'      => 'Cart Page',
			'short_code' => '[cl_listing_cart]',
		),
	);

	// -- Run only if cl_admin_settings page is empty | create pages according to default page title & content information
	if ( isset( $cl_pages ) && empty( $cl_pages ) ) {
		$set_option = array();

		// -- default page title & content information

		foreach ( $cl_pages_info as $key => $page_val ) {
			$args = array(
				'post_title'     => $page_val['title'], // -- Set title
				'post_content'   => $page_val['short_code'], // -- Set content
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'comment_status' => 'closed',
			);
			// Catch post ID
			$get_id             = wp_insert_post( $args );
			$set_option[ $key ] = $get_id;
		}

		update_option( 'cl_admin_settings', $set_option );
	} else {

		foreach ( $cl_pages as $page_val => $page_id ) {
			if ( isset( $cl_pages_info[ $page_val ] ) ) {
				$args = array(
					'ID'           => $page_id,
					'post_content' => $cl_pages_info[ $page_val ]['short_code'], // -- Set content
				);
				// Update Page
				wp_update_post( $args );
			}
		}

		return;
	}
}


function cl_add_custom_query_var( $vars ) {
	$vars[] = 'cl_edit_listing_var';
	return $vars;
}

add_filter( 'query_vars', 'cl_add_custom_query_var' );
add_filter( 'wp_kses_allowed_html', 'cl_kses_allowed_html', 10, 2 );
function cl_kses_allowed_html( $tags, $context ) {
	switch ( $context ) {
		case 'cl_code_context':
			$tags = array(
				'iframe' => array(
					'allowfullscreen' => array(),
					'frameborder'     => array(),
					'height'          => array(),
					'width'           => array(),
					'src'             => array(),
					'class'           => array(),
				),
				'li'     => array(
					'class' => array(),
				),
				'h5'     => array(
					'class' => array(),
				),
				'span'   => array(
					'class' => array(),
				),
				'a'      => array(
					'href'  => array(),
					'class' => array(),
				),
				'i'      => array(
					'class' => array(),
				),
				'br'     => array(
					'class' => array(),
				),
				'p'      => array(),
				'em'     => array(),
				'strong' => array(),
				'button' => array(
					'id'    => array(),
					'class' => array(),
				),
			);
			return $tags;
		case 'cl_code_img':
			$tags = array(
				'img' => array(
					'class'  => array(),
					'height' => array(),
					'width'  => array(),
					'src'    => array(),
					'alt'    => array(),
				),
			);
			return $tags;
		default:
			return $tags;
	}
}


add_filter( 'display_post_states', 'init_wperesds_pages_state', 10, 2 );
/**
 * init_wperesds_pages_state
 *
 * @param  mixed $post_states
 * @param  mixed $post
 * @return void
 * @since 1.0.0
 */
function init_wperesds_pages_state( $post_states, $post ) {
	$cl_pages    = get_option( 'cl_admin_settings' );
	$post_states = array();
	if ( $cl_pages ) {
		if ( in_array( $post->ID, $cl_pages ) ) {
			// -- Flip the array to get the key as val
			$set_val = array_flip( $cl_pages );
			// -- Set name and replace '_' with ' '
			$set_name = str_replace( '_', ' ', $set_val[ $post->ID ] );
			// -- Set post states
			$post_states[] = ucwords( $set_name );
		}
	}

	return $post_states;
}

if ( ! function_exists( 'cl_get_avat' ) ) {
	function cl_get_avat( $size = 70 ) {
		global $post;
		$wp_user_avatar = get_user_meta( $post->post_author, 'wp_user_avatar', true );
		if ( $wp_user_avatar ) {
			$avatar_url = wp_get_attachment_image_url( $wp_user_avatar, 'thumbnail' );
			echo '<div class="author-img"><img src="' . esc_url( $avatar_url ) . '" class="avatar author-avater-img" width="' . esc_attr( $size ) . '" height="' . esc_attr( $size ) . '"  alt="img"></div>';
		} else {
			return '<div class="author-img">' . get_avatar( $post->post_author, $size ) . '</div>';
		}
	}
}

if ( ! function_exists( 'cl_get_avatar_url' ) ) {
	function cl_get_avatar_url() {
		$current_user   = wp_get_current_user();
		$wp_user_avatar = get_user_meta( $current_user->ID, 'wp_user_avatar', true );
		if ( $wp_user_avatar ) {
			return wp_get_attachment_image_url( $wp_user_avatar, 'thumbnail' );
		} else {
			return get_avatar_url( $current_user->ID );
		}
	}
}

if ( ! function_exists( 'cl_get_current_avatar' ) ) {
	function cl_get_current_avatar() {
		$current_user   = wp_get_current_user();
		$wp_user_avatar = get_user_meta( $current_user->ID, 'wp_user_avatar', true );
		if ( $wp_user_avatar ) {
			$avatar_url = wp_get_attachment_image_url( $wp_user_avatar, 'thumbnail' );
			return '<img src="' . $avatar_url . '" class="author-avater-img" width="100" height="100" alt="img">';
		} else {
			return get_avatar( $current_user->ID );
		}
	}
}

function cl_get_current_page_url( $nocache = false ) {

	global $wp;

	if ( get_option( 'permalink_structure' ) ) {

		$base = trailingslashit( home_url( $wp->request ) );
	} else {

		$base = add_query_arg( $wp->query_string, '', trailingslashit( home_url( $wp->request ) ) );
		$base = remove_query_arg( array( 'post_type', 'name' ), $base );
	}

	$scheme = is_ssl() ? 'https' : 'http';
	$uri    = set_url_scheme( $base, $scheme );

	if ( is_front_page() ) {
		$uri = home_url( '/' );
	} elseif ( cl_is_checkout() ) {
		$uri = cl_get_checkout_uri();
	}

	$uri = apply_filters( 'cl_get_current_page_url', $uri );

	if ( $nocache ) {
		$uri = cl_add_cache_busting( $uri );
	}

	return $uri;
}

function cl_is_checkout() {
	global $wp_query;

	$is_object_set    = isset( $wp_query->queried_object );
	$is_object_id_set = isset( $wp_query->queried_object_id );
	$is_checkout      = is_page( cl_admin_get_option( 'purchase_page' ) );

	if ( ! $is_object_set ) {

		unset( $wp_query->queried_object );
	}

	if ( ! $is_object_id_set ) {

		unset( $wp_query->queried_object_id );
	}

	return apply_filters( 'cl_is_checkout', $is_checkout );
}

function cl_get_checkout_uri( $args = array() ) {
	$uri = cl_admin_get_option( 'purchase_page', false );
	$uri = isset( $uri ) ? get_permalink( $uri ) : null;
	if ( ! empty( $args ) ) {
		// Check for backward compatibility
		if ( is_string( $args ) ) {
			$args = str_replace( '?', '', $args );
		}

		$args = wp_parse_args( $args );

		$uri = add_query_arg( $args, $uri );
	}

	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$ajax_url = admin_url( 'admin-ajax.php', $scheme );

	if ( ( ! preg_match( '/^https/', $uri ) && preg_match( '/^https/', $ajax_url ) ) || cl_is_ssl_enforced() ) {
		$uri = preg_replace( '/^http:/', 'https:', $uri );
	}

	if ( cl_admin_get_option( 'no_cache_checkout', false ) ) {
		$uri = cl_add_cache_busting( $uri );
	}

	return apply_filters( 'cl_get_checkout_uri', $uri );
}

function cl_admin_get_option( $key, $default = false ) {
	$options = get_option( 'cl_admin_settings' );

	return ( isset( $options[ $key ] ) && ! empty( $options[ $key ] ) ) ? $options[ $key ] : $default;
}

function cl_is_ssl_enforced() {
	 $ssl_enforced = cl_admin_get_option( 'enforce_ssl', false );
	$ssl_enforced  = isset( $ssl_enforced ) && ( $ssl_enforced == 1 ) ? true : false;
	return (bool) apply_filters( 'cl_is_ssl_enforced', $ssl_enforced );
}

function cl_is_ajax_enabled() {
	 $retval = ! cl_is_ajax_disabled();
	return apply_filters( 'cl_is_ajax_enabled', $retval );
}

function cl_is_ajax_disabled() {
	return apply_filters( 'cl_is_ajax_disabled', false );
}

function cl_add_cache_busting( $url = '' ) {
	$no_cache_checkout = cl_admin_get_option( 'no_cache_checkout', false );
	$no_cache_checkout = isset( $no_cache_checkout ) && ( $no_cache_checkout == 1 ) ? true : false;
	if ( cl_is_caching_plugin_active() || ( cl_is_checkout() && $no_cache_checkout ) ) {
		$url = add_query_arg( 'nocache', 'true', $url );
	}
	return $url;
}

function cl_is_caching_plugin_active() {
	$caching = ( function_exists( 'wpsupercache_site_admin' ) || defined( 'W3TC' ) || function_exists( 'rocket_init' ) ) || defined( 'WPHB_VERSION' );
	return apply_filters( 'cl_is_caching_plugin_active', $caching );
}

function cl_get_customer_address( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$address = get_user_meta( $user_id, '_cl_user_address', true );

	if ( ! $address || ! is_array( $address ) || empty( $address ) ) {
		$address = array();
	}

	$address = wp_parse_args(
		$address,
		array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'zip'     => '',
			'country' => '',
			'state'   => '',
		)
	);

	return $address;
}
function cl_get_file_extension( $str ) {
	$parts = explode( '.', $str );
	return end( $parts );
}


function cl_get_ip() {
	$ip = '127.0.0.1';

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		// check ip from share internet
		$ip = cl_sanitization( $_SERVER['HTTP_CLIENT_IP'] );
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// to check ip is pass from proxy
		// can include more than 1 ip, first is the public one
		$ip = explode( ',', cl_sanitization( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		$ip = trim( $ip[0] );
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = cl_sanitization( $_SERVER['REMOTE_ADDR'] );
	}

	// Fix potential CSV returned from $_SERVER variables
	$ip_array = explode( ',', $ip );
	$ip_array = array_map( 'trim', $ip_array );

	return apply_filters( 'cl_get_ip', $ip_array[0] );
}

function cl_is_test_mode() {
	$ret = cl_admin_get_option( 'paypal_sandbox_active', false );
	return (bool) apply_filters( 'cl_is_test_mode', $ret );
}

function cl_is_success_page() {
	 $is_success_page = cl_admin_get_option( 'success_page', false );
	$is_success_page  = isset( $is_success_page ) ? is_page( $is_success_page ) : false;

	return apply_filters( 'cl_is_success_page', $is_success_page );
}

function cl_payment_get_ip_address_url( $payment_id ) {

	$payment = new Clpayment( $payment_id );

	$base_url     = 'https://ipinfo.io/';
	$provider_url = '<a href="' . esc_url( $base_url ) . esc_attr( $payment->ip ) . '" target="_blank">' . esc_html( $payment->ip ) . '</a>';

	return apply_filters( 'cl_payment_get_ip_address_url', $provider_url, $payment->ip, $payment_id );
}


function cl_get_default_labels() {
	$defaults = array(
		'singular' => __( 'listing', 'essential-wp-real-estate' ),
		'plural'   => __( 'listings', 'essential-wp-real-estate' ),
	);
	return apply_filters( 'cl_default_listings_name', $defaults );
}

/**
 * Get Singular Label
 *
 * @since 1.0.8.3
 *
 * @param bool $lowercase
 * @return string $defaults['singular'] Singular label
 */
function cl_get_label_singular( $lowercase = false ) {
	$defaults = cl_get_default_labels();
	return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}


function cl_get_label_plural( $lowercase = false ) {
	$defaults = cl_get_default_labels();
	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

function cl_has_upgrade_completed( $upgrade_action = '' ) {
	if ( empty( $upgrade_action ) ) {
		return false;
	}
	$completed_upgrades = cl_get_completed_upgrades();
	return in_array( $upgrade_action, $completed_upgrades );
}

function cl_get_completed_upgrades() {
	$completed_upgrades = get_option( 'cl_completed_upgrades' );
	if ( false === $completed_upgrades ) {
		$completed_upgrades = array();
	}
	return $completed_upgrades;
}

function cl_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = cl_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'cl_completed_upgrades', $completed_upgrades );
}


function cl_is_cc_verify_enabled() {
	$ret = true;

	$gateways = WPERECCP()->front->gateways->cl_get_enabled_payment_gateways();

	if ( count( $gateways ) == 1 && ! isset( $gateways['paypal'] ) && ! isset( $gateways['manual'] ) ) {
		$ret = true;
	} elseif ( count( $gateways ) == 1 ) {
		$ret = false;
	} elseif ( count( $gateways ) == 2 && isset( $gateways['paypal'] ) && isset( $gateways['manual'] ) ) {
		$ret = false;
	}

	return (bool) apply_filters( 'cl_verify_credit_cards', $ret );
}

function cl_get_ajax_url() {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = cl_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'cl_ajax_url', $ajax_url );
}


function cl_can_view_receipt( $payment_key = '' ) {

	$return = false;

	if ( empty( $payment_key ) ) {
		return $return;
	}

	global $cl_receipt_args;

	$cl_receipt_args['id'] = cl_get_purchase_id_by_key( $payment_key );

	$user_id = (int) cl_get_payment_user_id( $cl_receipt_args['id'] );

	$payment_meta = cl_get_payment_meta( $cl_receipt_args['id'] );

	if ( is_user_logged_in() ) {
		if ( $user_id === (int) get_current_user_id() ) {
			$return = true;
		} elseif ( wp_get_current_user()->user_email === cl_get_payment_user_email( $cl_receipt_args['id'] ) ) {
			$return = true;
		} elseif ( current_user_can( 'view_shop_sensitive_data' ) ) {
			$return = true;
		}
	}

	$session = cl_get_purchase_session();
	if ( ! empty( $session ) && ! is_user_logged_in() ) {
		if ( $session['purchase_key'] === $payment_meta['key'] ) {
			$return = true;
		}
	}

	return (bool) apply_filters( 'cl_can_view_receipt', $return, $payment_key );
}

function cl_sanitize_html_class( $class = '' ) {
	if ( is_string( $class ) ) {
		$class = sanitize_html_class( $class );
	} elseif ( is_array( $class ) ) {
		$class = array_values( array_map( 'sanitize_html_class', $class ) );
		$class = implode( ' ', array_unique( $class ) );
	}
	return $class;
}

function cl_logged_in_only() {
	$ret = cl_admin_get_option( 'logged_in_only', false );
	return (bool) apply_filters( 'cl_logged_in_only', $ret );
}

function cl_htaccess_exists() {
	 $upload_path = cl_get_upload_dir();
	return file_exists( $upload_path . '/.htaccess' );
}

function cl_get_upload_dir() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/cl' );
	$path = $wp_upload_dir['basedir'] . '/cl';
	return apply_filters( 'cl_get_upload_dir', $path );
}

function cl_get_htaccess_rules( $method = false ) {

	if ( empty( $method ) ) {
		$method = WPERECCP()->front->listingsaction->cl_get_file_listing_method();
	}

	switch ( $method ) :

		case 'redirect':
			// Prevent directory browsing
			$rules = 'Options -Indexes';
			break;

		case 'direct':
		default:
			// Prevent directory browsing and direct access to all files, except images (they must be allowed for featured images / thumbnails)
			$allowed_filetypes = apply_filters( 'cl_protected_directory_allowed_filetypes', array( 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'ogg' ) );
			$rules             = "Options -Indexes\n";
			$rules            .= "deny from all\n";
			$rules            .= "<FilesMatch '\.(" . implode( '|', $allowed_filetypes ) . ")$'>\n";
			$rules            .= "Order Allow,Deny\n";
			$rules            .= "Allow from all\n";
			$rules            .= "</FilesMatch>\n";
			break;

	endswitch;
	$rules = apply_filters( 'cl_protected_directory_htaccess_rules', $rules, $method );
	return $rules;
}

function cl_pagination( $args = array() ) {

	$big = 999999;

	$defaults = array(
		'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format'  => '?paged=%#%',
		'current' => max( 1, get_query_var( 'paged' ) ),
		'type'    => '',
		'total'   => '',
	);

	$args = wp_parse_args( $args, $defaults );

	$type  = $args['type'];
	$total = $args['total'];

	// Type and total must be specified.
	if ( empty( $type ) || empty( $total ) ) {
		return false;
	}

	$pagination = paginate_links(
		array(
			'base'    => $args['base'],
			'format'  => $args['format'],
			'current' => $args['current'],
			'total'   => $total,
		)
	);

	if ( ! empty( $pagination ) ) : ?>
		<div id="cl_<?php echo esc_attr( $type ); ?>_pagination" class="cl_pagination navigation">
			<?php printf( $pagination ); ?>
		</div>
		<?php
endif;
}



function cl_total_active_listing_by_user() {
	global $current_user;
	$args               = array(
		'author'         => $current_user->ID,
		'post_type'      => 'cl_cpt',
		'posts_per_page' => -1, // no limit,
	);
	$current_user_posts = get_posts( $args );
	return count( $current_user_posts );
}

function cl_listing_total_view() {
	global $current_user;
	$args = array(
		'author'         => $current_user->ID,
		'post_type'      => 'cl_cpt',
		'posts_per_page' => -1, // no limit,
	);

	$count              = 0;
	$current_user_posts = get_posts( $args );
	foreach ( $current_user_posts as $single_post ) {
		$single_count = get_post_meta( $single_post->ID, 'listing_views_count', true );
		$count       += (int) $single_count;
	}

	return $count;
}

function cl_listing_total_review() {
	global $current_user, $author, $author_name;

	$curauth = ( isset( $_GET['author_name'] ) ) ? get_user_by( 'slug', $author_name ) : get_userdata( intval( $author ) );
	if ( $curauth != '' ) {
		$author_id = $curauth->ID;
	} else {
		$author_id = $current_user->ID;
	}
	$args = array(
		'author'         => $author_id,
		'post_type'      => 'cl_cpt',
		'posts_per_page' => -1, // no limit,
	);

	$comments           = 0;
	$current_user_posts = get_posts( $args );
	foreach ( $current_user_posts as $single_post ) {
		$single_comments = get_comments( array( 'post_id' => $single_post->ID ) );
		$comments       += (int) count( $single_comments );
	}
	return $comments;
}


function cl_listing_total_saved() {
	global $current_user;

	$user_meta = get_user_meta( $current_user->ID, '_favorite_posts' );
	echo count( $user_meta );
}



function cl_sanitization( $value ) {
	if ( is_array( $value ) ) {
		$sanitized = array_map( 'cl_sanitization', $value );
	} else {
		$sanitized = sanitize_text_field( $value );
	}
	return $sanitized;
}



function cl_listing_insert_enquiry_message( $args = array() ) {
	global $wpdb;
	if ( empty( $args['name'] ) ) {
		return new \WP_Error( 'no-name', __( 'You must provide a name.', 'essential-wp-real-estate' ) );
	}
	$created_for = (int) $args['created_for'];
	$defaults    = array(
		'name'        => '',
		'email'       => '',
		'message'     => '',
		'created_for' => $created_for,
		'created_at'  => current_time( 'mysql' ),
	);

	$data     = wp_parse_args( $args, $defaults );
	$inserted = $wpdb->insert(
		$wpdb->prefix . 'enquiry_message',
		$data,
		array(
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
		)
	);

	if ( ! $inserted ) {
		return new \WP_Error( 'failed-to-insert', __( 'Failed to insert data', 'essential-wp-real-estate' ) );
	}

	return $wpdb->insert_id;
}
