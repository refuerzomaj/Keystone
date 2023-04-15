<?php
$ered_settings = get_option('ered_settings', array());

$popup_title = isset($ered_settings['popup_title'])
	? $ered_settings['popup_title']
	: esc_html__('Download document','ered');

$popup_subtitle = isset($ered_settings['popup_subtitle'])
	? $ered_settings['popup_subtitle']
	: esc_html__('Enter your email before downloading this document','ered');

$email_address = isset($ered_settings['email_address']) ? $ered_settings['email_address'] : '';
?>
<div class="wrap">
	<h1><?php esc_html_e('Settings','ered') ?></h1>

	<?php settings_errors('page_for_ered_setting') ?>

	<form action="" method="post">
		<?php wp_nonce_field('ered_settings_page_action', 'ered_settings_page_nonce') ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="support_page_id"><?php esc_html_e('Popup Title (*)', 'ered') ?></label>
					</th>
					<td>
						<input type="text" name="popup_title" value="<?php echo esc_attr($popup_title) ?>" class="regular-text" required>
						<p class="description">
							<?php esc_html_e('Enter popup download title','ered') ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="support_page_id"><?php esc_html_e('Popup Subtitle (*)', 'ered') ?></label>
					</th>
					<td>
						<input type="text" name="popup_subtitle" value="<?php echo esc_attr($popup_subtitle) ?>" class="regular-text" required>
						<p class="description">
							<?php esc_html_e('Enter popup download subtitle','ered') ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="support_page_id"><?php esc_html_e('Email Notification', 'ered') ?></label>
					</th>
					<td>
						<input type="email" name="email_address" value="<?php echo esc_attr($email_address) ?>" class="regular-text">
						<p class="description">
							<?php esc_html_e('Enter email address here if you want receive notification when client download. Empty to disable notification!','ered') ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button(esc_html__('Save Changes', 'ered'), 'primary') ?>
	</form>
</div>