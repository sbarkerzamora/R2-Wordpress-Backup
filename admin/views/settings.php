<?php
/**
 * Settings page.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) || ! current_user_can( 'manage_options' ) ) {
	exit;
}

$account_id    = get_option( 'r2wb_account_id', '' );
$access_key    = get_option( 'r2wb_access_key_id', '' );
$secret_key    = ''; // Never output stored secret; leave blank in form. User fills to change.
$has_secret    = ( get_option( 'r2wb_secret_access_key', '' ) !== '' );
$bucket        = get_option( 'r2wb_bucket', '' );
$retention     = get_option( 'r2wb_retention_count', 5 );
$exclude_paths  = get_option( 'r2wb_exclude_paths', "wp-content/cache\nwp-content/debug.log" );
$exclude_tables = get_option( 'r2wb_exclude_tables', '' );
?>
<div class="wrap r2wb-wrap">
	<h1><?php esc_html_e( 'Settings', 'r2-wordpress-backup' ); ?></h1>
	<?php settings_errors( 'r2wb_settings' ); ?>
	<form method="post" action="" id="r2wb-settings-form" class="r2wb-form">
		<?php wp_nonce_field( 'r2wb_save_settings', 'r2wb_settings_nonce' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="r2wb_account_id"><?php esc_html_e( 'Account ID', 'r2-wordpress-backup' ); ?></label></th>
				<td><input type="text" id="r2wb_account_id" name="r2wb_account_id" value="<?php echo esc_attr( $account_id ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="r2wb_access_key_id"><?php esc_html_e( 'Access Key ID', 'r2-wordpress-backup' ); ?></label></th>
				<td><input type="text" id="r2wb_access_key_id" name="r2wb_access_key_id" value="<?php echo esc_attr( $access_key ); ?>" class="regular-text" autocomplete="off" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="r2wb_secret_access_key"><?php esc_html_e( 'Secret Access Key', 'r2-wordpress-backup' ); ?></label></th>
				<td>
					<input type="password" id="r2wb_secret_access_key" name="r2wb_secret_access_key" value="<?php echo esc_attr( $secret_key ); ?>" class="regular-text" autocomplete="off" placeholder="<?php echo $has_secret ? esc_attr( __( 'Leave blank to keep current', 'r2-wordpress-backup' ) ) : ''; ?>" />
					<?php if ( $has_secret ) : ?>
						<p class="description"><?php esc_html_e( 'Leave blank to keep the current secret.', 'r2-wordpress-backup' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="r2wb_bucket"><?php esc_html_e( 'Bucket name', 'r2-wordpress-backup' ); ?></label></th>
				<td><input type="text" id="r2wb_bucket" name="r2wb_bucket" value="<?php echo esc_attr( $bucket ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="r2wb_retention_count"><?php esc_html_e( 'Retention (keep last N backups)', 'r2-wordpress-backup' ); ?></label></th>
				<td><input type="number" id="r2wb_retention_count" name="r2wb_retention_count" value="<?php echo esc_attr( $retention ); ?>" min="1" max="100" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="r2wb_exclude_paths"><?php esc_html_e( 'Exclude paths (one per line)', 'r2-wordpress-backup' ); ?></label></th>
				<td><textarea id="r2wb_exclude_paths" name="r2wb_exclude_paths" rows="5" class="large-text"><?php echo esc_textarea( $exclude_paths ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="r2wb_exclude_tables"><?php esc_html_e( 'Exclude DB tables (one per line)', 'r2-wordpress-backup' ); ?></label></th>
				<td><textarea id="r2wb_exclude_tables" name="r2wb_exclude_tables" rows="3" class="large-text"><?php echo esc_textarea( $exclude_tables ); ?></textarea></td>
			</tr>
		</table>
		<p class="submit">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save settings', 'r2-wordpress-backup' ); ?></button>
			<button type="button" class="button r2wb-test-connection"><?php esc_html_e( 'Test connection', 'r2-wordpress-backup' ); ?></button>
		</p>
	</form>
	<p class="description"><?php esc_html_e( 'R2 free tier: 10 GB storage, 1M Class A and 10M Class B operations per month.', 'r2-wordpress-backup' ); ?></p>
</div>
