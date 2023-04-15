<?php

/**
 * Customize property excerpt.
 *
 * @param $excerpt
 *
 * @return mixed
 */
function es_the_excerpt( $excerpt ) {
    $post = get_post( 0 );
    if ( $excerpt && $post instanceof WP_Post && $post->post_type == 'properties' ) {
        $excerpt = wp_trim_words( $excerpt, ests( 'excerpt_length' ), '...' );
    }
    return $excerpt;
}
add_filter( 'the_excerpt', 'es_the_excerpt' );

if ( ! function_exists( 'es_search_render_field' ) ) {

    /**
     * Render advanced search field.
     *
     * @param $field
     * @param array $attributes
     * @param null $force_type
     */
    function es_search_render_field( $field, $attributes = array(), $force_type = null ) {
        $field_config = es_search_get_field_config( $field );
        if ( $field_config && ! empty( $field_config['search_support'] ) ) {
            $search_settings = $field_config['search_settings'];
            $type = $force_type ? $force_type : $search_settings['type'];
            $uid = uniqid();
            $selected_value = isset( $attributes[ $field ] ) ? $attributes[ $field ] : null;
            $selected_value = isset( $_GET[ $field ] ) ? es_clean( $_GET[ $field ] ) : $selected_value;

            if ( empty( $search_settings['values'] ) && ! empty( $search_settings['values_callback'] ) ) {
                if ( ! empty( $search_settings['values_callback']['args'] ) ) {
                    $values = call_user_func_array( $search_settings['values_callback']['callback'], $search_settings['values_callback']['args'] );
                } else {
                    $values = call_user_func( $search_settings['values_callback']['callback'] );
                }

                if ( $values && ! is_wp_error( $values ) ) {
                    $search_settings['values'] = $values;
                }
            }

            $field_html = null;

            switch ( $type ) {
                case 'price':
                    $values = array();
                    if ( ests( 'is_same_price_for_categories_enabled' ) ) {
                        $values['min'] = ests( 'min_prices_list' ) ? explode( ',', ests( 'min_prices_list' ) ) : array();
                        $values['max'] = ests( 'max_prices_list' ) ? explode( ',', ests( 'max_prices_list' ) ) : array();

                        $values['min'] = array_combine( $values['min'], $values['min'] );
                        $values['max'] = array_combine( $values['max'], $values['max'] );

                        $prices_list = array();
                    } else {
                        if ( $prices_list = ests( 'custom_prices_list' ) ) {
                            foreach ( $prices_list as $price_item ) {
                                if ( empty( $price_item['type'] ) && empty( $price_item['category'] ) ) {
                                    $values['min'] = explode( ',', $price_item['min_prices_list'] );
                                    $values['max'] = explode( ',', $price_item['max_prices_list'] );
                                    break;
                                }
                            }
                        }
                    }

                    $field_html = "<div class='es-field-row es-field-row__range js-search-field-container'>";

                    foreach ( array( 'min', 'max' ) as $field_range ) {
                        if ( ! empty( $values[ $field_range ] ) ) {
	                        $values[ $field_range ] = es_format_values( $values[ $field_range ], $field_config['formatter'] );
                        }
                        $range_label = ! empty( $search_settings['range_label'] ) ? $search_settings['range_label'] : $field_config['label'];
                        $field_name = $field_range . '_' . $field;
                        $value = isset( $attributes[ $field_name ] ) ? $attributes[ $field_name ] : null;
                        $value = isset( $_GET[ $field_name ] ) ? es_clean( $_GET[ $field_name ] ) : $value;

                        $config = array(
                            'type' => ! empty( $values[ $field_range ] ) ? 'select' : 'number',
                            'label' => $field_range == 'min' ? $range_label : false,
                            'value' => $value,
                            'attributes' => array(
                                'data-prices-list' => es_esc_json_attr( $prices_list ),
                                'id' => sprintf( '%s-%s-%s', $field, $field_range, $uid ),
                                'class' => 'js-es-search-field js-es-search-field--price ' . sprintf( 'js-es-search-field--price-%s', $field_range ),
                                'data-base-name' => $field,
                                'data-placeholder' => sprintf( __( 'No %s', 'es' ), $field_range ),
                            ),
                            'options' => ! empty( $values[ $field_range ] ) ? array( '' => '' ) + $values[ $field_range ] : array(),
                        );

                        $field_html .= es_framework_get_field_html( $field_name, es_parse_args( $config, $search_settings ) );
                    }
                    $field_html .= "</div>";

                    break;
                case 'select':
                case 'list':
                case 'dropdown':
                    $search_settings['values'] = es_format_values( $search_settings['values'], $field_config['formatter'] );
                    $values = $search_settings['values'];

                    if ( ! empty( $search_settings['attributes']['data-placeholder'] ) ) {
                        $values = array( '' => '' ) + $values;
                    }

                    if ( 'keywords' == $field && $selected_value ) {
                        $values = array_combine( $selected_value, $selected_value );
                    }

//                    if ( ! $search_settings['attributes']['multiple'] ) {
//                        $values = array( '' => _x( 'All', 'search dropdown placeholder', 'es' ) ) + $values;
//                    }

                    $config = array(
                        'type'       => $type,
                        'options'    => $values,
                        'value' => $selected_value,
                        'attributes' => array(
                            'id' => sprintf( '%s-%s', $field, $uid ),
                            'class' => sprintf( 'js-es-search-field js-es-search-field--%s', $field ),
                            'data-base-name' => $field,
                        ),
                        'label'      => $field_config['label'],
                    );
                    $search_settings['wrapper_class'] .= ' js-search-field-container';
                    $field_html = es_framework_get_field_html( $field, es_parse_args( $config, $search_settings ) );
                    break;

                case 'checkboxes':
                    if ( ! empty( $search_settings['values'] ) ) {
                        $values = es_format_values( $search_settings['values'], $field_config['formatter'] );
                        $visible_items = ! empty( $search_settings['visible_items'] ) ? $search_settings['visible_items'] : false;

                        $config = array(
                            'type'       => $type,
                            'options'    => $values,
                            'disable_hidden_input' => true,
                            'value' => $selected_value,
                            'visible_items' => $visible_items,
                            'button_label' => ! empty( $search_settings['show_more_label'] ) ? $search_settings['show_more_label'] : '',
                            'attributes' => array(
                                'id' => sprintf( '%s-%s', $field, $uid ),
                                'class' => sprintf( 'js-es-search-field js-es-search-field--%s', $field ),
                                'data-base-name' => $field,
                            ),
                            'label'      => $field_config['label'],
                        );

                        $search_settings['wrapper_class'] .= ' js-search-field-container';
                        $field_html = es_framework_get_field_html( $field, es_parse_args( $config, $search_settings ) );
                    }
                    break;

                case 'radio-bordered':
                case 'checkboxes-bordered':
                case 'checkboxes-boxed':
                    if ( ! empty( $search_settings['values'] ) ) {
                        $options = $search_settings['values'];
	                    $field_name = $field;
	                    $field_class = sprintf( 'js-es-search-field js-es-search-field--%s', $field_name );

                        if ( in_array( $field, array( 'bedrooms', 'bathrooms', 'half_baths' ) ) ) {
                            array_walk( $search_settings['values'], 'es_arr_add_suffix_plus' );
                            $options = array( '' => __( 'Any', 'es' ) ) + $search_settings['values'];
                            $field_name = 'from_' . $field;
                            $selected_value = isset( $attributes[ $field_name ] ) ? $attributes[ $field_name ] : null;
                            $selected_value = isset( $_GET[ $field_name ] ) ? es_clean( $_GET[ $field_name ] ) : $selected_value;
                        }

                        $config = array(
                            'type' => $type,
                            'options' => $options,
                            'label' => $field_config['label'],
                            'value' => $selected_value,
                            'disable_hidden_input' => true,
                            'attributes' => array(
                                'id' => sprintf( '%s-%s', $field_name, $uid ),
                                'class' => $field_class,
                                'data-formatter' => $field_config['formatter'],
                                'data-base-name' => $field,
                            ),
                        );
                        $search_settings['wrapper_class'] .= ' js-search-field-container';
                        $field_html = es_framework_get_field_html( $field_name, es_parse_args( $config, $search_settings ) );
                    }
                    break;

                case 'range':
                    $field_html = "<div class='es-field-row es-field-row__range js-search-field-container'>";
                    foreach ( array( 'min', 'max' ) as $field_range ) {
                        $range_label = ! empty( $search_settings['range_label'] ) ? $search_settings['range_label'] : $field_config['label'];
                        $values = ! empty( $search_settings['values_' . $field_range] ) ? $search_settings['values_' . $field_range] : array();
                        $values = es_format_values( $values, $field_config['formatter'] );
                        $field_name = $field_range . '_' . $field;
                        $selected_value = isset( $attributes[ $field_name ] ) ? $attributes[ $field_name ] : null;
                        $selected_value = isset( $_GET[ $field_name ] ) ? es_clean( $_GET[ $field_name ] ) : $selected_value;
                        $config = array(
                            'type' => $values ? 'select' : 'number',
                            'label' => $field_range == 'min' ? $range_label : false,
                            'value' => $selected_value,
                            'attributes' => array(
                                'id' => sprintf( '%s-%s-%s', $field, $field_range, $uid ),
                                'min' => ests( 'search_min_' . $field ),
                                'max' => ests( 'search_max_' . $field ),
                                'data-formatter' => $field_config['formatter'],
                                'class' => sprintf( 'js-es-search-field js-es-search-field--%s', $field ),
                                'data-base-name' => $field,
                                'data-placeholder' => sprintf( __( 'No %s', 'es' ), $field_range ),
                                'placeholder' => sprintf( __( 'No %s', 'es' ), $field_range ),
                            ),
                            'options' => array( '' => '' ) + $values,
                        );

                        $field_html .= es_framework_get_field_html( $field_name, es_parse_args( $config, $search_settings ) );
                    }
                    $field_html .= "</div>";

                    break;
                default:
                    $search_settings['wrapper_class'] .= ' js-search-field-container';
                    $field_config = array_merge( $field_config, $search_settings );
                    $field_config['value'] = $selected_value;
                    $field_html = es_framework_get_field_html( $field, $field_config );
            }

            if ( ! empty( $field_html ) || ( ! empty( $attributes['type'] ) && $attributes['type'] == 'range' ) ) {
                echo apply_filters( 'es_search_render_field_html', $field_html, $field, $attributes, $force_type );
            }

	        if ( ! empty( $search_settings['range'] ) &&  $type != 'range' ) {
		        $field_config['type'] = 'range';
		        $field_config['search_settings']['type'] = 'range';
		        es_search_render_field( $field, $field_config, 'range' );
	        }
        }
    }
}
add_action( 'es_search_render_field', 'es_search_render_field', 10, 2 );

