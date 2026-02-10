<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options = array(
	'r2wb_version',
	'r2wb_account_id',
	'r2wb_access_key_id',
	'r2wb_secret_access_key',
	'r2wb_bucket',
	'r2wb_retention_count',
	'r2wb_exclude_paths',
	'r2wb_exclude_tables',
	'r2wb_schedule_interval',
	'r2wb_schedule_next',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

delete_transient( 'r2wb_backup_count' );

wp_clear_scheduled_hook( 'r2wb_run_scheduled_backup' );
