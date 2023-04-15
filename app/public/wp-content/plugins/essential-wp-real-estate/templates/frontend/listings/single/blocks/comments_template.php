<?php
$comments = get_comments(
	array(
		'post_id'            => get_the_ID(),
		'order'              => 'ASC',
		'status'             => 'approve',
		'include_unapproved' => array( is_user_logged_in() ? get_current_user_id() : wp_get_unapproved_comment_author_email() ),
	)
);

if ( $comments ) {
	?>
	<!-- Single Reviews Block -->
	<div class="style-2">

		<div class="property_block_wrap_header">
			<h4 class="property_block_title"><?php echo esc_html__( get_comments_number() . ' Reviews', 'essential-wp-real-estate' ); ?></h4>
		</div>
		<div>
			<div class="block-body">
				<div class="author-review">
					<div class="comment-list">
						<ul>
							<?php
							foreach ( $comments as $comment ) {
								$total_rate = array();
								?>
								<!-- Single Thing -->
								<li class="article_comments_wrap" id="comment-<?php echo esc_html( $comment->comment_ID ); ?>">
									<article>
										<div class="article_comments_thumb">
											<?php echo get_avatar( $comment, $size = '80' ); ?>
										</div>
										<div class="comment-details">
											<div class="comment-meta">
												<div class="comment-left-meta">
													<h4 class="author-name"><?php echo esc_html( $comment->comment_author ); ?></h4>
													<div class="comment-date"><?php echo esc_html( $comment->comment_date ); ?></div>
												</div>
											</div>
											<div class="comment-text">
												<p><?php echo esc_html( $comment->comment_content ); ?></p>
											</div>
										</div>
									</article>
								</li>
								<!-- Single Thing -->
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		</div>

	</div>
	<!-- Single Reviews Block -->

	<?php
} else {
	echo esc_html__( 'Be the first one to rate this.', 'essential-wp-real-estate' );
}
