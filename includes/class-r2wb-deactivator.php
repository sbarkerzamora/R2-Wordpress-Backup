<?php
/**
 * Fired during plugin deactivation.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class R2WB_Deactivator
 */
class R2WB_Deactivator {

	/**
	 * Plugin deactivation: clear scheduled cron events.
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'r2wb_run_scheduled_backup' );
	}
}
