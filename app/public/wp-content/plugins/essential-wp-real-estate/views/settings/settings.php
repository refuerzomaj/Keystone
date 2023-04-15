<?php
do_action( 'cl_admin_before_template/setting.php' );
?>
<div class="wrap <?php echo 'wrap-' . esc_attr( $activetab ); ?>">
	<h2><?php _e( 'Property Listing Plugin Listing Settings', 'essential-wp-real-estate' ); ?></h2>
	<h2 class="nav-tab-wrapper">
		<?php
		foreach ( $mainsettingstabs as $tabid => $tab_name ) {
			$tab_url = add_query_arg(
				array(
					'settings-updated' => false,
					'tab'              => $tabid,
				)
			);

			// Remove the section from the tabs so we always end up at the main section
			$tab_url = remove_query_arg( 'section', $tab_url );
			$active  = $activetab == $tabid ? ' nav-tab-active' : '';
			echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . esc_attr( $active ) . '">';
			echo esc_html( $tab_name );
			echo '</a>';
		}
		?>
	</h2>
	<?php
	$subtab = count( $settingstabs[ $activetab ] );
	if ( $subtab > 1 ) {
		foreach ( $settingstabs[ $activetab ] as $subtabid => $subtab_name ) {
		}
	}
	$number = 0;
	if ( $subtab > 1 ) {
		echo '<div class="wp-clearfix"><ul class="subsubsub">';
		foreach ( $settingstabs[ $activetab ] as $sectionid => $section_name ) {
			echo '<li>';
			$number++;
			$tab_url = add_query_arg(
				array(
					'settings-updated' => false,
					'tab'              => $activetab,
					'section'          => $sectionid,
				)
			);
			$class   = '';
			if ( $section == $sectionid ) {
				$class = 'current';
			}
			echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $tab_url ) . '">' . esc_html( $section_name ) . '</a>';

			if ( $number != $subtab ) {
				echo ' | ';
			}
			echo '</li>';
		}
		echo '</ul></div>';
	}
	?>

	<div id="tab_container" class="<?php echo esc_attr( $activetab ) . '-tab'; ?>">
		<div class="cl-settings-wrapc wp-clearfix">
			<div class="cl-settings-content">
				<form method="post" action="options.php" id="<?php echo 'form_' . esc_attr( $activetab ) . '_' . esc_attr( $section ); ?>">
					<table class="form-table">
						<?php
						settings_fields( 'cl_all_settings' );
						?>
						<input type="hidden" name="wptest" value="<?php echo wp_create_nonce( 'load_posts_by_ajax' ); ?>" />
						<?php

						if ( 'main' === $section ) {
							do_action( 'cl_admin_settings_tab_top', $activetab );
						}
						do_action( 'cl_admin_settings_tab_top_' . $activetab . '_' . $section );
						do_settings_sections( 'cl_admin_settings_' . $activetab . '_' . $section );
						do_action( 'cl_admin_settings_tab_bottom_' . $activetab . '_' . $section );
						// For backwards compatibility.
						if ( 'main' === $section ) {
							do_action( 'cl_admin_settings_tab_bottom', $activetab );
						}
						?>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>
		</div>
	</div><!-- #tab_container-->
</div><!-- .wrap -->
<?php
do_action( 'cl_admin_after_template/setting.php' );
