<?php
/**
 * Schedules page.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) || ! current_user_can( 'manage_options' ) ) {
	exit;
}

$current = R2WB_Scheduler::get_current_interval();
$next_run = R2WB_Scheduler::get_next_run();
$schedules = array(
	''             => __( 'No automatic backup', 'r2-wordpress-backup' ),
	'r2wb_daily'   => __( 'Once daily', 'r2-wordpress-backup' ),
	'r2wb_weekly'  => __( 'Once weekly', 'r2-wordpress-backup' ),
	'r2wb_monthly' => __( 'Once monthly', 'r2-wordpress-backup' ),
);
?>
<div class="wrap r2wb-wrap">
	<h1 class="text-xl font-semibold text-slate-900 mb-3"><?php esc_html_e( 'Schedules', 'r2-wordpress-backup' ); ?></h1>
	<p class="r2wb-description"><?php esc_html_e( 'Configure automatic backups to R2.', 'r2-wordpress-backup' ); ?></p>
	<?php settings_errors( 'r2wb_schedule' ); ?>

	<div class="bg-white shadow-sm rounded-lg border border-slate-200 p-4 sm:p-6">
		<form method="post" action="" class="space-y-4">
			<?php wp_nonce_field( 'r2wb_save_schedule', 'r2wb_schedule_nonce' ); ?>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
				<div class="space-y-2">
					<label for="r2wb_schedule_interval" class="block text-sm font-medium text-slate-700">
						<?php esc_html_e( 'Automatic backup', 'r2-wordpress-backup' ); ?>
					</label>
					<select name="r2wb_schedule_interval" id="r2wb_schedule_interval" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 text-sm">
						<?php foreach ( $schedules as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="text-xs text-slate-500">
						<?php esc_html_e( 'Choose how often a backup should be created and sent to R2.', 'r2-wordpress-backup' ); ?>
					</p>
				</div>
				<div class="space-y-2">
					<label class="block text-sm font-medium text-slate-700">
						<?php esc_html_e( 'Next run', 'r2-wordpress-backup' ); ?>
					</label>
					<p class="text-sm text-slate-700">
						<?php
						if ( $next_run ) {
							echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_run ) );
						} else {
							esc_html_e( 'No backup scheduled.', 'r2-wordpress-backup' );
						}
						?>
					</p>
				</div>
			</div>
			<div>
				<button type="submit" class="button button-primary inline-flex items-center px-4 py-2 rounded-md bg-sky-600 hover:bg-sky-700 text-white text-sm">
					<?php esc_html_e( 'Save schedule', 'r2-wordpress-backup' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>
