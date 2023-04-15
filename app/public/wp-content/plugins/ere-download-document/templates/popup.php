<?php
$ered_settings = get_option('ered_settings', array());

$popup_title = isset($ered_settings['popup_title'])
	? $ered_settings['popup_title']
	: esc_html__('Download document','ered');

$popup_subtitle = isset($ered_settings['popup_subtitle'])
	? $ered_settings['popup_subtitle']
	: esc_html__('Enter your email before downloading this document','ered');
?>
<div id="ered_download_popup" class="mfp-hide ered-white-popup-block">
	<div class="ered-download-popup-wrapper">
		<h4><?php echo esc_html($popup_title) ?></h4>
		<p><?php echo esc_html($popup_subtitle) ?></p>
		<form action="<?php echo esc_url(admin_url('admin-ajax.php?action=ered_download_document'))?>" method="post">
			<?php wp_nonce_field('ered_download_document_action', 'ered_download_document_nonce') ?>
			<input type="hidden" name="url" value="">
			<p>
				<input type="text" name="full_name" value="" placeholder="<?php esc_attr_e('Enter your name...','ered') ?>" required>
				<input type="email" name="email_address" value="" placeholder="<?php esc_attr_e('Enter your email...','ered') ?>" required>
			</p>
			<p>
				<button type="submit" class="ladda-button" data-style="expand-right"><?php esc_attr_e('Download Now','ered') ?></button>
			</p>
		</form>
	</div>
</div>