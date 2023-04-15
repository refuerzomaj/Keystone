<?php

/**
 * Class Es_Profile_Shortcode.
 */
class Es_Profile_Shortcode extends Es_Shortcode {

    /**
     * Return shortcode name.
     *
     * @return string
     */
    public static function get_shortcode_name() {
        return 'es_profile';
    }

    /**
     * @return false|string
     */
    public function get_content() {
        ob_start();

        if ( get_current_user_id() ) {
			$tabs = array(
				'saved-homes' => array(
					'template' => es_locate_template( 'front/shortcodes/profile/tabs/saved-homes.php' ),
					'label' => __( 'Saved homes', 'es' ),
					'icon' => "<span class='es-icon es-icon_heart'></span>",
					'id' => 'saved-homes',
				),
				'saved-searches' => array(
					'template' => es_locate_template( 'front/shortcodes/profile/tabs/saved-searches.php' ),
					'label' => __( 'Saved searches', 'es' ),
					'icon' => "<span class='es-icon es-icon_search'></span>",
					'id' => 'saved-searches',
				),
			);

	        if ( ! ests( 'is_properties_wishlist_enabled' ) ) {
		        unset( $tabs['saved-homes'] );
	        }

	        $tabs = apply_filters( 'es_profile_get_tabs', $tabs );

            es_load_template( 'front/shortcodes/profile/profile.php', array(
                'user_entity' => es_get_user_entity(),
                'tabs' => $tabs
            ) );
        } else {
            $shortcode = es_get_shortcode_instance( 'es_authentication' );
            echo $shortcode->get_content();
        }
        return ob_get_clean();
    }
}