/**
 * @param WP_Admin_Bar $admin_bar
 */
function es_admin_bar_edit_property_link( $admin_bar ) {

    if ( is_singular( Es_Property::get_post_type_name() ) && current_user_can( 'edit_post', get_the_ID() ) ) {
        global $wp_query;
        $admin_bar->add_menu( array(
            'id'    => 'edit-property',
            'title' => __( 'Edit property', 'es' ),
            'href'  => get_edit_post_link( $wp_query->post->ID ),
            'meta'  => array(
                'title' => __( 'Edit property', 'es' ),
            ),
        ));
    }
}
add_action( 'admin_bar_menu', 'es_admin_bar_edit_property_link', 100 );

if ( ! function_exists( 'es_privacy_policy' ) ) {

    /**
     * Display terms & conditions text / checkbox.
     *
     * @param $context
     */
    function es_privacy_policy( $context ) {
        $content = null;
        $terms_forms = ests( 'terms_forms' );
        $terms_conditions_page_id = ests( 'terms_conditions_page_id' );
        $privacy_policy_page_id = ests( 'privacy_policy_page_id' );

        if ( $terms_forms && is_array( $terms_forms ) && in_array( $context, $terms_forms ) ) {

            $terms = __( 'Terms of Use', 'es' );
            $policy = __( 'Privacy Policy', 'es' );

            if ( $terms_conditions_page_id && get_post_status( $terms_conditions_page_id ) == 'publish' ) {
                $terms = "<a href='" . esc_url( get_permalink( $terms_conditions_page_id ) ) . "' class='es-terms-link'>{$terms}</a>";
            }

            if ( $privacy_policy_page_id && get_post_status( $privacy_policy_page_id ) == 'publish' ) {
                $policy = "<a href='" . esc_url( get_permalink( $privacy_policy_page_id ) ) . "' class='es-terms-link'>{$policy}</a>";
            }

            $context_label = null;

            if ( $context == 'request_form' ) {
                $context_label = _x( 'REQUEST INFO', 'terms & conditions', 'es' );
            }

            if ( $context == 'sign_up_form' ) {
                $context_label = _x( 'SIGN UP', 'terms & conditions', 'es' );
            }

            $context_label = apply_filters( 'es_es_privacy_policy_button_label', $context_label, $content );

            $content = sprintf( __( 'By clicking the %s button you agree to the %s and %s', 'es' ), '«' . $context_label . '»', $terms, $policy );
            $content = "<div class='es-terms-text'>{$content}</div>";

            if ( 'checkbox' == ests( 'terms_input_type' ) ) {
                $content = __( 'I agree to the Terms of Use and Privacy Policy', 'es' );
                $content = es_framework_get_field_html( 'terms_conditions', array(
                    'type' => 'checkbox',
                    'attributes' => array(
                        'required' => 'required',
                        'id' => 'terms-conditions-' . uniqid()
                    ),
                ) ) . $content;
            }
        }

        if ( ! empty( $content ) ) {
            $content = "<div class='es-privacy-policy-container'>{$content}</div>";
        }

        echo apply_filters( 'es_privacy_policy_content', $content, $context );
    }
}
add_action( 'es_privacy_policy', 'es_privacy_policy', 10, 1 );

