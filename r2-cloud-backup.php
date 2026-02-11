<?php
/**
 * Plugin Name: R2 Cloud Backup
 * Plugin URI: https://github.com/sbarkerzamora/R2-Wordpress-Backup
 * Description: Full site backups (files + database) with automatic upload to Cloudflare R2 (S3-compatible API). Export, Import, Schedules, and Settings.
 * Version: 1.0.7
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: Stephan Barker
 * Author URI: https://stephanbarker.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: r2-cloud-backup
 * Domain Path: /languages
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Settings link on Plugins page (registered for every copy so it works even when this copy does not run).
$r2wb_this_basename = plugin_basename( __FILE__ );
add_filter( 'plugin_action_links_' . $r2wb_this_basename, function ( $links ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return $links;
	}
	$url = admin_url( 'admin.php?page=r2wb-settings' );
	$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'r2-cloud-backup' ) . '</a>';
	return $links;
}, 10, 1 );

// Register admin menu from EVERY copy that loads (before the early return).
// When two copies exist (e.g. r2-cloud-backup + r2-wordpress-backup), the one that loads second
// bails before adding the menu. This copy then never registers the page, causing 403 on direct access.
add_action( 'admin_menu', function () {
	$r2wb_file = __FILE__;
	$r2wb_dir  = plugin_dir_path( $r2wb_file );
	if ( ! defined( 'R2WB_PLUGIN_DIR' ) ) {
		define( 'R2WB_PLUGIN_DIR', $r2wb_dir );
	}
	if ( ! defined( 'R2WB_PLUGIN_BASENAME' ) ) {
		define( 'R2WB_PLUGIN_BASENAME', plugin_basename( $r2wb_file ) );
	}
	if ( ! defined( 'R2WB_PLUGIN_URL' ) ) {
		define( 'R2WB_PLUGIN_URL', plugin_dir_url( $r2wb_file ) );
	}
		if ( ! defined( 'R2WB_VERSION' ) ) {
			define( 'R2WB_VERSION', '1.0.7' );
		}
	require_once $r2wb_dir . 'includes/class-r2wb-credentials.php';
	require_once $r2wb_dir . 'includes/class-r2wb-s3-signer.php';
	require_once $r2wb_dir . 'includes/class-r2wb-r2-client.php';
	require_once $r2wb_dir . 'includes/class-r2wb-backup-engine.php';
	require_once $r2wb_dir . 'includes/class-r2wb-scheduler.php';
	require_once $r2wb_dir . 'includes/class-r2wb-restore.php';
	require_once $r2wb_dir . 'includes/class-r2wb-admin.php';
	$admin = new R2WB_Admin();
	$admin->add_menu_pages();
}, 10 );

// Reminder on Plugins screen (once per request).
if ( ! isset( $GLOBALS['r2wb_plugins_notice_done'] ) ) {
	$GLOBALS['r2wb_plugins_notice_done'] = true;
	add_action( 'load-plugins.php', function () {
		add_action( 'admin_notices', function () {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$url = admin_url( 'admin.php?page=r2wb-settings' );
			echo '<div class="notice notice-info"><p><strong>R2 Cloud Backup:</strong> ';
			echo wp_kses(
				sprintf(
					/* translators: %s: link to settings page */
					__( 'To open configuration, click <a href="%s">Settings</a> next to "Deactivate" in the R2 Cloud Backup row below.', 'r2-cloud-backup' ),
					esc_url( $url )
				),
				array( 'a' => array( 'href' => array() ) )
			);
			echo '</p></div>';
		}, 5 );
	} );
}

// Prevent fatal error if another copy of the plugin is loaded (e.g. old version in a second folder).
if ( function_exists( 'r2wb_run' ) ) {
	return;
}

// Optional: show a small diagnostic when WP_DEBUG is on (confirms plugin loaded).
if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_admin() ) {
	add_action( 'admin_notices', function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		if ( strpos( $page, 'r2wb' ) !== 0 ) {
			return;
		}
		$dir_ok = defined( 'R2WB_PLUGIN_DIR' ) && is_readable( R2WB_PLUGIN_DIR . 'admin/js/admin.js' );
		$url_ok = defined( 'R2WB_PLUGIN_URL' ) && R2WB_PLUGIN_URL !== '';
		echo '<div class="notice notice-info"><p><strong>R2 Cloud Backup</strong> ';
		echo esc_html( defined( 'R2WB_VERSION' ) ? 'v' . R2WB_VERSION : '' );
		echo ' — ';
		echo $dir_ok && $url_ok ? esc_html__( 'Plugin loaded. Path and URL OK.', 'r2-cloud-backup' ) : esc_html__( 'Plugin loaded. Check path/URL if assets fail.', 'r2-cloud-backup' );
		echo '</p></div>';
	}, 1 );
}

define( 'R2WB_VERSION', '1.0.7' );
define( 'R2WB_PLUGIN_FILE', __FILE__ );
define( 'R2WB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'R2WB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'R2WB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once R2WB_PLUGIN_DIR . 'includes/class-r2wb-activator.php';
require_once R2WB_PLUGIN_DIR . 'includes/class-r2wb-deactivator.php';
require_once R2WB_PLUGIN_DIR . 'includes/class-r2wb-credentials.php';
require_once R2WB_PLUGIN_DIR . 'includes/class-r2wb-loader.php';
require_once R2WB_PLUGIN_DIR . 'includes/class-r2wb-admin.php';

function r2wb_run() {
	$activator = new R2WB_Activator();
	$loader    = new R2WB_Loader();

	register_activation_hook( R2WB_PLUGIN_FILE, array( $activator, 'activate' ) );
	register_deactivation_hook( R2WB_PLUGIN_FILE, array( 'R2WB_Deactivator', 'deactivate' ) );

	try {
		$loader->run();
	} catch ( \Throwable $e ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'R2 Cloud Backup: ' . $e->getMessage() );
			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( 'R2 Cloud Backup stack: ' . $e->getTraceAsString() );
			}
		}
		add_action( 'admin_notices', function () use ( $e ) {
			if ( current_user_can( 'manage_options' ) ) {
				echo '<div class="notice notice-error"><p><strong>R2 Cloud Backup:</strong> ' . esc_html( $e->getMessage() ) . '</p></div>';
			}
		} );
	}
}

r2wb_run();
