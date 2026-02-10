<?php
/**
 * Reset Hub page.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) || ! current_user_can( 'manage_options' ) ) {
	exit;
}
?>
<div class="wrap r2wb-wrap">
	<h1 class="text-xl font-semibold text-slate-900 mb-3"><?php esc_html_e( 'Reset Hub', 'r2-wordpress-backup' ); ?></h1>
	<p class="r2wb-description"><?php esc_html_e( 'Reset plugin options and schedules. R2 credentials are kept unless you choose to clear them.', 'r2-wordpress-backup' ); ?></p>

	<div class="rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-800">
		<p><?php esc_html_e( 'This will reset the plugin configuration and scheduled backups to their defaults. R2 credentials will be kept unless you remove them manually from Settings.', 'r2-wordpress-backup' ); ?></p>
		<p class="mt-2 text-xs text-red-700"><?php esc_html_e( 'Use this only if you want to start over with a clean configuration.', 'r2-wordpress-backup' ); ?></p>
		<p class="mt-4">
			<button type="button" class="button button-secondary r2wb-reset-options inline-flex items-center px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm">
				<?php esc_html_e( 'Reset options and schedules', 'r2-wordpress-backup' ); ?>
			</button>
		</p>
	</div>
</div>