if ( ! function_exists( 'es_recaptcha' ) ) {

    /**
     * @param $context
     */
    function es_recaptcha( $context = 'basic' ) {
        $forms = ests( 'recaptcha_forms' );

        if ( ! $context || ( is_array( $forms ) && in_array( $context, $forms ) ) ) {
            $site_key = ests( 'recaptcha_site_key' );
            $site_secret = ests( 'recaptcha_secret_key' );

            if ( $site_key && $site_secret ) {
                $lang = es_get_locale();
                $recaptcha_version = ests( 'recaptcha_version' );
                $siteKey = $site_key;
                $uid = uniqid();

                $url = 'https://www.google.com/recaptcha/api.js';
                $args = array( 'hl' => $lang );

                if ( 'v3' == $recaptcha_version ) {
                    $args['render'] = $siteKey; ?>
                    <input type="hidden" name="g-recaptcha-response" id="recaptchaResponse-<?php echo $uid; ?>"/>
                    <?php
                } else if ( 'v2' == $recaptcha_version ) : $args['onload'] = 'es_initialize_recaptcha'; ?>
                    <div class="es-recaptcha-wrapper">
                        <div class="js-g-recaptcha" id="g-recaptcha-<?php echo $uid; ?>"></div>
                    </div>
                <?php endif;

                wp_enqueue_script( 'es-google-recaptcha', add_query_arg( $args, $url ) );

	            if ( 'v3' == $recaptcha_version ) {
		            wp_add_inline_script( 'es-google-recaptcha', "
					(function(){
					    var interval = setInterval(function(){
                            if ( window.grecaptcha ) {
                                window.grecaptcha.ready(function () {
                                    if ( document.getElementById('recaptchaResponse-" . $uid . "') ) {
                                        window.grecaptcha.execute('" . $siteKey . "', { action: '" . $context . "' }).then(function (token) {
                                            var recaptchaResponse = document.getElementById('recaptchaResponse-" . $uid . "');
                                            recaptchaResponse.value = token;
                                        });
                                    }
                                });
                                clearInterval(interval);
                            }
                        });
					})();    
                    ");
	            }
            }
        }
    }
}
add_action( 'es_recaptcha', 'es_recaptcha' );

/**
 * Add sorting labels to the settings array.
 *
 * @param $values array
 * @param $name string Setting name.
 *
 * @return mixed
 */
function es_add_sort_labels_settings( $values, $name ) {
    if ( 'properties_sorting_options' == $name || 'properties_default_sorting_option' == $name ) {
        $labels = es_get_terms_list( 'es_label', false, array(), 'all' );

        if ( $labels && ! is_wp_error( $labels ) ) {
            foreach ( $labels as $label ) {
                $values[ $label->slug ] = $label->name;
            }
        }
    }

    return $values;
}
add_filter( 'es_settings_get_available_values', 'es_add_sort_labels_settings', 10, 2 );

/**
 * Display login / register popup.
 *
 * @return void
 */
function es_authentication_popup() {
    es_load_template( 'front/popup/authentication.php' );
}
add_action( 'wp_footer', 'es_authentication_popup' );

/**
 * @param $post_id int
 * @param $post WP_Post
 */
function es_set_settings_pages_ids( $post_id, $post ) {
    if ( ! ests( 'profile_page_id' ) && ! empty( $post->post_content ) && stristr( $post->post_content, '[es_profile' ) !== false ) {
        ests_save_option( 'profile_page_id', $post_id );
    }

    if ( ! ests( 'search_results_page_id' ) && ! empty( $post->post_content ) && stristr( $post->post_content, 'ignore_search="0"' ) !== false ) {
	    ests_save_option( 'search_results_page_id', $post_id );
    }

    if ( ! ests( 'map_search_page_id' ) && ! empty( $post->post_content ) && stristr( $post->post_content, 'layout="half_map"' ) !== false ) {
	    ests_save_option( 'map_search_page_id', $post_id );
    }
}
add_action( 'save_post_page', 'es_set_settings_pages_ids', 10, 2 );

/**
 * @param $avatar
 * @param $id_or_email
 * @param $size
 * @param $default
 * @param $alt
 *
 * @return string
 */
function es_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
    $user = false;

    if ( is_numeric( $id_or_email ) ) {

        $id = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );

    } elseif ( is_object( $id_or_email ) ) {

        if ( ! empty( $id_or_email->user_id ) ) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by( 'id' , $id );
        }

    } else {
        $user = get_user_by( 'email', $id_or_email );
    }

    if ( $user && is_object( $user ) && ( $entity = es_get_user_entity( $user->ID ) ) ) {
        $attachment_id = $entity->avatar_id;
        if ( $attachment_id ) {
            $src = wp_get_attachment_url( $attachment_id );
        } else {
            $src = esc_attr( es_user_get_default_image_url( $user->ID ) );
        }

	    $avatar = "<img alt='{$alt}' src='{$src}' class='avatar avatar-{$size}' width='{$size}' height='{$size}'/>";
    }

    return $avatar;
}
add_action( 'get_avatar', 'es_get_avatar', 10, 5 );

