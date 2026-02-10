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
	<h1><?php esc_html_e( 'Import', 'r2-wordpress-backup' ); ?></h1>
	<p class="r2wb-description"><?php esc_html_e( 'Restore this site from a backup stored in R2.', 'r2-wordpress-backup' ); ?></p>
	<p><?php esc_html_e( 'Select a backup to restore. This will replace the current site data. Only use on this site.', 'r2-wordpress-backup' ); ?></p>
	<?php if ( empty( $backups ) ) : ?>
		<p><?php esc_html_e( 'No backups in R2.', 'r2-wordpress-backup' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Backup', 'r2-wordpress-backup' ); ?></th>
					<th><?php esc_html_e( 'Action', 'r2-wordpress-backup' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( array_reverse( $backups ) as $key ) : ?>
					<?php $name = basename( $key ); ?>
					<tr>
						<td><?php echo esc_html( $name ); ?></td>
						<td>
							<button type="button" class="button button-primary r2wb-restore-backup" data-key="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Restore this backup', 'r2-wordpress-backup' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<div class="r2wb-restore-progress" style="display:none; margin-top:1em;" role="status" aria-live="polite"></div>
</div>
