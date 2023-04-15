<?php
$user          = wp_get_current_user();
$commenter     = wp_get_current_commenter();
$user_identity = $user->display_name;
$req           = get_option( 'require_name_email' );
$post_id       = get_the_ID();

// -- Rating Array
$rating_types = array(
	'property',
	'location',
	'value_for_money',
	'agent_support',
);

// -- Rating Array
?>
<div class="review-form-box form-submit" id="respond">
	<form action="<?php echo get_option( 'siteurl' ); ?>/wp-comments-post.php" method="post" id="commentform">
		<!-- Single Write a Review -->
		<div class="row">
			<div class="col-lg-8 col-md-12">
				<div class="row">
					<?php
					foreach ( $rating_types as $key => $rating_type ) {
						echo '<div class="col-lg-6 col-md-6 col-sm-12">';
						echo '<label>' . str_replace( '_', ' ', ucwords( esc_html( $rating_type ) ) ) . '</label><div class="rate-stars">';
						for ( $i = 5; $i >= 1; $i-- ) {
							if ( $i == 5 ) {
								$check_val = 'checked';
							} else {
								$check_val = null;
							}
							echo '<input type="radio" id="' . esc_attr( $rating_type ) . esc_attr( $i ) . '" name="' . esc_attr( $rating_type ) . '" value="' . esc_attr( $i ) . '" ' . esc_attr( $check_val ) . '><label for="' . esc_attr( $rating_type ) . esc_attr( $i ) . '"></label>';
						}
						echo '</div></div>';
					}
					?>
				</div>
			</div>
			<div class="col-lg-4 col-md-12">
				<div class="avg-total-pilx">
					<h4 class="high user_commnet_avg_rate"><?php echo '5'; ?></h4>
					<span><?php echo esc_html__( 'Average Ratting', 'essential-wp-real-estate' ); ?></span>
				</div>
			</div>
			<!-- rating code here -->
			<div class="block-body col-lg-12">
				<div class="simple-form">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12">
							<div class="form-group">
								<textarea rows="8" id="comment" required name="comment" class="form-control" placeholder="<?php _e( 'Messages', 'essential-wp-real-estate' ); ?>" aria-required="true"></textarea>
							</div>
						</div>
						<?php if ( ! is_user_logged_in() ) { ?>
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<input id="author" class="form-control" required name="author" type="text" placeholder="<?php _e( 'Your Name', 'essential-wp-real-estate' ); ?>" value="" aria-required="true">
								</div>
							</div>
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<input id="email" name="email" required class="form-control" type="email" placeholder="<?php _e( 'Your Email', 'essential-wp-real-estate' ); ?>" value="" aria-required="true">
								</div>
							</div>
						<?php } ?>
						<div class="col-lg-12 col-md-12 col-sm-12">
							<div class="form-group review-submit-btn">
								<input name="submit" type="submit" id="submit" class="btn btn-theme-light-2 rounded" value="<?php _e( 'Submit Review', 'essential-wp-real-estate' ); ?>">
								<input type="hidden" name="comment_post_ID" value="<?php echo esc_attr( $post_id ); ?>" id="comment_post_ID">
								<input type="hidden" name="comment_parent" id="comment_parent" value="0">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Comment Form -->
	</form>
</div> <!-- #respond -->
