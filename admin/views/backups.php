<?php
/**
 * Backups list page.
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
$download_url = admin_url( 'admin-ajax.php' );
$nonce        = wp_create_nonce( 'r2wb_admin' );
?>
<div class="wrap r2wb-wrap">
	<h1 class="text-xl font-semibold text-slate-900 mb-3"><?php esc_html_e( 'Backups', 'r2-cloud-backup' ); ?></h1>
	<p class="r2wb-description"><?php esc_html_e( 'Backups stored in Cloudflare R2.', 'r2-cloud-backup' ); ?></p>

	<div class="bg-white shadow-sm rounded-lg border border-slate-200 p-4 sm:p-6">
		<?php if ( empty( $backups ) ) : ?>
			<div class="r2wb-alert r2wb-alert--info" role="status">
				<p class="r2wb-alert__title"><?php esc_html_e( 'No backups yet', 'r2-cloud-backup' ); ?></p>
				<p class="r2wb-alert__body"><?php esc_html_e( 'Use Export to create a backup and upload it to R2.', 'r2-cloud-backup' ); ?></p>
			</div>
		<?php else : ?>
			<div class="overflow-x-auto">
				<table class="wp-list-table widefat fixed striped min-w-full text-sm">
					<thead class="bg-slate-50">
						<tr>
							<th class="px-4 py-2 text-left font-semibold text-slate-700"><?php esc_html_e( 'Backup', 'r2-cloud-backup' ); ?></th>
							<th class="px-4 py-2 text-left font-semibold text-slate-700"><?php esc_html_e( 'Actions', 'r2-cloud-backup' ); ?></th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-100">
						<?php foreach ( array_reverse( $backups ) as $index => $key ) : ?>
							<?php
							$name        = basename( $key );
							$is_latest   = ( 0 === $index );
							$row_classes = $is_latest ? 'font-medium' : '';
							?>
							<tr data-key="<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $row_classes ); ?>">
								<td class="px-4 py-2 align-middle">
									<?php echo esc_html( $name ); ?>
									<?php if ( $is_latest ) : ?>
										<span class="ml-2 inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700"><?php esc_html_e( 'Latest', 'r2-cloud-backup' ); ?></span>
									<?php endif; ?>
								</td>
								<td class="px-4 py-2 align-middle whitespace-nowrap">
									<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'r2wb_download_backup', 'key' => $key, 'nonce' => $nonce ), $download_url ) ); ?>" class="button button-small inline-flex items-center px-3 py-1 rounded-md bg-slate-100 text-slate-800 hover:bg-slate-200">
										<?php esc_html_e( 'Download', 'r2-cloud-backup' ); ?>
									</a>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=r2wb-import&restore=' . rawurlencode( $key ) ) ); ?>" class="button button-small inline-flex items-center px-3 py-1 rounded-md bg-sky-600 text-white hover:bg-sky-700">
										<?php esc_html_e( 'Restore', 'r2-cloud-backup' ); ?>
									</a>
									<button type="button" class="button button-small button-link-delete r2wb-delete-backup inline-flex items-center px-3 py-1 rounded-md bg-red-50 text-red-700 hover:bg-red-100" data-key="<?php echo esc_attr( $key ); ?>">
										<?php esc_html_e( 'Delete', 'r2-cloud-backup' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>