/**
 * @param $states array
 * @param $post WP_Post
 *
 * @return mixed
 */
function es_display_post_states( $states, $post ) {
    $pages = array(
        'login_page_id' => __( 'Estatik Authentication', 'es' ),
        'profile_page_id' => __( 'Estatik Profile', 'es' ),
        'map_search_page_id' => __( 'Estatik Half map', 'es' ),
        'search_results_page_id' => __( 'Estatik Search results', 'es' )
    );

    foreach ( $pages as $key => $label ) {
        $page_id = ests( $key );
        if ( $page_id && get_post_status( $page_id ) == 'publish' && $post->ID == $page_id ) {
            $states[ $key ] = $label;
        }
    }

    return $states;
}
add_filter( 'display_post_states', 'es_display_post_states', 10, 2 );

/**
 * Display dynamic content on single property page.
 *
 * @return void
 */
function es_render_dynamic_content() {
    if ( ests( 'is_dynamic_content_enabled' ) && ests( 'dynamic_content' ) ) {
        do_action( 'es_before_dynamic_content' ); ?>
        <div class='es-dymanic-content content-font'>
        <?php echo do_shortcode( strtr( stripslashes( ests( 'dynamic_content' ) ), array(
            '{blog_name}' => get_bloginfo( 'name' )
        ) ) );
        ?>
        </div><?php
        do_action( 'es_after_dynamic_content' );
    }
}
add_action( 'es_after_single_property_content', 'es_render_dynamic_content', 1 );

