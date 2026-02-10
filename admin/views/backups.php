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
$nonce = wp_create_nonce( 'r2wb_admin' );
?>
<div class="wrap r2wb-wrap">
	<h1><?php esc_html_e( 'Backups', 'r2-wordpress-backup' ); ?></h1>
	<p class="r2wb-description"><?php esc_html_e( 'Backups stored in Cloudflare R2.', 'r2-wordpress-backup' ); ?></p>
	<?php if ( empty( $backups ) ) : ?>
		<p><?php esc_html_e( 'No backups yet. Use Export to create one.', 'r2-wordpress-backup' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Backup', 'r2-wordpress-backup' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'r2-wordpress-backup' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( array_reverse( $backups ) as $key ) : ?>
					<?php $name = basename( $key ); ?>
					<tr data-key="<?php echo esc_attr( $key ); ?>">
						<td><?php echo esc_html( $name ); ?></td>
						<td>
							<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'r2wb_download_backup', 'key' => $key, 'nonce' => $nonce ), $download_url ) ); ?>" class="button button-small"><?php esc_html_e( 'Download', 'r2-wordpress-backup' ); ?></a>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=r2wb-import&restore=' . rawurlencode( $key ) ) ); ?>" class="button button-small"><?php esc_html_e( 'Restore', 'r2-wordpress-backup' ); ?></a>
							<button type="button" class="button button-small button-link-delete r2wb-delete-backup" data-key="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Delete', 'r2-wordpress-backup' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
