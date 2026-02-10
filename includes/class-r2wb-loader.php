<?php
/**
 * Register all actions and filters for the plugin.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class R2WB_Loader
 */
class R2WB_Loader {

	/**
	 * Admin class instance.
	 *
	 * @var R2WB_Admin
	 */
	private $admin;

	/**
	 * Run the loader: register hooks.
	 */
	public function run() {
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_cron_hooks();
	}

	/**
	 * Load required dependency classes.
	 */
	private function load_dependencies() {
		require_once R2WB_PLUGIN_DIR . 'includes/class-r2wb-s3-signer.php';
		require_once R2WB_PLUGIN_DIR . 'includes/class-r2wb-r2-client.php';
		require_once R2WB_PLUGIN_DIR . 'includes/class-r2wb-backup-engine.php';
		require_once R2WB_PLUGIN_DIR . 'includes/class-r2wb-scheduler.php';
		require_once R2WB_PLUGIN_DIR . 'includes/class-r2wb-restore.php';

		$this->admin = new R2WB_Admin();
	}

	/**
	 * Register admin hooks.
	 */
	private function define_admin_hooks() {
		add_action( 'admin_menu', array( $this->admin, 'add_menu_pages' ), 9999 );
		add_filter( 'plugin_action_links_' . R2WB_PLUGIN_BASENAME, array( $this->admin, 'plugin_action_links' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_assets' ) );
		add_action( 'admin_footer', array( $this->admin, 'render_support_sidebar' ) );
		add_action( 'admin_init', array( $this->admin, 'maybe_save_settings' ) );
		add_action( 'admin_init', array( $this->admin, 'maybe_save_schedule' ) );
		add_action( 'admin_init', array( $this->admin, 'maybe_show_duplicate_plugin_notice' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );

		add_action( 'wp_ajax_r2wb_test_connection', array( $this->admin, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_r2wb_start_backup', array( $this->admin, 'ajax_start_backup' ) );
		add_action( 'wp_ajax_r2wb_reset_options', array( $this->admin, 'ajax_reset_options' ) );
		add_action( 'wp_ajax_r2wb_download_backup', array( $this->admin, 'ajax_download_backup' ) );
		add_action( 'wp_ajax_r2wb_delete_backup', array( $this->admin, 'ajax_delete_backup' ) );
		add_action( 'wp_ajax_r2wb_restore_backup', array( $this->admin, 'ajax_restore_backup' ) );
		add_action( 'wp_ajax_r2wb_dismiss_folder_notice', array( $this->admin, 'ajax_dismiss_folder_notice' ) );
	}

	/**
	 * Register cron hook for scheduled backups.
	 */
	private function define_cron_hooks() {
		add_action( 'r2wb_run_scheduled_backup', array( 'R2WB_Backup_Engine', 'run_scheduled_backup' ) );
		add_filter( 'cron_schedules', array( 'R2WB_Scheduler', 'add_cron_intervals' ) );
	}

	/**
	 * Load plugin text domain for i18n.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'r2-wordpress-backup',
			false,
			(string) dirname( R2WB_PLUGIN_BASENAME ) . '/languages'
		);
	}
}