if ( ! function_exists( 'es_powered_by' ) ) {

    /**
     * Render powered by.
     *
     * @return void
     */
    function es_powered_by() {
        echo "<div class='es-powered content-font'>" . sprintf( __( 'Powered by %s' ), "<a target='_blank' href='https://estatik.net'>" . __( 'Estatik', 'es' ) . "</a>" ) . "</div>";
    }
}
add_action( 'es_after_listings', 'es_powered_by' );
add_action( 'es_after_single_content', 'es_powered_by' );
add_action( 'es_after_authentication', 'es_powered_by' );
add_action( 'es_after_profile', 'es_powered_by' );

if ( ! function_exists( 'es_login_logo' ) ) {

    /**
     * Add custom logo on wp login page.
     *
     * @return void
     */
    function es_login_logo() {
        if ( ests( 'logo_attachment_id' ) && ( $url = wp_get_attachment_image_url( ests( 'logo_attachment_id' ), 'medium' ) ) ) : ?>
            <style type="text/css">
                #login h1 a, .login h1 a {
                    background-image: url(<?php echo $url; ?>);
                    height:150px;
                    width:150px;
                    background-size: contain;
                    background-repeat: no-repeat;
                    padding-bottom: 30px;
                }
            </style>
        <?php endif; }
}
add_action( 'login_enqueue_scripts', 'es_login_logo' );

/**
 * Change term link for location taxonomy.
 *
 * @param $url
 * @param $term
 * @param $taxonomy
 * @return string|string[]
 */
function es_location_term_permalink( $url, $term, $taxonomy ) {
    if ( 'es_location' == $taxonomy ) {
        $location_type = get_term_meta( $term->term_id, 'type', true );
        $taxonomy = get_taxonomy( $taxonomy );
        // If is state.
        if ( 'administrative_area_level_1' == $location_type && ests( 'state_slug' ) ) {
            $url = str_replace('/' . $taxonomy->rewrite['slug'], '/' . ests( 'state_slug' ), $url);
        } else if ( 'locality' == $location_type && ests( 'city_slug' ) ) {
            $url = str_replace('/' . $taxonomy->rewrite['slug'], '/' . ests( 'city_slug' ), $url);
        }
    }

    return $url;
}
add_filter( 'term_link', 'es_location_term_permalink', 10, 3 );

/**
 * Change location taxonomy request for cities and states.
 *
 * @param $query
 * @return mixed
 */
function es_location_term_request( $query ) {
    $name = null;
    $state_slug = ests( 'state_slug' );
    $city_slug = ests( 'city_slug' );

    if ( ( ! empty( $_SERVER['REQUEST_URI'] ) &&
        ( ( $state_slug && stristr( $_SERVER['REQUEST_URI'], $state_slug ) ) ||
          ( $city_slug  && stristr( $_SERVER['REQUEST_URI'], $city_slug ) ) ) ) || empty( $_SERVER['REQUEST_URI'] ) ) {
        if ( ! empty( $query['name'] ) ) {
            $name = $query['name'];
            unset( $query['name'] );
        } else if ( ! empty( $query['attachment'] ) ) {
            $name = $query['attachment'];
            unset( $query['attachment'] );
        } else if ( ! empty( $query['pagename'] ) ) {
            if ( ( $state_slug && stristr( $query['pagename'], $state_slug ) ) ||
                ( $city_slug && stristr( $query['pagename'], $state_slug ) ) ) {
                $name = explode( '/', $query['pagename'] );
                $name = $name[ count( $name ) - 1 ];
                $query = array();
            }
        }

        if ( $name && term_exists( $name, 'es_location' ) ) {
            $query['es_location'] = $name;
        }
    }

    return $query;
}
add_filter( 'request', 'es_location_term_request', 1, 1 );

