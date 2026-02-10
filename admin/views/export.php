<?php
/**
 * Export (manual backup) page.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) || ! current_user_can( 'manage_options' ) ) {
	exit;
}
?>
<div class="wrap r2wb-wrap">
	<h1><?php esc_html_e( 'Export', 'r2-wordpress-backup' ); ?></h1>
	<p class="r2wb-description"><?php esc_html_e( 'Create a full backup (files + database) and upload it to R2.', 'r2-wordpress-backup' ); ?></p>
	<p>
		<button type="button" class="button button-primary button-hero r2wb-start-backup" aria-describedby="r2wb-backup-desc">
			<?php esc_html_e( 'Start backup', 'r2-wordpress-backup' ); ?>
		</button>
	</p>
	<p id="r2wb-backup-desc" class="description"><?php esc_html_e( 'Creates a full backup (database + files) and uploads it to R2. This may take a few minutes.', 'r2-wordpress-backup' ); ?></p>
	<div class="r2wb-backup-progress" style="display:none;" role="status" aria-live="polite"></div>
</div>
