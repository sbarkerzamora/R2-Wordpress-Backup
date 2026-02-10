<?php
/**
 * Plugin Name: R2 WordPress Backup
 * Plugin URI: https://github.com/your-org/r2-wordpress-backup
 * Description: Full site backups (files + database) with automatic upload to Cloudflare R2 (S3-compatible API). Export, Import, Schedules, and Settings.
 * Version: 1.0.0
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

define( 'R2WB_VERSION', '1.0.0' );
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

	$loader->run();
}

r2wb_run();