/**
 * Disable featured image for property single page.
 *
 * @param $html
 * @param $image_post
 * @return string
 */
function es_disable_single_featured_image( $html, $image_post ) {
    $image_post = get_post( $image_post );

    return $image_post && is_singular( 'properties' ) && $image_post->post_type == 'properties' &&
        $image_post->ID == get_the_ID() ? '' : $html;
}
add_filter( 'post_thumbnail_html', 'es_disable_single_featured_image', 10, 2 );

/**
 * Delete children locations.
 *
 * @param $term
 * @param $taxonomy
 */
function es_delete_children_locations( $term, $taxonomy ) {
    if ( 'es_location' == $taxonomy ) {
        /** @var Int[] $children_terms */
        $children_terms = es_get_children_locations( $term, $taxonomy );

        if ( $children_terms ) {
            remove_filter( 'pre_delete_term', 'es_delete_children_locations', 10 );

            foreach ( $children_terms as $child_term ) {
                if ( $child_term == $term ) continue;
                wp_delete_term( $child_term, $taxonomy );
            }
        }
    }
}
add_action( 'pre_delete_term', 'es_delete_children_locations', 10, 2 );

/**
 * Prepare date and date-time values for getting.
 *
 * @param $value
 * @param $field
 *
 * @return mixed
 */
function es_property_alter_get_field_value( $value, $field ) {
    $field_info = es_property_get_field_info( $field );

    if ( ! empty( $field_info['type'] ) && in_array( $field_info['type'], array( 'date', 'date-time' ) ) && $value ) {
        $format = $field_info['attributes']['data-date-format'];
        $value = date( $format, $value );
    }

    return $value;
}
add_filter( 'es_property_get_field_value', 'es_property_alter_get_field_value', 10, 2 );

/**
 * Prepare date and date-time values for saving.
 *
 * @param $value
 * @param $field
 * @return mixed
 */
function es_property_alter_save_field_value( $value, $field ) {
    $field_info = es_property_get_field_info( $field );

    if ( ! empty( $field_info['type'] ) && in_array( $field_info['type'], array( 'date', 'date-time' ) ) && $value ) {
        $format = $field_info['attributes']['data-date-format'];

        $value = DateTime::createFromFormat( $format, $value );

        if ( $field_info['type'] == 'date' ) {
            $value->setTime( 0, 0, 0 );
        }

        $value = $value instanceof DateTime ? $value->getTimestamp() : null;
    }

    return $value;
}
add_filter( 'es_property_save_field_value', 'es_property_alter_save_field_value', 10, 2 );

/**
 * Generate search keywords after property save action.
 *
 * @param $data
 * @param $entity Es_Entity
 */
function es_property_generate_keywords( $data, $entity ) {
    $post_id = $entity->get_id();
    $property = es_get_property( $post_id );
    $keywords_fields = apply_filters( 'es_property_keywords_fields', array( 'post_title', 'address', 'ID' ) );
    $property->delete_field_value( 'keywords' );

    foreach ( $keywords_fields as $field ) {
        if ( $value = $property->{$field} ) {
            add_post_meta( $post_id, 'es_property_keywords', $value, false );
        }
    }
}
add_action( 'es_property_after_save_fields', 'es_property_generate_keywords', 10, 2 );

/**
 * Auto tags function.
 *
 * @param $post_id
 * @param $post
 */
function es_generate_property_tags( $post_id ) {
    $post = get_post( $post_id );

    if ( ! empty( $post->post_content ) && ests( 'is_auto_tags_enabled' ) ) {

        $append_tags = array();

        $tags = get_terms( array(
            'taxonomy' => 'es_tag',
            'hide_empty' => false,
            'fields' => 'id=>name',
        ) );

        if ( ! empty( $tags ) ) {
            foreach ( $tags as $id => $tag ) {
                if ( stristr( $post->post_content, $tag ) ) {
                    $append_tags[] = $id;
                }
            }
        }

        if ( ! empty( $append_tags ) ) {
            wp_set_post_terms( $post_id, $append_tags, 'es_tag', true );
        }
    }
}
add_action( 'save_post_properties', 'es_generate_property_tags', 10 );

/**
 * @param $value
 * @param $field
 * @return mixed
 */
