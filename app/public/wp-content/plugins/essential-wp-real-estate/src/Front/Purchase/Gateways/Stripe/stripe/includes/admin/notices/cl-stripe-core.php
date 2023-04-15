<?php
/**
 * Notice: cl-stripe-core
 *
 * @package CL_Stripe\Admin\Notices
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.8.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gateways_url = add_query_arg(
	array(
		'post_type' => 'listing',
		'page'      => 'cl-settings',
		'tab'       => 'gateways',
	),
	admin_url( 'edit.php' )
);
?>

<p>
	<strong>
		<?php esc_html_e( 'Accept credit card payments with Stripe', 'essential-wp-real-estate' ); ?>
	</strong> <br />
	<?php
	echo wp_kses(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. %3$s Opening anchor tag, do not translate. %4$s Closing anchor tag, do not translate. */
			__( 'Property Listing Plugin now lets you accept credit card payments using Stripe, including Apple Pay and Google Pay support. %1$sEnable Stripe%2$s now or %3$slearn more%4$s about the benefits of using Stripe.', 'essential-wp-real-estate' ),
			'<a href="' . esc_url( $gateways_url ) . '">',
			'</a>',
			'<a href="#" target="_blank" rel="noopener noreferrer">',
			'</a>'
		),
		array(
			'a' => array(
				'href'   => true,
				'rel'    => true,
				'target' => true,
			),
		)
	);
	?>
</p>
