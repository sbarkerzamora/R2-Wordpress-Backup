<?php
/**
 * Restore: download from R2, extract, import DB and files (this site only).
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class R2WB_Restore
 */
class R2WB_Restore {

	/**
	 * Restore from a backup key in R2 (this site only).
	 *
	 * @param string $remote_key Key in R2 bucket.
	 * @return true|WP_Error
	 */
	public function restore( $remote_key ) {
		$client = new R2WB_R2_Client();
		$upload_dir = wp_upload_dir();
		$temp_dir = trailingslashit( $upload_dir['basedir'] ) . 'r2-backup-temp/restore-' . gmdate( 'Y-m-d-His' );
		if ( ! is_dir( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );
		}

		$zip_path = $temp_dir . '/' . basename( $remote_key );
		$result = $client->download( $remote_key, $zip_path );
		if ( is_wp_error( $result ) ) {
			$this->rmdir_recursive( $temp_dir );
			return $result;
		}

		$zip = new ZipArchive();
		if ( $zip->open( $zip_path ) !== true ) {
			$this->rmdir_recursive( $temp_dir );
			return new WP_Error( 'r2wb_restore_zip', __( 'Could not open backup file.', 'r2-wordpress-backup' ) );
		}

		$extract_dir = $temp_dir . '/extract';
		wp_mkdir_p( $extract_dir );
		$zip->extractTo( $extract_dir );
		$zip->close();
		@unlink( $zip_path );

		// 1. Restore database
		$sql_file = $extract_dir . '/database.sql';
		if ( file_exists( $sql_file ) ) {
			$err = $this->import_database( $sql_file );
			if ( is_wp_error( $err ) ) {
				$this->rmdir_recursive( $temp_dir );
				return $err;
			}
		}

		// 2. Restore files (wp-content)
		$wp_content_src = $extract_dir . '/wp-content';
		if ( is_dir( $wp_content_src ) ) {
			$this->copy_directory( $wp_content_src, trailingslashit( ABSPATH ) . 'wp-content' );
		}

		$this->rmdir_recursive( $temp_dir );
		return true;
	}

	/**
	 * Import SQL file (run statements one by one).
	 *
	 * @param string $sql_path Full path to database.sql.
	 * @return true|WP_Error
	 */
	private function import_database( $sql_path ) {
		global $wpdb;
		$sql = file_get_contents( $sql_path );
		if ( $sql === false ) {
			return new WP_Error( 'r2wb_restore_read', __( 'Could not read database file.', 'r2-wordpress-backup' ) );
		}
		// Split by semicolon outside of quoted strings (simplified: split on ";\n" and ";\r\n").
		$statements = array_filter( array_map( 'trim', preg_split( '/;\s*[\r\n]+/', $sql ) ) );
		foreach ( $statements as $stmt ) {
			if ( $stmt === '' || strpos( $stmt, '--' ) === 0 ) {
				continue;
			}
			$wpdb->query( $stmt );
			if ( $wpdb->last_error ) {
				return new WP_Error( 'r2wb_restore_db', __( 'Database error during restore: ', 'r2-wordpress-backup' ) . $wpdb->last_error );
			}
		}
		return true;
	}

	/**
	 * Copy directory recursively.
	 *
	 * @param string $src Source directory.
	 * @param string $dst Destination directory.
	 */
	private function copy_directory( $src, $dst ) {
		$dir = opendir( $src );
		if ( ! $dir ) {
			return;
		}
		if ( ! is_dir( $dst ) ) {
			wp_mkdir_p( $dst );
		}
		while ( ( $file = readdir( $dir ) ) !== false ) {
			if ( $file === '.' || $file === '..' ) {
				continue;
			}
			$src_path = $src . '/' . $file;
			$dst_path = $dst . '/' . $file;
			if ( is_dir( $src_path ) ) {
				$this->copy_directory( $src_path, $dst_path );
			} else {
				copy( $src_path, $dst_path );
			}
		}
		closedir( $dir );
	}

	/**
	 * Remove directory recursively.
	 *
	 * @param string $dir Path.
	 */
	private function rmdir_recursive( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$files = array_diff( scandir( $dir ), array( '.', '..' ) );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			if ( is_dir( $path ) ) {
				$this->rmdir_recursive( $path );
			} else {
				@unlink( $path );
			}
		}
		@rmdir( $dir );
	}
}
