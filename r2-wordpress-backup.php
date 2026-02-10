<?php
/**
 * Plugin Name: R2 Cloud Backup
 * Plugin URI: https://github.com/sbarkerzamora/R2-Wordpress-Backup
 * Description: Full site backups (files + database) with automatic upload to Cloudflare R2 (S3-compatible API). Export, Import, Schedules, and Settings.
 * Version: 1.0.6
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: Stephan Barker
 * Author URI: https://stephanbarker.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: r2-wordpress-backup
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
	$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'r2-wordpress-backup' ) . '</a>';
	return $links;
}, 10, 1 );

// Register settings page so direct link works even when menu does not (e.g. duplicate plugin folder).
add_action( 'admin_menu', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$r2wb_main = __FILE__;
	add_submenu_page(
		null,
		__( 'R2 Cloud Backup', 'r2-wordpress-backup' ),
		null,
		'manage_options',
		'r2wb-settings',
		function () use ( $r2wb_main ) {
			$dir = plugin_dir_path( $r2wb_main );
			if ( ! defined( 'R2WB_PLUGIN_DIR' ) ) {
				define( 'R2WB_PLUGIN_DIR', $dir );
			}
			if ( ! defined( 'R2WB_PLUGIN_BASENAME' ) ) {
				define( 'R2WB_PLUGIN_BASENAME', plugin_basename( $r2wb_main ) );
			}
			if ( ! defined( 'R2WB_PLUGIN_URL' ) ) {
				define( 'R2WB_PLUGIN_URL', plugin_dir_url( $r2wb_main ) );
			}
			if ( ! defined( 'R2WB_VERSION' ) ) {
				define( 'R2WB_VERSION', '1.0.6' );
			}
			require_once $dir . 'includes/class-r2wb-credentials.php';
			require_once $dir . 'includes/class-r2wb-s3-signer.php';
			require_once $dir . 'includes/class-r2wb-r2-client.php';
			require_once $dir . 'includes/class-r2wb-backup-engine.php';
			require_once $dir . 'includes/class-r2wb-scheduler.php';
			require_once $dir . 'includes/class-r2wb-restore.php';
			require_once $dir . 'includes/class-r2wb-admin.php';
			$admin = new R2WB_Admin();
			$admin->render_settings_page();
		},
		1
	);
}, 1 );

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
					__( 'To open configuration, click <a href="%s">Settings</a> next to "Deactivate" in the R2 Cloud Backup row below.', 'r2-wordpress-backup' ),
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

define( 'R2WB_VERSION', '1.0.6' );
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
		}
		add_action( 'admin_notices', function () use ( $e ) {
			if ( current_user_can( 'manage_options' ) ) {
				echo '<div class="notice notice-error"><p><strong>R2 Cloud Backup:</strong> ' . esc_html( $e->getMessage() ) . '</p></div>';
			}
		} );
	}
}

r2wb_run();
