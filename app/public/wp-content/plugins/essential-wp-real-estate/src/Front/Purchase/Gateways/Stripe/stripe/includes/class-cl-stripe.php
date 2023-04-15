<?php
/**
 * Main plugin class.
 *
 * @package CL_Stripe
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.8.1
 */

/**
 * CL_Stripe class.
 *
 * @since 2.6.0
 */
class CL_Stripe {


	/**
	 * Singleton instance.
	 *
	 * @since 2.6.0
	 * @var CL_Stripe
	 */
	private static $instance;

	/**
	 * Rate limiting component.
	 *
	 * @since 2.6.19
	 * @var CL_Stripe_Rate_Limiting
	 */
	public $rate_limiting;

	/**
	 * Instantiates or returns the singleton instance.
	 *
	 * @since 2.6.0
	 *
	 * @return CL_Stripe
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CL_Stripe ) ) {
			self::$instance = new CL_Stripe();
			self::$instance->includes();
			self::$instance->setup_classes();
			self::$instance->actions();
			self::$instance->filters();

			if ( class_exists( 'CL_License' ) && is_admin() && true === cls_is_pro() ) {
				new CL_License(
					CL_STRIPE_PLUGIN_FILE,
					'Stripe Pro Payment Gateway',
					CL_STRIPE_VERSION,
					'Sandhills Development, LLC',
					'stripe_license_key',
					null,
					167
				);
			}
		}

		return self::$instance;
	}

	/**
	 * Includes files.
	 *
	 * @since 2.6.0
	 */
	private function includes() {
		if ( ! class_exists( 'Stripe\Stripe' ) ) {
			require_once CLS_PLUGIN_DIR . '/vendor/autoload.php';
		}
		require_once CLS_PLUGIN_DIR . '/includes/class-stripe-api.php';

		require_once CLS_PLUGIN_DIR . '/includes/utils/exceptions/class-stripe-api-unmet-requirements.php';
		require_once CLS_PLUGIN_DIR . '/includes/utils/exceptions/class-attribute-not-found.php';
		require_once CLS_PLUGIN_DIR . '/includes/utils/exceptions/class-stripe-object-not-found.php';
		require_once CLS_PLUGIN_DIR . '/includes/utils/exceptions/class-gateway-exception.php';
		require_once CLS_PLUGIN_DIR . '/includes/utils/interface-static-registry.php';
		require_once CLS_PLUGIN_DIR . '/includes/utils/class-registry.php';
		require_once CLS_PLUGIN_DIR . '/includes/utils/modal.php';

		require_once CLS_PLUGIN_DIR . '/includes/functions.php';
		require_once CLS_PLUGIN_DIR . '/includes/deprecated.php';
		require_once CLS_PLUGIN_DIR . '/includes/compat.php';
		require_once CLS_PLUGIN_DIR . '/includes/i18n.php';
		require_once CLS_PLUGIN_DIR . '/includes/emails.php';
		require_once CLS_PLUGIN_DIR . '/includes/payment-receipt.php';
		require_once CLS_PLUGIN_DIR . '/includes/card-actions.php';
		require_once CLS_PLUGIN_DIR . '/includes/gateway-actions.php';
		require_once CLS_PLUGIN_DIR . '/includes/gateway-filters.php';
		require_once CLS_PLUGIN_DIR . '/includes/payment-actions.php';
		require_once CLS_PLUGIN_DIR . '/includes/webhooks.php';
		require_once CLS_PLUGIN_DIR . '/includes/elements.php';
		require_once CLS_PLUGIN_DIR . '/includes/scripts.php';
		require_once CLS_PLUGIN_DIR . '/includes/template-functions.php';
		require_once CLS_PLUGIN_DIR . '/includes/class-cl-stripe-rate-limiting.php';

		// Payment Methods.
		require_once CLS_PLUGIN_DIR . '/includes/payment-methods/payment-request/index.php';
		require_once CLS_PLUGIN_DIR . '/includes/payment-methods/buy-now/index.php';

		if ( is_admin() ) {
			require_once CLS_PLUGIN_DIR . '/includes/admin/class-notices-registry.php';
			require_once CLS_PLUGIN_DIR . '/includes/admin/class-notices.php';
			require_once CLS_PLUGIN_DIR . '/includes/admin/notices.php';

			require_once CLS_PLUGIN_DIR . '/includes/admin/admin-actions.php';
			require_once CLS_PLUGIN_DIR . '/includes/admin/admin-filters.php';
			require_once CLS_PLUGIN_DIR . '/includes/admin/settings/stripe-connect.php';
			require_once CLS_PLUGIN_DIR . '/includes/admin/settings.php';
			require_once CLS_PLUGIN_DIR . '/includes/admin/upgrade-functions.php';
			require_once CLS_PLUGIN_DIR . '/includes/admin/reporting/class-stripe-reports.php';
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once CLS_PLUGIN_DIR . '/includes/integrations/wp-cli.php';
		}

		if ( defined( 'CL_ALL_ACCESS_VER' ) && CL_ALL_ACCESS_VER ) {
			require_once CLS_PLUGIN_DIR . '/includes/integrations/cl-all-access.php';
		}

		if ( class_exists( 'CL_Auto_Register' ) ) {
			require_once CLS_PLUGIN_DIR . '/includes/integrations/cl-auto-register.php';
		}

		// Pro.
		$pro = CLS_PLUGIN_DIR . '/includes/pro/index.php';

		if ( file_exists( $pro ) ) {
			require_once $pro;
		}
	}

