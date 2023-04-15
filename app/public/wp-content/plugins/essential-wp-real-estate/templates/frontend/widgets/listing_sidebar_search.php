<?php
class Listing_Search_Widget extends WP_Widget {

	function __construct() {
		WP_Widget::__construct(
			'listing-search',  // Base ID
			'Listing Search'   // Name
		);
	}

	public function widget( $args, $instance ) {
		global $pagenow;
		$listing_url = '';
		printf( $args['before_widget'] );
		if ( $pagenow !== 'widgets.php' ) {
			$cl_search_fields_data   = WPERECCP()->front->listing_provider->cl_search_fields_data();
			$listing_post_type       = WPERECCP()->front->listing_provider->get_cpt_lists( true );
			$listing_url             = get_post_type_archive_link( $listing_post_type );
			$query_string            = explode( '&', cl_sanitization( $_SERVER['QUERY_STRING'] ) );
			$core_key                = array( 'post_type', 'layout', 'search', 'sort' );
			$arr_values['post_type'] = $listing_post_type;
			$arr_values['search']    = 'advanced';
			if ( ! empty( array_filter( $query_string ) ) ) {
				foreach ( $query_string as $query_val ) {
					$arr_val = explode( '=', $query_val );
					if ( count( $arr_val ) == 2 ) {
						$arr_values[ $arr_val[0] ] = $arr_val[1];
					}
				}
			}
			?>
			<div class="sidebar-widgets p-4">
				<?php if ( isset( $instance['title'] ) && ! empty( $instance['title'] ) ) { ?>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 mb-2">
							<h6><?php echo esc_html__( $instance['title'], 'essential-wp-real-estate' ); ?></h6>
						</div>
					</div>
				<?php } ?>
				<form method="get" id="advanced-searchform" role="search" action="<?php echo esc_url( $listing_url ); ?>">
					<?php
					// -- Declaring hidden val
					foreach ( $arr_values as $key => $value ) {
						if ( in_array( $key, $core_key ) ) {
							echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
						}
					}
					foreach ( $cl_search_fields_data as $params ) {
						cl_get_template( "search/{$params['type']}.php", $params );
					}
					?>

					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12">
							<input type="submit" class="btn theme-bg rounded full-width" value="<?php echo esc_html__( $instance['submit_button'], 'essential-wp-real-estate' ); ?>" />
						</div>
					</div>
				</form>

			</div>

			<?php
		} else {
			echo '<div style="text-align:center;font-size:12px;display:flex;flex-direction:column;gap:10px;"><img width="100%" src="' . WPERESDS_ASSETS . '/img/search.svg"><p>Listing search placeholder. Check the preview on frontend.</p></div>';
		}
		printf( $args['after_widget'] );
	}

	public function form( $instance ) {
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$submit_button = ! empty( $instance['submit_button'] ) ? $instance['submit_button'] : esc_html__( 'Search', 'essential-wp-real-estate' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html__( 'Title', 'essential-wp-real-estate' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<!-- Submit Button -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'submit_button' ) ); ?>"><?php echo esc_html__( 'Submit Button', 'essential-wp-real-estate' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'submit_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'submit_button' ) ); ?>" type="text" value="<?php echo esc_attr( $submit_button ); ?>">
		</p>
		<?php

	}

	public function update( $new_instance, $old_instance ) {
		$instance                  = array();
		$instance['title']         = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['submit_button'] = ( ! empty( $new_instance['submit_button'] ) ) ? $new_instance['submit_button'] : '';
		return $instance;
	}
}
$my_widget = new Listing_Search_Widget();
