<?php
/**
 * Scheduler: WP-Cron intervals and scheduled backup events.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class R2WB_Scheduler
 */
class R2WB_Scheduler {

	const OPTION_INTERVAL = 'r2wb_schedule_interval';

	const INTERVALS = array( 'r2wb_daily', 'r2wb_weekly', 'r2wb_monthly' );

	/**
	 * Add custom cron intervals.
	 *
	 * @param array $schedules Existing schedules.
	 * @return array
	 */
	public static function add_cron_intervals( $schedules ) {
		$schedules['r2wb_daily'] = array(
			'interval' => DAY_IN_SECONDS,
			'display'  => __( 'Once daily', 'r2-cloud-backup' ),
		);
		$schedules['r2wb_weekly'] = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Once weekly', 'r2-cloud-backup' ),
		);
		$schedules['r2wb_monthly'] = array(
			'interval' => 30 * DAY_IN_SECONDS,
			'display'  => __( 'Once monthly', 'r2-cloud-backup' ),
		);
		return $schedules;
	}

	/**
	 * Get current schedule interval (or empty string if none).
	 *
	 * @return string
	 */
	public static function get_current_interval() {
		$interval = get_option( self::OPTION_INTERVAL, '' );
		return in_array( $interval, self::INTERVALS, true ) ? $interval : '';
	}

	/**
	 * Set schedule: clear existing and schedule new.
	 *
	 * @param string $interval One of r2wb_daily, r2wb_weekly, r2wb_monthly, or '' for none.
	 * @return bool True on success.
	 */
	public static function set_schedule( $interval ) {
		wp_clear_scheduled_hook( 'r2wb_run_scheduled_backup' );
		update_option( self::OPTION_INTERVAL, $interval === '' ? '' : $interval );
		if ( $interval !== '' && in_array( $interval, self::INTERVALS, true ) ) {
			wp_schedule_event( time(), $interval, 'r2wb_run_scheduled_backup' );
			return true;
		}
		return true;
	}

	/**
	 * Get next scheduled run timestamp.
	 *
	 * @return int|false Unix timestamp or false if not scheduled.
	 */
	public static function get_next_run() {
		return wp_next_scheduled( 'r2wb_run_scheduled_backup' );
	}
}
