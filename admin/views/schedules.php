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
	<h1><?php esc_html_e( 'Schedules', 'r2-wordpress-backup' ); ?></h1>
	<p class="r2wb-description"><?php esc_html_e( 'Configure automatic backups to R2.', 'r2-wordpress-backup' ); ?></p>
	<?php settings_errors( 'r2wb_schedule' ); ?>
	<form method="post" action="">
		<?php wp_nonce_field( 'r2wb_save_schedule', 'r2wb_schedule_nonce' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="r2wb_schedule_interval"><?php esc_html_e( 'Automatic backup', 'r2-wordpress-backup' ); ?></label></th>
				<td>
					<select name="r2wb_schedule_interval" id="r2wb_schedule_interval">
						<?php foreach ( $schedules as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<?php if ( $next_run ) : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Next run', 'r2-wordpress-backup' ); ?></th>
					<td><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_run ) ); ?></td>
				</tr>
			<?php endif; ?>
		</table>
		<p class="submit">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save schedule', 'r2-wordpress-backup' ); ?></button>
		</p>
	</form>
</div>
