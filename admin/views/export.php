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
	<h1 class="text-xl font-semibold text-slate-900 mb-3"><?php esc_html_e( 'Export', 'r2-wordpress-backup' ); ?></h1>
	<p class="r2wb-description"><?php esc_html_e( 'Create a full backup (files + database) and upload it to R2.', 'r2-wordpress-backup' ); ?></p>

	<div class="bg-white shadow-sm rounded-lg border border-slate-200 p-4 sm:p-6 space-y-4">
		<p class="text-sm text-slate-700">
			<?php esc_html_e( 'Run a full backup of this site (database + wp-content) and upload it securely to your Cloudflare R2 bucket.', 'r2-wordpress-backup' ); ?>
		</p>
		<p>
			<button type="button" class="button button-primary button-hero r2wb-start-backup inline-flex items-center px-6 py-3 rounded-lg bg-sky-600 hover:bg-sky-700 text-white" aria-describedby="r2wb-backup-desc">
				<?php esc_html_e( 'Start backup', 'r2-wordpress-backup' ); ?>
			</button>
		</p>
		<p id="r2wb-backup-desc" class="description text-xs text-slate-500">
			<?php esc_html_e( 'Creates a full backup (database + files) and uploads it to R2. This may take a few minutes.', 'r2-wordpress-backup' ); ?>
		</p>
		<div class="r2wb-backup-progress mt-4 text-sm rounded-md bg-sky-50 border border-sky-100 text-sky-800" style="display:none;" role="status" aria-live="polite"></div>
	</div>
</div>
