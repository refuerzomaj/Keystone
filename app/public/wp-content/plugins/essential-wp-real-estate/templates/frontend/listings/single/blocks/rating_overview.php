<?php if ( WPERECCP()->front->listing_provider->get_average_rate( get_the_ID() ) ) { ?>
	<!-- Review Block Wrap -->
	<div class="rating-overview">
		<div class="rating-overview-box">
			<span class="rating-overview-box-total"><?php echo WPERECCP()->front->listing_provider->get_average_rate( get_the_ID() ); ?></span>
			<span class="rating-overview-box-percent"><?php _e( 'out of 5.0', 'essential-wp-real-estate' ); ?></span>
			<div class="star-rating" data-rating="5">
				<?php
				$average        = WPERECCP()->front->listing_provider->get_average_rate( get_the_ID() );
				$averageRounded = ceil( $average );
				if ( $averageRounded ) {
					$active_comment_rate = $averageRounded;
					for ( $x = 1; $x <= $active_comment_rate; $x++ ) {
						echo '<i class="fa fa-star filled"></i>';
					}
					$inactive_comment_rate = 5 - $active_comment_rate;
					if ( $inactive_comment_rate > 0 ) {
						for ( $x = 1; $x <= $inactive_comment_rate; $x++ ) {
							echo '<i class="fa fa-star"></i>';
						}
					}
				}
				?>
			</div>
		</div>
		<?php
		$total_ratting         = WPERECCP()->front->listing_provider->get_average_ratting_name( get_the_ID() );
		$property_width        = '';
		$value_for_money_width = '';
		$agent_support_width   = '';
		$location_width        = '';

		if ( $total_ratting['property'] ) {
			$property_width = ( $total_ratting['property'] * 100 ) / 5;
		}

		if ( $total_ratting['value_for_money'] ) {
			$value_for_money_width = ( $total_ratting['value_for_money'] * 100 ) / 5;
		}

		if ( $total_ratting['agent_support'] ) {
			$agent_support_width = ( $total_ratting['agent_support'] * 100 ) / 5;
		}

		if ( $total_ratting['location'] ) {
			$location_width = ( $total_ratting['location'] * 100 ) / 5;
		}

		if ( $property_width < 30 ) {
			$property_width_class = 'poor';
		} elseif ( $property_width < 60 ) {
			$property_width_class = 'mid';
		} else {
			$property_width_class = 'high';
		}
		if ( $value_for_money_width < 30 ) {
			$value_for_money_width_class = 'poor';
		} elseif ( $value_for_money_width < 60 ) {
			$value_for_money_width_class = 'mid';
		} else {
			$value_for_money_width_class = 'high';
		}
		if ( $agent_support_width < 30 ) {
			$agent_support_width_class = 'poor';
		} elseif ( $agent_support_width < 60 ) {
			$agent_support_width_class = 'mid';
		} else {
			$agent_support_width_class = 'high';
		}
		if ( $location_width < 30 ) {
			$location_width_class = 'poor';
		} elseif ( $location_width < 60 ) {
			$location_width_class = 'mid';
		} else {
			$location_width_class = 'high';
		}

		?>
		<div class="rating-bars">
			<?php
			if ( $property_width ) {
				?>
				<div class="rating-bars-item">
					<span class="rating-bars-name"><?php echo esc_html__( 'Property', 'essential-wp-real-estate' ); ?></span>
					<span class="rating-bars-inner">
						<span class="rating-bars-rating <?php echo esc_attr( $property_width_class ); ?>" data-rating="<?php echo round( $total_ratting['property'], 1 ); ?>">
							<span class="rating-bars-rating-inner" style="width: <?php echo round( $property_width ); ?>%;"></span>
						</span>
						<strong>
							<?php echo round( $total_ratting['property'], 1 ); ?>
						</strong>
					</span>
				</div>
				<?php
			}
			if ( $value_for_money_width ) {
				?>
				<div class="rating-bars-item">
					<span class="rating-bars-name"><?php echo esc_html__( 'Value for Money', 'essential-wp-real-estate' ); ?></span>
					<span class="rating-bars-inner">
						<span class="rating-bars-rating <?php echo esc_attr( $value_for_money_width_class ); ?>" data-rating="<?php echo round( $total_ratting['value_for_money'], 1 ); ?>">
							<span class="rating-bars-rating-inner" style="width: <?php echo round( $value_for_money_width ); ?>%;"></span>
						</span>
						<strong><?php echo round( $total_ratting['value_for_money'], 1 ); ?></strong>
					</span>
				</div>
				<?php
			}
			if ( $location_width ) {
				?>
				<div class="rating-bars-item">
					<span class="rating-bars-name"><?php echo esc_html__( 'Location', 'essential-wp-real-estate' ); ?></span>
					<span class="rating-bars-inner">
						<span class="rating-bars-rating <?php echo esc_attr( $location_width_class ); ?>" data-rating="<?php echo round( $total_ratting['location'], 1 ); ?>">
							<span class="rating-bars-rating-inner" style="width:<?php echo round( $location_width ); ?>%;"></span>
						</span>
						<strong><?php echo round( $total_ratting['location'], 1 ); ?></strong>
					</span>
				</div>
				<?php
			}
			if ( $agent_support_width ) {
				?>
				<div class="rating-bars-item">
					<span class="rating-bars-name"><?php echo esc_html__( 'Support', 'essential-wp-real-estate' ); ?></span>
					<span class="rating-bars-inner">
						<span class="rating-bars-rating <?php echo esc_attr( $agent_support_width_class ); ?>" data-rating="<?php echo round( $total_ratting['agent_support'], 1 ); ?>">
							<span class="rating-bars-rating-inner" style="width: <?php echo round( $agent_support_width ); ?>%;"></span>
						</span>
						<strong><?php echo round( $total_ratting['agent_support'], 1 ); ?></strong>
					</span>
				</div>
			<?php } ?>
		</div>
	</div>

	<?php
} else {
	echo esc_html__( 'Not yet Rated.', 'essential-wp-real-estate' );
}
