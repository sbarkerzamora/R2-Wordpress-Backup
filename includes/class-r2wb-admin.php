<?php
/**
 * Admin menu and pages.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class R2WB_Admin
 */
class R2WB_Admin {

	/**
	 * Menu slug for the main menu.
	 *
	 * @var string
	 */
	const MENU_SLUG = 'r2wb';

	/**
	 * Add admin menu and submenus.
	 * Menu is always registered with a plain title first so it never disappears on error.
	 */
	public function add_menu_pages() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$menu_title = __( 'R2 Cloud Backup', 'r2-wordpress-backup' );

		add_menu_page(
			__( 'R2 Cloud Backup', 'r2-wordpress-backup' ),
			$menu_title,
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_backups_page' ),
			'dashicons-cloud-upload',
			80
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Export', 'r2-wordpress-backup' ),
			__( 'Export', 'r2-wordpress-backup' ),
			'manage_options',
			'r2wb-export',
			array( $this, 'render_export_page' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Import', 'r2-wordpress-backup' ),
			__( 'Import', 'r2-wordpress-backup' ),
			'manage_options',
			'r2wb-import',
			array( $this, 'render_import_page' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Backups', 'r2-wordpress-backup' ),
			__( 'Backups', 'r2-wordpress-backup' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_backups_page' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Reset Hub', 'r2-wordpress-backup' ),
			__( 'Reset Hub', 'r2-wordpress-backup' ),
			'manage_options',
			'r2wb-reset',
			array( $this, 'render_reset_page' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Schedules', 'r2-wordpress-backup' ),
			__( 'Schedules', 'r2-wordpress-backup' ),
			'manage_options',
			'r2wb-schedules',
			array( $this, 'render_schedules_page' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'r2-wordpress-backup' ),
			__( 'Settings', 'r2-wordpress-backup' ),
			'manage_options',
			'r2wb-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * If the plugin is loaded from a folder other than r2-cloud-backup, another copy may be active; show notice.
	 */
	public function maybe_show_duplicate_plugin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$folder = dirname( R2WB_PLUGIN_BASENAME );
		if ( $folder === 'r2-cloud-backup' ) {
			return;
		}
		$dismissed = get_user_meta( get_current_user_id(), 'r2wb_dismiss_folder_notice', true );
		if ( $dismissed ) {
			return;
		}
		$message = __( 'R2 Cloud Backup: For the menu and updates to work correctly, the plugin folder must be named <strong>r2-cloud-backup</strong>. You may have another copy in a different folder; deactivate one and keep only <code>wp-content/plugins/r2-cloud-backup/</code>.', 'r2-wordpress-backup' );
		add_action( 'admin_notices', function () use ( $message ) {
			$nonce = wp_create_nonce( 'r2wb_dismiss_folder_notice' );
			echo '<div class="notice notice-warning is-dismissible r2wb-folder-notice" data-nonce="' . esc_attr( $nonce ) . '"><p>' . wp_kses( $message, array( 'strong' => array(), 'code' => array() ) ) . '</p></div>';
		} );
		add_action( 'admin_footer', array( $this, 'print_dismiss_folder_notice_script' ) );
	}

	/**
	 * AJAX: Dismiss the duplicate/folder notice.
	 */
	public function ajax_dismiss_folder_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'r2wb_dismiss_folder_notice' ) ) {
			wp_send_json_error();
		}
		update_user_meta( get_current_user_id(), 'r2wb_dismiss_folder_notice', 1 );
		wp_send_json_success();
	}

