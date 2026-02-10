<?php
/**
 * Fired during plugin activation.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class R2WB_Activator
 */
class R2WB_Activator {

	/**
	 * Plugin activation: set default options and ensure temp directory exists.
	 */
	public function activate() {
		$this->set_default_options();
		$this->ensure_temp_dir();
	}

	/**
	 * Set default plugin options.
	 */
	private function set_default_options() {
		$defaults = array(
			'r2wb_version'           => R2WB_VERSION,
			'r2wb_account_id'         => '',
			'r2wb_access_key_id'      => '',
			'r2wb_secret_access_key'  => '',
			'r2wb_bucket'             => '',
			'r2wb_retention_count'    => 5,
			'r2wb_exclude_paths'       => "wp-content/cache\nwp-content/debug.log\nwp-content/uploads/r2-backup-temp",
			'r2wb_exclude_tables'     => '',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key, false ) ) {
				add_option( $key, $value );
			}
		}
	}

	/**
	 * Create temp directory for backups and protect it.
	 */
	private function ensure_temp_dir() {
		$upload_dir = wp_upload_dir();
		$temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'r2-backup-temp';

		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );
		}

		$htaccess = $temp_dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			// Deny web access to temp backup files.
			file_put_contents( $htaccess, "Deny from all\n" );
		}

		$index = $temp_dir . '/index.php';
		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}
	}
}