function es_get_the_formatter_post_content( $value, $field ) {
    if ( 'post_content' == $field ) {
        if ( ests( 'is_auto_tags_enabled' ) && ests( 'is_clickable_tags_enabled' ) ) {
            $tags = get_terms( array(
                'taxonomy' => 'es_tag',
                'hide_empty' => true,
                'fields' => 'id=>name',
            ) );
            $replace = array();

            /** @var $tags string[] */
            if ( ! empty( $tags ) ) {
                foreach ( $tags as $id => $tag ) {
                    $replace[ $tag ] = "<a href='" . get_term_link( $id, 'es_tag' ) . "'>{$tag}</a>";
                }
            }

            if ( $replace ) {
                $value = strtr( $value, $replace );
            }
        }
    }

    return $value;
}
add_filter( 'es_get_the_formatted_field', 'es_get_the_formatter_post_content', 10, 2 );

/**
 * Delete property attachments.
 *
 * @param $post_id
 */
function es_property_delete_attachments( $post_id ) {
    if ( es_is_property( $post_id ) ) {
        $attachments = get_children( array(
            'post_type' => 'attachment',
            'post_parent' => $post_id,
            'fields' => 'ids',
        ) );

        if ( ! empty( $attachments ) ) {
            foreach ( $attachments as $attachment_id ) {
                wp_delete_attachment( $attachment_id, true );
            }
        }
    }
}
add_action( 'before_delete_post', 'es_property_delete_attachments', 10, 1 );

/**
 * @param $classes
 * @param $class
 * @param $post_id
 *
 * @return mixed
 */
function es_entities_post_class( $classes, $class, $post_id ) {
	$entity = es_get_entity_by_id( $post_id );

	if ( $entity ) {
		$classes[] = 'es-post-entity';
	}
	return $classes;
}
add_filter( 'post_class', 'es_entities_post_class', 10, 3 );

/**
 * @param $str
 *
 * @return string
 */
function es_sanitize_title_intl( $str ) {
	$chars = array(
		"Є"=>"YE","І"=>"I","Ѓ"=>"G","і"=>"i","№"=>"#","є"=>"ye","ѓ"=>"g",
		"А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
		"Е"=>"E","Ё"=>"YO","Ж"=>"ZH",
		"З"=>"Z","И"=>"I","Й"=>"J","К"=>"K","Л"=>"L",
		"М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R",
		"С"=>"S","Т"=>"T","У"=>"U","Ф"=>"F","Х"=>"X",
		"Ц"=>"C","Ч"=>"CH","Ш"=>"SH","Щ"=>"SHH","Ъ"=>"'",
		"Ы"=>"Y","Ь"=>"","Э"=>"E","Ю"=>"YU","Я"=>"YA",
		"а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
		"е"=>"e","ё"=>"yo","ж"=>"zh",
		"з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l",
		"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
		"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"x",
		"ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"shh","ъ"=>"",
		"ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
		"—"=>"-","«"=>"","»"=>"","…"=>""
	);

	return strtr( $str, $chars );
}

add_action( 'sanitize_title', 'es_sanitize_title_intl', 8 );

/**
 * @param $plugin
 */
function es_demo_content_redirect( $plugin ) {
	$page = filter_input( INPUT_GET, 'page' );
	if ( $plugin == ES_PLUGIN_BASENAME && $page != 'tgmpa-install-plugins' ) {
		$checked = ! empty( $_POST['checked'] ) ? $_POST['checked'] : array();
        if ( es_need_migration() ) {
	        exit ( wp_redirect( 'admin.php?page=es_migration' ) );
        } else if ( ! es_is_demo_executed() && count( $checked ) <=1 ) {
			exit ( wp_redirect( 'admin.php?page=es_demo' ) );
		}
	}
}
add_action( 'activated_plugin', 'es_demo_content_redirect' );

/**
 * WP Multilang Support.
 *
 * @param $config
 *
 * @return mixed
 */
function es_wpm_load_config( $config ) {

	$config['post_types']['properties'] = array();
	$config['post_types']['agent'] = array();
	$config['post_types']['agency'] = array();

	return $config;
}
add_filter( 'wpm_load_config', 'es_wpm_load_config' );

/**
 * Fluh rewrite rules when slug for properties post type is changed.
 *
 * @return void
 */
function es_flush_rewrite_rules() {
	if ( ! get_option( 'es_flush_executed' ) ) {
		flush_rewrite_rules();
		update_option( 'es_flush_executed', 1 );
	}
}
add_action( 'init', 'es_flush_rewrite_rules', 999 );

/**
 * @param $post_types
 *
 * @return mixed
 */
function es_et_builder_third_party_post_types( $post_types ) {
    $post_types[] = 'properties';
    return $post_types;
}
add_filter( 'et_builder_third_party_post_types', 'es_et_builder_third_party_post_types' );