	/**
	 * Inline script to dismiss the folder notice via AJAX.
	 */
	public function print_dismiss_folder_notice_script() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<script>
		(function() {
			var notice = document.querySelector('.r2wb-folder-notice');
			if (!notice) return;
			var btn = notice.querySelector('.notice-dismiss');
			if (!btn) return;
			btn.addEventListener('click', function() {
				var form = new FormData();
				form.append('action', 'r2wb_dismiss_folder_notice');
				form.append('nonce', notice.getAttribute('data-nonce'));
				fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', body: form, credentials: 'same-origin' });
			});
		})();
		</script>
		<?php
	}

	/**
	 * Add backup count badge to the menu title (runs late on admin_menu so menu is already registered).
	 */
	public function add_menu_badge() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$count = 0;
		try {
			$count = $this->get_r2_backup_count();
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'R2 Cloud Backup: menu badge count failed – ' . $e->getMessage() );
			}
		}
		if ( $count <= 0 || ! isset( $GLOBALS['menu'] ) || ! is_array( $GLOBALS['menu'] ) ) {
			return;
		}
		$badge = ' <span class="awaiting-mod count-' . absint( $count ) . '"><span class="backup-count">' . number_format_i18n( $count ) . '</span></span>';
		foreach ( $GLOBALS['menu'] as $key => $item ) {
			if ( isset( $item[2] ) && $item[2] === self::MENU_SLUG ) {
				$GLOBALS['menu'][ $key ][0] = __( 'R2 Cloud Backup', 'r2-wordpress-backup' ) . $badge;
				break;
			}
		}
	}

	/**
	 * Get backup count from R2 for menu badge (cached briefly).
	 *
	 * @return int
	 */
	private function get_r2_backup_count() {
		$cache_key = 'r2wb_backup_count';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached && is_numeric( $cached ) ) {
			return (int) $cached;
		}

		try {
			$client = new R2WB_R2_Client();
			$count  = $client->list_backups_count();
			set_transient( $cache_key, $count, MINUTE_IN_SECONDS * 5 );
			return $count;
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'R2 Cloud Backup: get_r2_backup_count – ' . $e->getMessage() );
			}
			return 0;
		}
	}

	/**
	 * Enqueue admin CSS and JS.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( strpos( $hook_suffix, 'r2wb' ) === false ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// TailwindCSS via CDN, scoped only to R2 Cloud Backup admin pages.
		wp_enqueue_style(
			'r2wb-tailwind',
			'https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css',
			array(),
			R2WB_VERSION
		);

		wp_enqueue_style(
			'r2wb-admin',
			R2WB_PLUGIN_URL . 'admin/css/admin.css',
			array( 'r2wb-tailwind' ),
			R2WB_VERSION
		);

		wp_enqueue_script(
			'r2wb-admin',
			R2WB_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			R2WB_VERSION,
			true
		);

		wp_localize_script(
			'r2wb-admin',
			'r2wbAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'r2wb_admin' ),
				'strings' => array(
					'confirmReset'   => __( 'Reset plugin options and schedules? R2 credentials will be kept.', 'r2-wordpress-backup' ),
					'confirmDelete'  => __( 'Delete this backup from R2? This cannot be undone.', 'r2-wordpress-backup' ),
					'confirmRestore' => __( 'Restore this site from the selected backup? Current database and files will be replaced. This cannot be undone.', 'r2-wordpress-backup' ),
					'startingBackup' => __( 'Starting backup…', 'r2-wordpress-backup' ),
					'backupSuccess'  => __( 'Backup completed successfully.', 'r2-wordpress-backup' ),
					'backupFailed'   => __( 'Backup failed.', 'r2-wordpress-backup' ),
					'restoring'      => __( 'Restoring…', 'r2-wordpress-backup' ),
					'restoreSuccess' => __( 'Restore completed.', 'r2-wordpress-backup' ),
					'restoreFailed'  => __( 'Restore failed.', 'r2-wordpress-backup' ),
					'requestFailed'  => __( 'Request failed.', 'r2-wordpress-backup' ),
					'connectionOk'  => __( 'Connection successful.', 'r2-wordpress-backup' ),
					'connectionFailed' => __( 'Connection failed.', 'r2-wordpress-backup' ),
					'resetFailed'    => __( 'Reset failed.', 'r2-wordpress-backup' ),
					'deleteFailed'   => __( 'Delete failed.', 'r2-wordpress-backup' ),
				),
			)
		);
	}

	/**
	 * Render Backups list page.
	 */
	public function render_backups_page() {
		require_once R2WB_PLUGIN_DIR . 'admin/views/backups.php';
	}

	/**
	 * Render Export page.
	 */
	public function render_export_page() {
		require_once R2WB_PLUGIN_DIR . 'admin/views/export.php';
	}

	/**
	 * Render Import page.
	 */
	public function render_import_page() {
		require_once R2WB_PLUGIN_DIR . 'admin/views/import.php';
	}

	/**
	 * Render Reset Hub page.
	 */
	public function render_reset_page() {
		require_once R2WB_PLUGIN_DIR . 'admin/views/reset.php';
	}

	/**
	 * Render Schedules page.
	 */
	public function render_schedules_page() {
		require_once R2WB_PLUGIN_DIR . 'admin/views/schedules.php';
	}

	/**
	 * Render Settings page.
	 */
	public function render_settings_page() {
		require_once R2WB_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/**
	 * Render support sidebar (only on R2WB admin pages).
	 * Link only; no third-party scripts or images (Plugin Directory guideline 8).
	 */
	public function render_support_sidebar() {
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'r2wb' ) === false || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$bmc_url        = 'https://www.buymeacoffee.com/stephanbarker';
		$sidebar_title  = __( 'Support the project', 'r2-wordpress-backup' );
		$sidebar_text   = __( 'If you find this plugin useful, consider supporting its development.', 'r2-wordpress-backup' );
		$sidebar_button = __( 'Buy me a coffee', 'r2-wordpress-backup' );
		?>
		<aside class="r2wb-sidebar" id="r2wb-support-sidebar" role="complementary">
			<h3 class="r2wb-sidebar__title"><?php echo esc_html( $sidebar_title ); ?></h3>
			<p class="r2wb-sidebar__text"><?php echo esc_html( $sidebar_text ); ?></p>
			<a href="<?php echo esc_url( $bmc_url ); ?>" target="_blank" rel="noopener noreferrer" class="r2wb-sidebar__button"><?php echo esc_html( $sidebar_button ); ?></a>
		</aside>
		<?php
	}

	/**
	 * Save schedule if form submitted.
	 */
	public function maybe_save_schedule() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( empty( $_POST['r2wb_schedule_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['r2wb_schedule_nonce'] ) ), 'r2wb_save_schedule' ) ) {
			return;
		}
		$interval = isset( $_POST['r2wb_schedule_interval'] ) ? sanitize_text_field( wp_unslash( $_POST['r2wb_schedule_interval'] ) ) : '';
		$allowed = array( 'r2wb_daily', 'r2wb_weekly', 'r2wb_monthly', '' );
		if ( ! in_array( $interval, $allowed, true ) ) {
			$interval = '';
		}
		R2WB_Scheduler::set_schedule( $interval );
		add_settings_error(
			'r2wb_schedule',
			'saved',
			__( 'Schedule saved.', 'r2-wordpress-backup' ),
			'success'
		);
	}

	/**
	 * Save settings if form submitted with valid nonce.
	 */
	public function maybe_save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( empty( $_POST['r2wb_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['r2wb_settings_nonce'] ) ), 'r2wb_save_settings' ) ) {
			return;
		}
		if ( empty( $_POST['r2wb_account_id'] ) && empty( $_POST['r2wb_bucket'] ) ) {
			return;
		}

		update_option( 'r2wb_account_id', sanitize_text_field( wp_unslash( $_POST['r2wb_account_id'] ?? '' ) ) );
		update_option( 'r2wb_access_key_id', sanitize_text_field( wp_unslash( $_POST['r2wb_access_key_id'] ?? '' ) ) );
		$secret = isset( $_POST['r2wb_secret_access_key'] ) ? wp_unslash( $_POST['r2wb_secret_access_key'] ) : '';
		if ( (string) $secret !== '' ) {
			R2WB_Credentials::set_secret_key( sanitize_text_field( $secret ) );
		}
		update_option( 'r2wb_bucket', sanitize_text_field( wp_unslash( $_POST['r2wb_bucket'] ?? '' ) ) );
		$retention = isset( $_POST['r2wb_retention_count'] ) ? absint( $_POST['r2wb_retention_count'] ) : 5;
		$retention = max( 1, min( 100, $retention ) );
		update_option( 'r2wb_retention_count', $retention );
		update_option( 'r2wb_exclude_paths', sanitize_textarea_field( wp_unslash( $_POST['r2wb_exclude_paths'] ?? '' ) ) );
		update_option( 'r2wb_exclude_tables', sanitize_textarea_field( wp_unslash( $_POST['r2wb_exclude_tables'] ?? '' ) ) );

		delete_transient( 'r2wb_backup_count' );
		add_settings_error(
			'r2wb_settings',
			'saved',
			__( 'Settings saved.', 'r2-wordpress-backup' ),
			'success'
		);
	}

	/**
	 * AJAX: Test R2 connection.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'r2wb_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'r2-wordpress-backup' ) ) );
		}
		$client = new R2WB_R2_Client();
		$result = $client->test_connection();
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Connection successful.', 'r2-wordpress-backup' ) ) );
	}

	/**
	 * AJAX: Start manual backup.
	 */
	public function ajax_start_backup() {
		check_ajax_referer( 'r2wb_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'r2-wordpress-backup' ) ) );
		}
		$engine = new R2WB_Backup_Engine();
		$result = $engine->run_backup();
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		delete_transient( 'r2wb_backup_count' );
		wp_send_json_success( array( 'message' => __( 'Backup completed and uploaded to R2.', 'r2-wordpress-backup' ) ) );
	}

	/**
	 * AJAX: Reset options and schedules.
	 */
	public function ajax_reset_options() {
		check_ajax_referer( 'r2wb_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'r2-wordpress-backup' ) ) );
		}
		R2WB_Deactivator::deactivate();
		$options = array( 'r2wb_retention_count', 'r2wb_exclude_paths', 'r2wb_exclude_tables', 'r2wb_schedule_interval', 'r2wb_schedule_next' );
		foreach ( $options as $opt ) {
			delete_option( $opt );
		}
		delete_transient( 'r2wb_backup_count' );
		wp_send_json_success( array( 'message' => __( 'Options and schedules have been reset.', 'r2-wordpress-backup' ) ) );
	}

	/**
	 * AJAX: Download backup file (stream to browser).
	 */
	public function ajax_download_backup() {
		check_ajax_referer( 'r2wb_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'r2-wordpress-backup' ), 403 );
		}
		$key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
		if ( $key === '' ) {
			wp_die( esc_html__( 'Invalid key.', 'r2-wordpress-backup' ), 400 );
		}
		$client = new R2WB_R2_Client();
		$upload_dir = wp_upload_dir();
		$temp_path = trailingslashit( $upload_dir['basedir'] ) . 'r2-backup-temp/' . basename( $key );
		$result = $client->download( $key, $temp_path );
		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ), 500 );
		}
		$filename = basename( $key );
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . esc_attr( $filename ) . '"' );
		header( 'Content-Length: ' . filesize( $temp_path ) );
		readfile( $temp_path );
		@unlink( $temp_path );
		exit;
	}

	/**
	 * AJAX: Delete backup from R2.
	 */
	public function ajax_delete_backup() {
		check_ajax_referer( 'r2wb_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'r2-wordpress-backup' ) ) );
		}
		$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		if ( $key === '' ) {
			wp_send_json_error( array( 'message' => __( 'Invalid key.', 'r2-wordpress-backup' ) ) );
		}
		$client = new R2WB_R2_Client();
		$result = $client->delete( $key );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		delete_transient( 'r2wb_backup_count' );
		wp_send_json_success( array( 'message' => __( 'Backup deleted from R2.', 'r2-wordpress-backup' ) ) );
	}

	/**
	 * AJAX: Restore from backup (this site only).
	 */
	public function ajax_restore_backup() {
		check_ajax_referer( 'r2wb_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'r2-wordpress-backup' ) ) );
		}
		$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		if ( $key === '' ) {
			wp_send_json_error( array( 'message' => __( 'Invalid key.', 'r2-wordpress-backup' ) ) );
		}
		$restore = new R2WB_Restore();
		$result = $restore->restore( $key );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Restore completed.', 'r2-wordpress-backup' ) ) );
	}
}
