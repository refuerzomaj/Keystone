<?php
/**
 * Utils: Modal
 *
 * Generic "Modal" helper for generating markup.
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Generates HTML for a Modal.
 *
 * @since 2.8.0
 *
 * @param int   $listing_id
 * @param array $args
 */
function cls_modal( $args = array() ) {
	$defaults = array(
		'id'      => '',
		'class'   => array(),
		'title'   => '',
		'content' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	// ID is required for any Javascript interaction
	// and accessibility.
	if ( empty( $args['id'] ) ) {
		return;
	}

	$classnames = implode( ' ', array_map( 'trim', (array) $args['class'] ) );

	ob_start();
	?>

	<div id="<?php echo esc_attr( $args['id'] ); ?>" class="cls-modal has-slide" aria-hidden="true">
		<div class="cls-modal__overlay" tabindex="-1" data-micromodal-close>
			<div class="cls-modal__container <?php echo esc_attr( $classnames ); ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $args['id'] ); ?>-modal-title">
				<header class="cls-modal__header">
					<?php if ( ! empty( $args['title'] ) ) : ?>
						<h2 id="<?php echo esc_attr( $args['id'] ); ?>-modal-title" class="cls-modal__title">
							<?php echo esc_html( $args['title'] ); ?>
						</h2>
					<?php endif; ?>

					<button type="button" class="cls-modal__close" aria-label="<?php echo esc_attr__( 'Close', 'essential-wp-real-estate' ); ?>" data-micromodal-close>
					</button>
				</header>

				<div id="<?php echo esc_attr( $args['id'] ); ?>-modal-content" class="cls-modal__content">
					<?php
					echo esc_html( $args['content'] ); // WPCS: XSS okay.
					?>
				</div>
			</div>
		</div>
	</div>
	<?php

	return ob_get_clean();
}
