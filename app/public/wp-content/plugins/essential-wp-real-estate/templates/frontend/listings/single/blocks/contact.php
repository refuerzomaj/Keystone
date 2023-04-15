<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$provider            = WPERECCP()->front->listing_provider;
$contacts            = array();
$contacts['address'] = $provider->get_meta_data( $provider->markups->prefix . 'address', get_the_ID() );
$contacts['zip']     = $provider->get_meta_data( $provider->markups->prefix . 'zip', get_the_ID() );
$contacts['phone']   = $provider->get_meta_data( $provider->markups->prefix . 'phone', get_the_ID() );
$contacts['email']   = $provider->get_meta_data( $provider->markups->prefix . 'email', get_the_ID() );
$contacts['Website'] = $provider->get_meta_data( $provider->markups->prefix . 'Website', get_the_ID() );
if ( ! empty( $contacts ) ) {
	?>
	<ul class="row p-0 m-0">
		<?php foreach ( $contacts as $key => $contact ) { ?>
			<li class="col-lg-4 col-md-6 mb-2 p-0"><?php echo ucwords( esc_html( $key ) ) . ' : ' . esc_html( $contact ); ?></li>
		<?php } ?>
	</ul>
	<?php
}
