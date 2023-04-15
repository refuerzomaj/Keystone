<?php
// Getting global $pref val
global $pref;
$provider = WPERECCP()->front->listing_provider;
// START MAP DATA
$map_data = wp_json_encode(
    array(
        'enable_geolocation' => cl_admin_get_option('enable_geolocation') != 1 ? false : true,
        'default_latitude'   => cl_admin_get_option('default_latitude'),
        'default_longitude'  => cl_admin_get_option('default_longitude'),
        'default_zoom'       => cl_admin_get_option('default_zoom'),
        'geo_markup'         => esc_html__('You are within {{radius_value}} meters from this point', 'essential-wp-real-estate')
    )
);
$value = utf8_encode(json_encode($map_data));
if (!empty($provider->get_meta_data($provider->prefix . 'maps_fields'))) {
?>
    <div class="mb-4" id="map" data-map_data="<?php echo esc_attr($map_data); ?>" style="height: 450px;"></div>
    <?php
    $img_url = !empty(get_the_post_thumbnail_url()) ? get_the_post_thumbnail_url() : WPERESDS_ASSETS . '/img/placeholder_light.png';
    $listing_data = wp_json_encode(
        array(
            'id'        => $provider->listing->ID,
            'url'       => $provider->listing->url,
            'title'     => $provider->listing->title,
            'content'   => $provider->listing->content,
            'excerpt'   => $provider->listing->excerpt,
            'img_url'   => $img_url,
            'address'   => $provider->get_meta_data($provider->prefix . 'address'),
            'latitude'  => $provider->get_meta_data($provider->prefix . 'maps_fields')[$provider->prefix . 'map_address_lat'],
            'longitude' => $provider->get_meta_data($provider->prefix . 'maps_fields')[$provider->prefix . 'map_address_lon'],
            'price'     => $provider->get_meta_data($provider->prefix . 'pricing', get_the_ID())
        )
    );
    $value = utf8_encode(json_encode($listing_data));
    // END MAP DATA
    ?>
    <div class="listing_data" id="post-<?php the_ID(); ?>" data-listing="<?php echo esc_attr($value); ?>"></div>
<?php
}
