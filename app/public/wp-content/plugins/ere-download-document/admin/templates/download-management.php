<?php
$current_page = intval(isset($_GET['current_page']) ? $_GET['current_page'] : 1);
$download_log = ERED()->db()->getEmailList($current_page);
$current_url = admin_url('admin.php?page=ered-download-management')
?>
<div class="wrap">
	<h1><?php esc_html_e('Download Management','ered') ?></h1>
	<table class="ered-download-list">
		<thead>
			<tr>
				<th><?php esc_html_e('ID','ered') ?></th>
				<th><?php esc_html_e('Name','ered') ?></th>
				<th><?php esc_html_e('Email','ered') ?></th>
				<th><?php esc_html_e('Link Download','ered') ?></th>
				<th><?php esc_html_e('Download Date','ered') ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($download_log['items'] as $log): ?>
				<tr>
					<td><?php echo esc_html($log->id) ?></td>
					<td class="td-name"><?php echo esc_html($log->full_name) ?></td>
					<td class="td-email"><?php echo esc_html($log->email) ?></td>
					<td><?php echo esc_html($log->document_link) ?></td>
					<td><?php echo esc_html($log->created_date) ?></td>
					<td>
						<a href="#ered_edit_info_popup" class="button button-primary button-small ered-change-email"
						        data-id="<?php echo esc_attr($log->id) ?>">
							<?php esc_html_e('Edit Email','ered') ?>
						</a>

						<a href="#" class="button button-secondary button-small ered-delete-email ladda-button" data-style="zoom-in"
						        data-nonce="<?php echo wp_create_nonce('ered_delete_email_action')?>"
						        data-url="<?php echo esc_url(admin_url('admin-ajax.php?action=ered_delete_email'))?>"
						        data-id="<?php echo esc_attr($log->id) ?>">

							<?php esc_html_e('Delete','ered') ?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php if ($download_log['total'] > 1): ?>
		<div class="ered-download-pagination">
			<?php for ($_page = 1; $_page <= $download_log['total']; $_page++): ?>
				<?php if ($current_page === $_page): ?>
					<a href="javascript:void();" class="current-page">
						<?php echo esc_html($_page) ?>
					</a>
				<?php else: ?>
					<a href="<?php echo esc_url($current_url . '&current_page=' . $_page) ?>">
						<?php echo esc_html($_page) ?>
					</a>
				<?php endif; ?>
			<?php endfor; ?>
		</div>
	<?php endif; ?>
	<div id="ered_edit_info_popup" class="mfp-hide ered-white-popup-block">
		<div class="ered-edit-customer-info">
			<form action="<?php echo esc_url(admin_url('admin-ajax.php?action=ered_change_email'))?>"
			      id="ered-change-info-form" method="post">
				<?php wp_nonce_field('ered_change_email_action', 'ered_change_email_nonce') ?>
				<input type="hidden" name="id" value="">
				<h2><?php esc_html_e('Edit Customer Information','ered') ?></h2>
				<p class="ered-field">
					<label><?php esc_html_e('Name','ered') ?></label>
					<input type="text" name="name" value="" required>
				</p>
				<p>
					<label><?php esc_html_e('Email','ered') ?></label>
					<input type="email" name="email" value="" required>
				</p>
				<p>
					<button type="submit" class="button button-primary button-large ered-save-email ladda-button" data-style="zoom-in"><?php esc_html_e('Save Changes','ered') ?></button>
				</p>
			</form>
		</div>
	</div>
</div>