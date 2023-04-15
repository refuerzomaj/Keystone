<?php
global $pref;
$provider        = WPERECCP()->front->listing_provider;
$single_settings = get_option( 'cl_single_settings_layout', array() );

if ( ! empty( $single_settings ) ) {
	$single_settings = json_decode( $single_settings, true );
} else {
	$single_settings = array(
		'sectionthumbnail' => array(
			'leftmeta' => array(
				'thumbnail' => array(
					'active'   => 1,
					'dropable' => 'thumbarea',
				),
				'class'     => 'leftmeta',
			),
			'class'    => 'sectionthumbnail',
		),
		'metasection'      => array(
			'leftmeta'  => array(
				'features' => array(
					'active'   => 1,
					'dropable' => 'metasection',
				),
				'title'    => array(
					'active'   => 1,
					'dropable' => 'metasection',
				),
				'location' => array(
					'active'   => 1,
					'dropable' => 'metasection',
				),
				'share'    => array(
					'active'   => 0,
					'dropable' => 'metasection',
				),
				'class'    => 'leftmeta',
				'block'    => 1,
			),
			'rightmeta' => array(
				'share'    => array(
					'active'   => 1,
					'dropable' => 'metasection',
				),
				'features' => array(
					'active'   => 0,
					'dropable' => 'metasection',
				),
				'title'    => array(
					'active'   => 0,
					'dropable' => 'metasection',
				),
				'location' => array(
					'active'   => 0,
					'dropable' => 'metasection',
				),
				'class'    => 'rightmeta',
				'block'    => 1,
			),
			'class'     => 'metasection',
		),

		'extrasection'     => array(
			'description' => array(
				'active'   => 1,
				'dropable' => 'metasection',
			),
			'contact'     => array(
				'active'   => 0,
				'dropable' => 'metasection',
			),
			'gallery'     => array(
				'active'   => 0,
				'dropable' => 'metasection',
			),
			'features'    => array(
				'active'   => 0,
				'dropable' => 'metasection',
			),
			'amenities'   => array(
				'active'   => 0,
				'dropable' => 'metasection',
			),
			'floor'       => array(
				'active'   => 0,
				'dropable' => 'metasection',
			),
			'video'       => array(
				'active'   => 0,
				'dropable' => 'metasection',
			),
			'maps'        => array(
				'active'   => 0,
				'dropable' => 'metasection',
			),
			'class'       => 'extrasection',
		),

		'templatesection'  => array(
			'rating_overview'   => array(
				'active'   => 1,
				'dropable' => 'tempsection',
			),
			'comment_form'      => array(
				'active'   => 1,
				'dropable' => 'tempsection',
			),
			'comments_template' => array(
				'active'   => 1,
				'dropable' => 'tempsection',
			),
			'class'             => 'templatesection',
		),
	);
}

foreach ( $single_settings as $key => $single_setting ) {
	printf( $provider->markups->section_wrapper( $key ) );
	do_action( $pref . $key, $single_setting );
	printf( $provider->markups->section_wrapper_close( $key ) );
}