	/**
	 * Applies various hooks.
	 *
	 * @since 2.6.0
	 */
	private function actions() {
		add_action( 'init', array( self::$instance, 'load_textdomain' ) );
		add_action( 'admin_init', array( self::$instance, 'database_upgrades' ) );
	}

	/**
	 * Applies various filters.
	 *
	 * @since 2.6.0
	 */
	private function filters() {
		add_filter( 'cl_payment_gateways', array( self::$instance, 'register_gateway' ) );
	}

	/**
	 * Configures core components.
	 *
	 * @since 2.6.19
	 */
	private function setup_classes() {
		$this->rate_limiting = new CL_Stripe_Rate_Limiting();
	}

	/**
	 * Performs database upgrades.
	 *
	 * @since 2.6.0
	 */
	public function database_upgrades() {
		$did_upgrade = false;
		$version     = get_option( 'cls_stripe_version' );

		if ( ! $version || version_compare( $version, CL_STRIPE_VERSION, '<' ) ) {
			$did_upgrade = true;

			switch ( CL_STRIPE_VERSION ) {
				case '2.5.8':
					cl_update_option( 'stripe_checkout_remember', true );
					break;
				case '2.8.0':
					cl_update_option( 'stripe_allow_prepaid', true );
					break;
			}
		}

		if ( $did_upgrade ) {
			update_option( 'cls_stripe_version', CL_STRIPE_VERSION );
		}
	}

	/**
	 * Loads the plugin text domain.
	 *
	 * @since 2.6.0
	 */
	public function load_textdomain() {
		 // Set filter for language directory
		$lang_dir = CLS_PLUGIN_DIR . '/languages/';

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'cls' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'cls', $locale );

		// Setup paths to current locale file
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/cl-stripe/' . $mofile;

		// Look in global /wp-content/languages/cl-stripe/ folder
		if ( file_exists( $mofile_global ) ) {
			load_textdomain( 'cls', $mofile_global );

			// Look in local /wp-content/plugins/cl-stripe/languages/ folder
		} elseif ( file_exists( $mofile_local ) ) {
			load_textdomain( 'cls', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'cls', false, $lang_dir );
		}
	}

	/**
	 * Registers the gateway.
	 *
	 * @param array $gateways Payment gateways.
	 * @return array
	 */
	public function register_gateway( $gateways ) {
		// Format: ID => Name
		$gateways['stripe'] = array(
			'admin_label'    => 'Stripe',
			'checkout_label' => __( 'Credit Card', 'essential-wp-real-estate' ),
			'supports'       => array(
				'buy_now',
			),
		);
		return $gateways;
	}
}