/**
 * @param $value
 *
 * @return mixed
 */
function es_et_get_option_divi_thumbnails( $value ) {
    if ( is_singular( es_builders_supported_post_types() ) ) {
        return false;
    }

    return $value;
}
add_filter( 'et_get_option_et_divi_divi_thumbnails', 'es_et_get_option_divi_thumbnails' );

/**
 * @param $fields
 *
 * @return mixed
 */
function es_handle_alt_description( $fields ) {
	$id = get_the_ID();
    $post = get_post( $id );

	if ( $id && es_get_entity_by_id( $id ) && in_array( $post->post_type, es_builders_supported_post_types() ) ) {
		$elementor_editor_mode = es_is_elementor_builder_enabled( $id );
		$divi_builder = function_exists( 'et_pb_is_pagebuilder_used' ) && et_pb_is_pagebuilder_used( $id );

        if ( $elementor_editor_mode || $divi_builder ) {
            $fields['alternative_description'] = array(
		        'type' => 'editor',
		        'tab_machine_name' => 'description',
		        'order' => 1,
		        'label' => __( 'Alt Description', 'es' ),
                'editor_id' => 'alternative_description',
	        );
        }

		if ( ! $elementor_editor_mode && ! $divi_builder ) {
			unset( $fields['alternative_description'] );
		}
	}

    return $fields;
}
add_filter( 'es_property_default_fields', 'es_handle_alt_description' );

/**
 * @return void
 */
function es_activation_handler() {
	update_option( 'es_flush_executed', 0 );
}
add_action( 'es_activation', 'es_activation_handler' );

/**
 * @param $lostpassword_url
 * @param $redirect
 *
 * @return mixed
 */
function es_alter_lostpassword_url( $lostpassword_url ) {
    if ( es_post( 'es_user_login' ) ) {
        $lostpassword_url = add_query_arg( 'auth_item', 'reset-form', es_post( '_wp_http_referer' ) );
    }
    return $lostpassword_url;
}
add_filter( 'lostpassword_url', 'es_alter_lostpassword_url', 10, 1 );

/**
 * @param $tags
 * @param $context
 *
 * @return mixed
 */
function es_alter_wpkses_post_tags( $tags, $context ) {
	if ( 'post' === $context ) {
		$tags['iframe'] = array(
			'src'             => true,
			'height'          => true,
			'width'           => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
			'title' => true,
			'allow' => true,
		);
	}

	return $tags;
}

add_filter( 'wp_kses_allowed_html', 'es_alter_wpkses_post_tags', 10, 2 );

/**
 * @param $mimes
 *
 * @return mixed
 */
function es_ttf_mime_type( $mimes ) {
	$mimes['ttf'] = 'font/ttf';

	return $mimes;
}
add_filter( 'upload_mimes', 'es_ttf_mime_type' );

/**
 * @param $data
 * @param $file
 * @param $filename
 * @param $mimes
 * @param $real_mime
 *
 * @return mixed
 */
function es_font_correct_filetypes( $data, $file, $filename, $mimes ) {
	if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
		return $data;
	}

	$wp_file_type = wp_check_filetype( $filename, $mimes );

	if ( 'ttf' === $wp_file_type['ext'] ) {
		$data['ext'] = 'ttf';
		$data['type'] = 'font/ttf';
	}

	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'es_font_correct_filetypes', 10, 4 );

/**
 * @param $post_id
 */
function es_pmxi_attach_images( $post_id ) {
    if ( es_is_property( $post_id ) ) {
        $attachments = get_posts( array(
            'post_type' => 'attachment',
            'post_parent' => $post_id,
            'posts_per_page' => -1,
            'fields' => 'ids',
        ) );

        if ( ! empty( $attachments ) ) {
            foreach ( $attachments as $order => $attachment_id ) {
                if ( ! get_post_meta( $attachment_id, 'es_attachment_type', true ) ) {
                    update_post_meta( $attachment_id, 'es_attachment_type', 'gallery' );
                    update_post_meta( $attachment_id, 'es_attachment_order', $order );
                }
            }
        }
    }
}
add_action( 'pmxi_saved_post', 'es_pmxi_attach_images', 10, 1 );

/**
 * @param $wp_query WP_Query
 */
function es_set_entities_per_page_query( $wp_query ) {
    $post_types = es_builders_supported_post_types();

    foreach ( $post_types as $post_type ) {
	    if ( $wp_query->is_post_type_archive( $post_type ) && $wp_query->is_main_query() ) {
            $name = $post_type == 'properties' ? 'properties_per_page' : 'agency_agents_per_page';
            $wp_query->set( 'posts_per_page', ests( $name ) );
	    }
    }
}
add_action( 'pre_get_posts', 'es_set_entities_per_page_query', 20 );