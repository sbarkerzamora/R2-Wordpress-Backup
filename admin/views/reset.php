<?php
/**
 * Reset Hub page.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) || ! current_user_can( 'manage_options' ) ) {
	exit;
}
?>
<div class="wrap r2wb-wrap">
	<h1><?php esc_html_e( 'Reset Hub', 'r2-wordpress-backup' ); ?></h1>
	<p class="r2wb-description"><?php esc_html_e( 'Reset plugin options and schedules. R2 credentials are kept unless you choose to clear them.', 'r2-wordpress-backup' ); ?></p>
	<p>
		<button type="button" class="button button-secondary r2wb-reset-options">
			<?php esc_html_e( 'Reset options and schedules', 'r2-wordpress-backup' ); ?>
		</button>
	</p>
</div>
