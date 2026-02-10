<?php
/**
 * Import (restore) page.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) || ! current_user_can( 'manage_options' ) ) {
	exit;
}

$backups = array();
try {
	$client = new R2WB_R2_Client();
	$backups = $client->list_backups();
} catch ( Exception $e ) {
	$backups = array();
}
$restore_key = isset( $_GET['restore'] ) ? sanitize_text_field( wp_unslash( $_GET['restore'] ) ) : '';
?>
<div class="wrap r2wb-wrap">
	<h1 class="text-xl font-semibold text-slate-900 mb-3"><?php esc_html_e( 'Import', 'r2-wordpress-backup' ); ?></h1>
	<p class="r2wb-description"><?php esc_html_e( 'Restore this site from a backup stored in R2.', 'r2-wordpress-backup' ); ?></p>

	<div class="mb-4 rounded-md border border-amber-100 bg-amber-50 p-4 text-sm text-amber-800">
		<p><?php esc_html_e( 'Restoring will replace the current database and wp-content files on this site. Make sure you understand the impact before continuing.', 'r2-wordpress-backup' ); ?></p>
	</div>

	<div class="bg-white shadow-sm rounded-lg border border-slate-200 p-4 sm:p-6">
		<p class="text-sm text-slate-700 mb-3"><?php esc_html_e( 'Select a backup to restore. This will replace the current site data. Only use on this site.', 'r2-wordpress-backup' ); ?></p>
		<?php if ( empty( $backups ) ) : ?>
			<p class="text-sm text-slate-600"><?php esc_html_e( 'No backups in R2.', 'r2-wordpress-backup' ); ?></p>
		<?php else : ?>
			<div class="overflow-x-auto">
				<table class="wp-list-table widefat fixed striped min-w-full text-sm">
					<thead class="bg-slate-50">
						<tr>
							<th class="px-4 py-2 text-left font-semibold text-slate-700"><?php esc_html_e( 'Backup', 'r2-wordpress-backup' ); ?></th>
							<th class="px-4 py-2 text-left font-semibold text-slate-700"><?php esc_html_e( 'Action', 'r2-wordpress-backup' ); ?></th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-100">
						<?php foreach ( array_reverse( $backups ) as $key ) : ?>
							<?php $name = basename( $key ); ?>
							<tr>
								<td class="px-4 py-2 align-middle">
									<?php echo esc_html( $name ); ?>
								</td>
								<td class="px-4 py-2 align-middle">
									<button type="button" class="button button-primary r2wb-restore-backup inline-flex items-center px-4 py-1.5 rounded-md bg-red-600 hover:bg-red-700 text-white" data-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'Restore this backup (destructive action)', 'r2-wordpress-backup' ); ?>">
										<?php esc_html_e( 'Restore this backup', 'r2-wordpress-backup' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
		<div class="r2wb-restore-progress mt-4 text-sm rounded-md bg-sky-50 border border-sky-100 text-sky-800" style="display:none;" role="status" aria-live="polite"></div>
	</div>
</div>
