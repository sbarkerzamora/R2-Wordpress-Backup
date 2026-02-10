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
	<h1 class="text-xl font-semibold text-slate-900 mb-3"><?php esc_html_e( 'Settings', 'r2-wordpress-backup' ); ?></h1>
	<?php settings_errors( 'r2wb_settings' ); ?>
	<form method="post" action="" id="r2wb-settings-form" class="r2wb-form space-y-6">
		<?php wp_nonce_field( 'r2wb_save_settings', 'r2wb_settings_nonce' ); ?>

		<div class="bg-white shadow-sm rounded-lg border border-slate-200 p-4 sm:p-6 space-y-4">
			<h2 class="text-sm font-semibold text-slate-800"><?php esc_html_e( 'Cloudflare R2 credentials', 'r2-wordpress-backup' ); ?></h2>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div>
					<label for="r2wb_account_id" class="block text-sm font-medium text-slate-700">
						<?php esc_html_e( 'Account ID', 'r2-wordpress-backup' ); ?>
					</label>
					<input type="text" id="r2wb_account_id" name="r2wb_account_id" value="<?php echo esc_attr( $account_id ); ?>" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 text-sm" />
				</div>
				<div>
					<label for="r2wb_bucket" class="block text-sm font-medium text-slate-700">
						<?php esc_html_e( 'Bucket name', 'r2-wordpress-backup' ); ?>
					</label>
					<input type="text" id="r2wb_bucket" name="r2wb_bucket" value="<?php echo esc_attr( $bucket ); ?>" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 text-sm" />
				</div>
				<div>
					<label for="r2wb_access_key_id" class="block text-sm font-medium text-slate-700">
						<?php esc_html_e( 'Access Key ID', 'r2-wordpress-backup' ); ?>
					</label>
					<input type="text" id="r2wb_access_key_id" name="r2wb_access_key_id" value="<?php echo esc_attr( $access_key ); ?>" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 text-sm" autocomplete="off" />
				</div>
				<div>
					<label for="r2wb_secret_access_key" class="block text-sm font-medium text-slate-700">
						<?php esc_html_e( 'Secret Access Key', 'r2-wordpress-backup' ); ?>
					</label>
					<input type="password" id="r2wb_secret_access_key" name="r2wb_secret_access_key" value="<?php echo esc_attr( $secret_key ); ?>" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 text-sm" autocomplete="off" placeholder="<?php echo $has_secret ? esc_attr( __( 'Leave blank to keep current', 'r2-wordpress-backup' ) ) : ''; ?>" />
					<?php if ( $has_secret ) : ?>
						<p class="description text-xs text-slate-500 mt-1"><?php esc_html_e( 'Leave blank to keep the current secret.', 'r2-wordpress-backup' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="bg-white shadow-sm rounded-lg border border-dashed border-sky-200 p-4 sm:p-6 space-y-3">
			<h2 class="text-sm font-semibold text-slate-800">
				<?php esc_html_e( 'Cloudflare R2 integration guide', 'r2-wordpress-backup' ); ?>
			</h2>
			<p class="text-xs text-slate-600">
				<?php esc_html_e( 'Follow these steps to connect this site to your Cloudflare R2 bucket using the S3-compatible API.', 'r2-wordpress-backup' ); ?>
			</p>
			<ol class="list-decimal list-inside space-y-1 text-xs text-slate-600">
				<li>
					<?php esc_html_e( 'In Cloudflare, go to R2 and create a bucket (for example, "wp-backups").', 'r2-wordpress-backup' ); ?>
				</li>
				<li>
					<?php esc_html_e( 'Copy the Account ID from the R2 dashboard and paste it into the "Account ID" field above.', 'r2-wordpress-backup' ); ?>
				</li>
				<li>
					<?php esc_html_e( 'Create an API token with permission to read and write to your R2 bucket using the S3-compatible API.', 'r2-wordpress-backup' ); ?>
				</li>
				<li>
					<?php esc_html_e( 'From that token, copy the Access Key ID and Secret Access Key and paste them into the fields above.', 'r2-wordpress-backup' ); ?>
				</li>
				<li>
					<?php esc_html_e( 'Enter the exact bucket name in the "Bucket name" field.', 'r2-wordpress-backup' ); ?>
				</li>
				<li>
					<?php esc_html_e( 'Click "Save settings" and then "Test connection" to verify that backups can be uploaded to R2.', 'r2-wordpress-backup' ); ?>
				</li>
			</ol>
			<p class="text-[11px] text-slate-500">
				<?php esc_html_e( 'Once connected, manual and scheduled backups will be created on this site and automatically uploaded to your R2 bucket.', 'r2-wordpress-backup' ); ?>
			</p>
		</div>

		<div class="bg-white shadow-sm rounded-lg border border-slate-200 p-4 sm:p-6 space-y-4">
			<h2 class="text-sm font-semibold text-slate-800"><?php esc_html_e( 'Backup options', 'r2-wordpress-backup' ); ?></h2>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div>
					<label for="r2wb_retention_count" class="block text-sm font-medium text-slate-700">
						<?php esc_html_e( 'Retention (keep last N backups)', 'r2-wordpress-backup' ); ?>
					</label>
					<input type="number" id="r2wb_retention_count" name="r2wb_retention_count" value="<?php echo esc_attr( $retention ); ?>" min="1" max="100" class="mt-1 block w-32 rounded-md border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 text-sm" />
					<p class="description text-xs text-slate-500 mt-1">
						<?php esc_html_e( 'Older backups beyond this number will be deleted from R2 automatically.', 'r2-wordpress-backup' ); ?>
					</p>
				</div>
				<div>
					<label for="r2wb_exclude_paths" class="block text-sm font-medium text-slate-700">
						<?php esc_html_e( 'Exclude paths (one per line)', 'r2-wordpress-backup' ); ?>
					</label>
					<textarea id="r2wb_exclude_paths" name="r2wb_exclude_paths" rows="5" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 text-sm"><?php echo esc_textarea( $exclude_paths ); ?></textarea>
					<p class="description text-xs text-slate-500 mt-1">
						<?php esc_html_e( 'Paths are relative to the WordPress root (e.g. wp-content/cache).', 'r2-wordpress-backup' ); ?>
					</p>
				</div>
				<div class="md:col-span-2">
					<label for="r2wb_exclude_tables" class="block text-sm font-medium text-slate-700">
						<?php esc_html_e( 'Exclude DB tables (one per line)', 'r2-wordpress-backup' ); ?>
					</label>
					<textarea id="r2wb_exclude_tables" name="r2wb_exclude_tables" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 text-sm"><?php echo esc_textarea( $exclude_tables ); ?></textarea>
					<p class="description text-xs text-slate-500 mt-1">
						<?php esc_html_e( 'Enter full table names to skip in the database export.', 'r2-wordpress-backup' ); ?>
					</p>
				</div>
			</div>
		</div>

		<div class="flex items-center justify-between">
			<p class="text-xs text-slate-500">
				<?php esc_html_e( 'R2 free tier: 10 GB storage, 1M Class A and 10M Class B operations per month.', 'r2-wordpress-backup' ); ?>
			</p>
			<div class="space-x-2">
				<button type="submit" class="button button-primary inline-flex items-center px-4 py-2 rounded-md bg-sky-600 hover:bg-sky-700 text-white text-sm">
					<?php esc_html_e( 'Save settings', 'r2-wordpress-backup' ); ?>
				</button>
				<button type="button" class="button r2wb-test-connection inline-flex items-center px-4 py-2 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm">
					<?php esc_html_e( 'Test connection', 'r2-wordpress-backup' ); ?>
				</button>
			</div>
		</div>
	</form>
</div>
