<?php
/**
 * Backup engine: database dump, file collection, ZIP, upload to R2.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class R2WB_Backup_Engine
 */
class R2WB_Backup_Engine {

	/**
	 * Run a scheduled backup (called by WP-Cron).
	 */
	public static function run_scheduled_backup() {
		$engine = new self();
		$engine->run_backup();
	}

	/**
	 * Run full backup and upload to R2.
	 *
	 * @return true|WP_Error
	 */
	public function run_backup() {
		$client = new R2WB_R2_Client();
		$config = $this->get_config();
		if ( $config === null ) {
			return new WP_Error( 'r2wb_not_configured', __( 'R2 credentials not configured.', 'r2-wordpress-backup' ) );
		}

		$upload_dir = wp_upload_dir();
		$temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'r2-backup-temp';
		if ( ! is_dir( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );
		}

		$date_slug = gmdate( 'Y-m-d-His' );
		$zip_name  = $date_slug . '-full.zip';
		$zip_path  = $temp_dir . '/' . $zip_name;

		$zip = new ZipArchive();
		if ( $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
			return new WP_Error( 'r2wb_zip_failed', __( 'Could not create ZIP file.', 'r2-wordpress-backup' ) );
		}

		// 1. Database dump
		$sql = $this->export_database();
		if ( is_wp_error( $sql ) ) {
			$zip->close();
			@unlink( $zip_path );
			return $sql;
		}
		$zip->addFromString( 'database.sql', $sql );

		// 2. Files (full WordPress installation, excluding configured paths and temp dir).
		$exclude_paths = $this->get_exclude_paths();
		$base         = trailingslashit( ABSPATH );
		$this->add_directory_to_zip( $zip, $base, '', $base, $exclude_paths );

		$zip->close();

		$remote_key = $client->get_prefix() . $zip_name;
		$result = $client->upload( $zip_path, $remote_key );
		@unlink( $zip_path );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$this->apply_retention( $client );
		delete_transient( 'r2wb_backup_count' );
		return true;
	}

	/**
	 * Get config (same as R2 client).
	 *
	 * @return array|null
	 */
	private function get_config() {
		$account_id = get_option( 'r2wb_account_id', '' );
		$access_key = get_option( 'r2wb_access_key_id', '' );
		$secret_key = R2WB_Credentials::get_secret_key();
		$bucket     = get_option( 'r2wb_bucket', '' );
		if ( $account_id === '' || $access_key === '' || $secret_key === '' || $bucket === '' ) {
			return null;
		}
		return array( 'account_id' => $account_id, 'access_key' => $access_key, 'secret_key' => $secret_key, 'bucket' => $bucket );
	}

	/**
	 * Export database to SQL string (PHP only).
	 *
	 * @return string|WP_Error
	 */
	private function export_database() {
		global $wpdb;
		$exclude_tables = $this->get_exclude_tables();
		$tables = $wpdb->get_col( 'SHOW TABLES' );
		if ( empty( $tables ) ) {
			return new WP_Error( 'r2wb_db_export', __( 'No tables found.', 'r2-wordpress-backup' ) );
		}

		$out = "-- R2 Cloud Backup - Database dump\n";
		$out .= "-- " . gmdate( 'Y-m-d H:i:s' ) . " UTC\n\n";
		$out .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

		foreach ( $tables as $table ) {
			if ( in_array( $table, $exclude_tables, true ) ) {
				continue;
			}
			$t_esc = '`' . str_replace( '`', '``', $table ) . '`';
			$create = $wpdb->get_row( "SHOW CREATE TABLE " . $t_esc, ARRAY_N );
			if ( $create ) {
				$out .= "DROP TABLE IF EXISTS " . $t_esc . ";\n";
				$out .= $create[1] . ";\n\n";
			}
			$rows = $wpdb->get_results( "SELECT * FROM " . $t_esc, ARRAY_A );
			if ( ! empty( $rows ) ) {
				foreach ( $rows as $row ) {
					$values = array();
					foreach ( $row as $v ) {
						$values[] = ( $v === null ) ? 'NULL' : "'" . $wpdb->_real_escape( $v ) . "'";
					}
					$cols = array_map( function ( $c ) {
						return '`' . str_replace( '`', '``', $c ) . '`';
					}, array_keys( $row ) );
					$out .= "INSERT INTO " . $t_esc . " (" . implode( ',', $cols ) . ") VALUES (" . implode( ',', $values ) . ");\n";
				}
				$out .= "\n";
			}
		}
		$out .= "SET FOREIGN_KEY_CHECKS=1;\n";
		return $out;
	}

	/**
	 * Get exclude paths (relative to ABSPATH or wp-content).
	 *
	 * @return array
	 */
	private function get_exclude_paths() {
		$raw = get_option( 'r2wb_exclude_paths', '' );
		$lines = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
		return array_values( $lines );
	}

	/**
	 * Get exclude table names.
	 *
	 * @return array
	 */
	private function get_exclude_tables() {
		$raw = get_option( 'r2wb_exclude_tables', '' );
		$lines = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
		return array_values( $lines );
	}

	/**
	 * Add directory to ZIP recursively, with exclusions.
	 *
	 * @param ZipArchive $zip ZipArchive instance.
	 * @param string     $dir Full path to directory.
	 * @param string     $zip_prefix Prefix inside ZIP (e.g. wp-content/).
	 * @param string     $base_abs ABSPATH.
	 * @param array      $exclude_paths Paths to exclude (relative segments).
	 */
	private function add_directory_to_zip( ZipArchive $zip, $dir, $zip_prefix, $base_abs, array $exclude_paths ) {
		$iter = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS ),
			RecursiveIteratorIterator::SELF_FIRST
		);
		$base_len = strlen( $base_abs );
		foreach ( $iter as $path ) {
			$full = $path->getPathname();
			$rel = substr( $full, $base_len );
			$rel = str_replace( '\\', '/', $rel );
			$skip = false;
			foreach ( $exclude_paths as $ex ) {
				$ex = ltrim( str_replace( '\\', '/', $ex ), '/' );
				if ( $ex === '' ) {
					continue;
				}
				if ( strpos( $rel, $ex ) === 0 || strpos( $rel, $ex . '/' ) !== false ) {
					$skip = true;
					break;
				}
			}
			if ( $skip ) {
				continue;
			}
			if ( $path->isDir() ) {
				$zip->addEmptyDir( $zip_prefix . $rel . '/' );
			} else {
				if ( is_readable( $full ) ) {
					$zip->addFile( $full, $zip_prefix . $rel );
				}
			}
		}
	}

	/**
	 * Delete oldest backups beyond retention count.
	 *
	 * @param R2WB_R2_Client $client R2 client.
	 */
	private function apply_retention( R2WB_R2_Client $client ) {
		$keep = (int) get_option( 'r2wb_retention_count', 5 );
		$keys = $client->list_backups();
		if ( count( $keys ) <= $keep ) {
			return;
		}
		rsort( $keys );
		$to_remove = array_slice( $keys, $keep );
		foreach ( $to_remove as $key ) {
			$client->delete( $key );
		}
	}
}
