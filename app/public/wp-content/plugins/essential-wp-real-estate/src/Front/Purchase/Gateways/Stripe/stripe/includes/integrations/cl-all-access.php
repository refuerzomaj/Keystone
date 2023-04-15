<?php
/**
 * Integrations: All Access Pass
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Disables Payment Request Button output if listing has been unlocked with a pass.
 *
 * @since 2.8.0
 *
 * @param bool $enabled If the Payment Request Button is enabled.
 * @param int  $listing_id Current listing ID.
 * @return bool
 */
function cls_all_access_prb_purchase_link_enabled( $enabled, $listing_id ) {
	$all_access = cl_all_access_check(
		array(
			'listing_id' => $listing_id,
		)
	);

	return false === $all_access['success'];
}
add_filter( 'cls_prb_purchase_link_enabled', 'cls_all_access_prb_purchase_link_enabled', 10, 2 );
