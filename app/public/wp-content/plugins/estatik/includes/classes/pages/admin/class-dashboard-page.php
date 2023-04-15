<?php

/**
 * Class Es_Dashboard_Page.
 */
class Es_Dashboard_Page {

	public static function es_posts_timeout_extend() {
		return 120;
	}

    /**
     * Get estatik.net articles.
     *
     * @return bool|array
     */
    public static function get_posts() {
	    add_filter( 'http_request_timeout', array( 'Es_Dashboard_Page', 'es_posts_timeout_extend' ) );

        $response = wp_remote_get( 'https://estatik.net/wp-json/wp/v2/posts?_fields=modified,link,title&per_page=10' );

		remove_filter( 'http_request_timeout', array( 'Es_Dashboard_Page', 'es_posts_timeout_extend' ) );

        // Exit if error.
        if ( is_wp_error( $response ) ) {
            return false;
        }

        // Get the body.
        return json_decode( wp_remote_retrieve_body( $response ) );
    }

	/**
	 * Changelog info.
	 *
	 * @return array[]
	 */
	public static function get_changelog() {
		return array(
//			'' => array(
//				'date' => _x( '', 'changelog', 'es' ),
//				'changes' => array(
//					array(
//						'text' => _x( '', 'changelog', 'es' ),
//						'label' => 'bugfix',
//					),
//					array(
//						'text' => _x( '', 'changelog', 'es' ),
//						'label' => 'bugfix',
//					),
//				),
//			),
			'4.0.4' => array(
				'date' => _x( 'January, 27, 2023', 'changelog', 'es' ),
				'changes' => array(
					array(
						'text' => _x( 'Added new option for disable tel country code field.', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Added new attribute named "default" in [es_property_field] shortcode for empty property fields.', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Implemented agents registration confirmation email.', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Set dynamic content disabled by default.', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Deleted formatters for bathrooms, bedrooms fields on single property page.', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Added new plugin translations.', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Added new settings for manage PDF fields in Fields Builder.', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Fixed images uploading via front property management.', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Property management agent assignment fix added.', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Fixed search fields order in Elementor search form widget.', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Google maps callback error fix added.', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Fixed property quick edit form agents saving.', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Fixed comments saving PHP warning.', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Fixed deactivated sections render.', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Fixed breadcrumbs locations order.', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Fixed property price spaces.', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Fixed duplicated HTML input IDs in DOM.', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Recaptcha fix added.', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Fixed slick slider initializing for property boxes.', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Fixed search widget location fields loading for non authorised users.', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Fixed MLS automatic import table render', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
				),
			),
			'4.0.3' => array(
				'date' => _x( 'December, 25, 2022', 'changelog', 'es' ),
				'changes' => array(
					array(
						'text' => _x( 'Captcha issues fixed', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'FB tab fields issues fixed', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Single property pages mobile layout fixed', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'MLS ID display bug fixed', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Translation for sorting fixed', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
				),
			),
			'4.0.2' => array(
				'date' => _x( 'November, 30, 2022', 'changelog', 'es' ),
				'changes' => array(
					array(
						'text' => _x( 'Lazy load for carousel images added', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Google fonts GDPR issue fixed', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Request Info form (subject and from email issue fixed)', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'SEO issues fixed', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'Responsive js refactored', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
				),
			),
			'4.0.1' => array(
				'date' => _x( '', 'changelog', 'es' ),
				'changes' => array(
					array(
						'text' => _x( 'Added min & max map zoom setting fields', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Polylang support added', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'MLS migration fix added', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
				),
			),
			'4.0.0' => array(
				'date' => _x( '', 'changelog', 'es' ),
				'changes' => array(
					array(
						'text' => _x( 'Front-and back-end interface design updated', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Agencies support added', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'One-time payments added', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Compare feature added', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Buyer\'s & agent\'s profiles upgraded', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Requests to profile added', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'AJAX map search added', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Fields Builder considerably improved', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Data Manager improved', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'WP ALL Import support added', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'New widgets added: agencies, locations, slideshow widget', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Share via email added', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Elementor Support improved', 'changelog', 'es' ),
						'label' => 'new',
					),
					array(
						'text' => _x( 'Loads of minor fixes and improvements', 'changelog', 'es' ),
						'label' => 'new',
					),
				),
			),
			'3.11.14' => array(
				'date' => _x( 'July, 26, 2022', 'changelog date', 'es' ),
				'changes' => array(
					array(
						'text' => _x( 'Estatik settings php warning fixed (All versions)', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'PDF library fixed (Pro & Premium)', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
					array(
						'text' => _x( 'minor fixes', 'changelog', 'es' ),
						'label' => 'bugfix',
					),
				),
			),
		);
	}

    /**
     * @return array
     */
    public static function get_links() {
        return apply_filters( 'es_dashboard_get_links', array(
            'my-listings' => array(
                'name' => __( 'My listings', 'es' ),
                'url' => admin_url( 'edit.php?post_type=properties' ),
                'icon' => '<span class="es-icon es-icon_home es-icon--rounded es-icon--green"></span>',
            ),
            'settings' => array(
                'name' => __( 'Settings', 'es' ),
                'url' => admin_url( 'admin.php?page=es_settings' ),
                'icon' => '<span class="es-icon es-icon_settings es-icon--rounded es-icon--green"></span>',
            ),
            'fields-builder' => array(
                'name' => __( 'Fields builder', 'es' ),
                'url' => admin_url( 'admin.php?page=es_fields_builder' ),
                'icon' => '<span class="es-icon es-icon_apps es-icon--rounded es-icon--green"></span>',
            ),
            'add-new' => array(
                'name' => __( 'Add new property', 'es' ),
                'url' => admin_url( 'post-new.php?post_type=properties' ),
                'icon' => '<span class="es-icon es-icon_plus es-icon--rounded es-icon--green"></span>',
            ),
            'shortcodes' => array(
                'name' => __( 'Shortcodes', 'es' ),
                'url' => 'https://estatik.net/docs-category/shortcodes/',
                'icon' => '<span class="es-icon es-icon_shortcode es-icon--rounded es-icon--green"></span>',
            ),
            'agents' => array(
                'name' => __( 'Agents', 'es' ),
                'label' => '<span class="es-label es-label--green">' . __( 'PRO', 'es' ) . '</span>',
                'url' => '#',
                'icon' => '<span class="es-icon es-icon_glasses es-icon--rounded es-icon--green"></span>',
                'disabled' => true,
            ),
            'agencies' => array(
                'name' => __( 'Agencies', 'es' ),
                'label' => '<span class="es-label es-label--green">' . __( 'PRO', 'es' ) . '</span>',
                'url' => '#',
                'icon' => '<span class="es-icon es-icon_case es-icon--rounded es-icon--green"></span>',
                'disabled' => true,
            ),
            'rets-import' => array(
                'name' => __( 'MLS Import', 'es' ),
                'label' => '<span class="es-label es-label--orange">' . __( 'Premium', 'es' ) . '</span>',
                'url' => '#',
                'icon' => '<span class="es-icon es-icon_cloud-connect es-icon--rounded es-icon--green"></span>',
                'disabled' => true,
            ),
        ) );
    }

    /**
     * @return array
     */
    public static function get_carousel_items() {
        return array(
            'estatik-native' => array(
                'link' => 'https://estatik.net/product/theme-native/',
                'name' => __( 'Native Theme', 'es' ),
                'demo_link' => 'http://native.estatik.net/',
                'image_url' => ES_PLUGIN_URL . 'admin/images/native.png',
                'free' => true,
            ),
            'estatik-trendy' => array(
                'link' => 'https://estatik.net/product/theme-trendy-estatik-pro/',
                'name' => __( 'Trendy Theme', 'es' ),
                'demo_link' => 'http://trendy.estatik.net/',
                'image_url' => ES_PLUGIN_URL . 'admin/images/portal.png',
            ),
            'estatik-project' => array(
                'link' => 'https://estatik.net/product/estatik-project-theme/',
                'name' => __( 'Project Theme', 'es' ),
                'demo_link' => 'http://project.estatik.net/',
                'image_url' => ES_PLUGIN_URL . 'admin/images/portal.png',
            ),
            'estatik-portal' => array(
                'link' => 'https://estatik.net/product/portal-theme/',
                'name' => __( 'Portal Theme', 'es' ),
                'demo_link' => 'http://portal.estatik.net/',
                'image_url' => ES_PLUGIN_URL . 'admin/images/portal.png',
            ),
            'estatik-realtor' => array(
                'link' => 'https://estatik.net/product/estatik-realtor-theme/',
                'name' => __( 'Realtor Theme', 'es' ),
                'demo_link' => 'http://realtor.estatik.net/',
                'image_url' => ES_PLUGIN_URL . 'admin/images/realtor.png',
            ),
            'mortgage-calc' => array(
                'link' => 'https://estatik.net/product/estatik-mortgage-calculator/',
                'name' => __( 'Mortgage Calculator', 'es' ),
                'demo_link' => '',
                'image_url' => ES_PLUGIN_URL . 'admin/images/portal.png',
                'free' => true,
            ),
        );
    }

    /**
     * @return array
     */
    public static function get_services() {
        return array(
            array(
                'link' => 'https://estatik.net/estatik-customization/',
                'text' => __( 'We can extend plugin features and customize it to meet your requirements. To get an estimate, just fill out the form and we will get back to you with a quote.', 'es' ),
                'title' => __( 'Custom Development', 'es' ),
            ),
            array(
                'link' => 'https://estatik.net/product/installation-setup/',
                'text' => __( 'If you are limited in time or just don’t feel like setting up the plugin yourself, our team is at your service. We can help set up your WordPress website to look like our plugin or theme demo websites.', 'es' ),
                'title' => __( 'Installation & Setup', 'es' ),
            ),
			array(
				'link' => 'https://estatik.net/product/estatik-premium-setup/',
				'text' => __( 'Installation, connection to MLS, and mapping MLS fields to Estatik for every property type (Residential, Commercial, Multifamily, Lease, LotsAndLand, etc.), setting up automatic import, and launching synchronization.', 'es' ),
				'title' => __( 'Premium MLS Setup (for Premium users only)', 'es' ),
			),
//            array(
//                'link' => '',
//                'text' => __( 'Estatik Pro integration with any MLS provider via RETS or IDX on individual custom basis.', 'es' ),
//                'title' => __( 'MLS integration service', 'es' ),
//            ),
//            array(
//                'link' => '',
//                'text' => __( 'Design, development, testing of your custom real estate website.', 'es' ),
//                'title' => __( 'Turn-key website', 'es' ),
//            ),
        );
    }

	/**
	 * Render page action.
	 *
	 * @return void
	 */
	public static function render() {
	    $f = es_framework_instance();
	    $f->load_assets();
	    wp_enqueue_script( 'es-slick' );
	    wp_enqueue_script( 'es-admin' );
	    wp_enqueue_style( 'es-dashboard', ES_PLUGIN_URL . 'admin/css/dashboard.min.css', array( 'es-admin', 'es-slick' ) );

		es_load_template( 'admin/dashboard/index.php', array(
		    'links' => static::get_links(),
            'posts' => static::get_posts(),
            'products' => static::get_carousel_items(),
            'services' => static::get_services(),
			'changelog' => static::get_changelog(),
        ) );
	}
}
