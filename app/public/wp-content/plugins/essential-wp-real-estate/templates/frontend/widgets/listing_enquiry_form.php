<?php
class Listing_Enquiry_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname'   => 'listing_enquiry_widget',
			'description' => __( "Latest CPT's & Posts." ),
		);
		WP_Widget::__construct(
			'listing-enquiry',
			__( 'Listing Enquiry Widget' ),
			$widget_ops
		);
		$this->alt_option_name = 'listing_enquiry_widget';

		add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
	}

	public function widget( $args, $instance ) {
		global $pagenow;
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'listing_enquiry_wid', 'widget' );
		}

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			printf( $cache[ $args['widget_id'] ] );
			return;
		}

		$title     = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$sub_title = ( ! empty( $instance['sub_title'] ) ) ? $instance['sub_title'] : '';
		if ( isset( $instance['shortcode'] ) && $instance['shortcode'] ) {
			$shortcode = $instance['shortcode'];
		}
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		printf( $args['before_widget'] );
		if ( $title ) {
			printf( $args['before_title'] . $title . $args['after_title'] );
		}
		if ( $sub_title ) {
			echo '<p>' . esc_html( $sub_title ) . '</p>';
		}
		// Show portion
		if ( isset( $shortcode ) && $shortcode ) {
			global $post, $author, $author_name;
			$curauth = ( isset( $_GET['author_name'] ) ) ? get_user_by( 'slug', $author_name ) : get_userdata( intval( $author ) );

			if ( ! empty( $curauth ) ) {
				$author_id = $curauth->ID;
			} else {
				$author_id = $post->post_author;
			}

			$author_name = get_the_author_meta( 'display_name', $author_id );
			?>
			<div class="sidebar-widgets p-4">
				<div class="sides-widget-header">
					<div class="agent-photo">
						<?php echo cl_get_avat( '60' ); ?>
					</div>
					<div class="sides-widget-details">
						<h4><?php echo esc_html( $author_name ); ?></h4>
						<?php if ( get_the_author_meta( 'phone', $post->post_author ) ) { ?>
							<span><i class="lni-phone-handset"></i><?php echo get_the_author_meta( 'phone', $post->post_author ); ?></span>
						<?php } ?>
					</div>
				</div>
				<div class="sides-widget-body simple-form">
					<?php echo do_shortcode( $shortcode ); ?>
				</div>
			</div>
			<?php
		} else {
			do_action( 'listing-enquiry-form' );
		}

		printf( $args['after_widget'] );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		if ( isset( $new_instance['title'] ) ) {
			$instance['title'] = strip_tags( $new_instance['title'] );
		}
		if ( isset( $new_instance['sub_title'] ) ) {
			$instance['sub_title'] = strip_tags( $new_instance['sub_title'] );
		}
		if ( isset( $new_instance['shortcode'] ) ) {
			$instance['shortcode'] = strip_tags( $new_instance['shortcode'] );
		}

		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['listing_enquiry_widget'] ) ) {
			delete_option( 'listing_enquiry_widget' );
		}

		return $instance;
	}

	public function flush_widget_cache() {
		wp_cache_delete( 'listing_enquiry_wid', 'widget' );
	}

	public function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Enquiry Widget';
		$sub_title = isset( $instance['sub_title'] ) ? esc_attr( $instance['sub_title'] ) : '';
		$shortcode = isset( $instance['shortcode'] ) ? esc_attr( $instance['shortcode'] ) : '';
		?>
		<p><label for="<?php printf( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php printf( $this->get_field_id( 'title' ) ); ?>" name="<?php printf( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php printf( $title ); ?>" />
		</p>
		<p><label for="<?php printf( $this->get_field_id( 'sub_title' ) ); ?>"><?php _e( 'Subtitle:' ); ?></label>
			<textarea class="widefat" id="<?php printf( $this->get_field_id( 'sub_title' ) ); ?>" name="<?php printf( $this->get_field_name( 'sub_title' ) ); ?>" value="<?php printf( $sub_title ); ?>"><?php printf( $sub_title ); ?></textarea>
		</p>
		<p><label for="<?php printf( $this->get_field_id( 'shortcode' ) ); ?>"><?php _e( 'Shortcode:' ); ?></label>
			<input placeholder="<?php echo esc_attr__( 'Keep Empty to get default Enquiry Form', 'essential-wp-real-estate' ); ?>" class="widefat" id="<?php printf( $this->get_field_id( 'shortcode' ) ); ?>" name="<?php printf( $this->get_field_name( 'shortcode' ) ); ?>" type="text" value="<?php printf( $shortcode ); ?>" />
		</p>
		<?php
	}
}
$my_widget = new Listing_Enquiry_Widget();
