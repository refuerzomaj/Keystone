<?php
if ( ! is_user_logged_in() ) {
	echo '<p>' . esc_html__( 'Please', 'essential-wp-real-estate' ) . ' <a href="' . esc_url( get_page_link( cl_admin_get_option( 'login_redirect_page' ) ) ) . '">' . esc_html__( 'Login', 'essential-wp-real-estate' ) . '</a></p>';
} else {
	wp_enqueue_media();
	global $current_user;
	$user_id = $current_user->ID;
	$error   = array();
	$alert   = array();
	if ( isset( $_POST['submit'] ) ) {
		$first_name    = cl_sanitization( $_POST['first_name'] );
		$last_name     = cl_sanitization( $_POST['last_name'] );
		$email         = sanitize_email( $_POST['email'] );
		$password      = cl_sanitization( $_POST['password'] );
		$conf_password = cl_sanitization( $_POST['conf_password'] );

		if ( strcmp( $password, $conf_password ) !== 0 ) {
			$error['error_msg'] = esc_html__( "Password didn't match", 'essential-wp-real-estate' );
		}

		if ( count( $error ) == 0 ) {


			$userdata = array(
				'ID'         => $user_id,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'user_email' => $email,
			);
			// Update user information
			$user_update = wp_update_user( $userdata );
			// Update user password
			wp_set_password( $password, $user_id );
			// Update User avatar
			if ( isset( $_POST['user_avt'] ) && ! empty( $_POST['user_avt'] ) ) {
				update_user_meta( $user_id, 'wp_user_avatar', cl_sanitization( $_POST['user_avt'] ) );
			}
			// Check if theres any error else return success
			if ( ! is_wp_error( $user_update ) ) {
				$alert['class'] = 'success';
				$alert['msg']   = esc_html__( 'Profile Successfully Updated.', 'essential-wp-real-estate' );
			}
		} else {
			$alert['class'] = 'danger';
			$alert['msg']   = $error['error_msg'];
		}
	} ?>
	<form action="#" method="post" id="cl-update-user-form" class="cl-update-user-form">
		<div class="container">
			<div class="row">
				<div class="col-md-12 form-group cl_user_avatar">
					<?php
					$avatar_url = cl_get_avatar_url();
					if ( $avatar_url ) {
						echo '<img class="files_featured" width="90px"  height="90px" src="' . esc_url( $avatar_url ) . '" alt="img" />';
					} else {
						echo '<img class="files_featured" src="#" alt="img" />';
					}
					?>
					<label for="file-input" class="frontend-avatar select_single_label">
						<i class="fa fa-upload"></i><?php esc_html_e( ' Select Image', 'essential-wp-real-estate' ); ?>
					</label>
					<input class="single_img_id" name="user_avt" type="hidden" />
				</div>
				<div class="col-md-12 form-group">
					<label for="user_name"><?php esc_html_e( 'User Name', 'essential-wp-real-estate' ); ?></label>
					<input disabled required type="text" name="user_name" id="user_name" class="input form-control" value="<?php echo esc_attr( $current_user->data->user_login ); ?>" />
				</div>
				<div class="col-md-6 form-group">
					<label for="first_name"><?php esc_html_e( 'First Name', 'essential-wp-real-estate' ); ?></label>
					<input required type="text" name="first_name" id="first_name" class="input form-control" value="<?php echo esc_attr( get_user_meta( $user_id, 'first_name', true ) ); ?>" />
				</div>
				<div class="col-md-6 form-group">
					<label for="last_name"><?php esc_html_e( 'Last Name', 'essential-wp-real-estate' ); ?></label>
					<input required type="text" name="last_name" id="last_name" class="input form-control" value="<?php echo esc_attr( get_user_meta( $user_id, 'last_name', true ) ); ?>" />
				</div>
				<div class="col-md-6 form-group">
					<label for="email"><?php esc_html_e( 'E-mail', 'essential-wp-real-estate' ); ?></label>
					<input required type="text" name="email" id="email" class="input form-control" value="<?php echo esc_attr( $current_user->data->user_email ); ?>" />
				</div>
				<div class="col-md-6 form-group">
					<label for="password"><?php esc_html_e( 'Password', 'essential-wp-real-estate' ); ?></label>
					<input required placeholder="<?php esc_attr_e( 'Password', 'essential-wp-real-estate' ); ?>" type="password" name="password" id="password" class="input form-control" value="" />
				</div>
				<div class="col-md-6 form-group">
					<label for="conf_password"><?php esc_html_e( 'Confirm Password', 'essential-wp-real-estate' ); ?></label>
					<input required placeholder="<?php esc_attr_e( 'confirm your password', 'essential-wp-real-estate' ); ?>" type="password" name="conf_password" id="conf_password" class="input form-control" value="" />
				</div>
				<?php if ( ! empty( $alert ) ) { ?>
					<div class="col-md-12">
						<div class="alert alert-<?php echo esc_attr( $alert['class'] ); ?>">
							<?php echo esc_html( $alert['msg'] ); ?>
						</div>
					</div>
				<?php } ?>
				<div class="col-md-12 form-group">
					<button type="submit" name="submit"><?php esc_html_e( 'Submit', 'essential-wp-real-estate' ); ?></button>
				</div>
			</div>
		</div>
	</form>
	<?php
}
