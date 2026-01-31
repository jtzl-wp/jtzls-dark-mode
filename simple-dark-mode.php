<?php
/**
 * Plugin Name: Simple Dark Mode
 * Plugin URI: https://github.com/jtzl/simple-dark-mode
 * Description: Automatic dark mode styling based on visitor OS preference using CSS prefers-color-scheme
 * Version: 1.0.0
 * Requires at least: 6.9
 * Requires PHP: 8.2
 * Author: JTZL
 * Author URI: https://jtzl.dev
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-dark-mode
 * Domain Path: /languages
 *
 * @package JTZL\Simple_Dark_Mode
 */

namespace JTZL\Simple_Dark_Mode;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'SIMPLE_DARK_MODE_VERSION', '1.0.0' );
define( 'SIMPLE_DARK_MODE_FILE', __FILE__ );
define( 'SIMPLE_DARK_MODE_PATH', plugin_dir_path( __FILE__ ) );

// PHP version check.
if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Simple Dark Mode requires PHP 8.2 or higher.', 'simple-dark-mode' );
			echo '</p></div>';
		}
	);
	return;
}

// WordPress version check.
if ( version_compare( $GLOBALS['wp_version'], '6.9', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Simple Dark Mode requires WordPress 6.9 or higher.', 'simple-dark-mode' );
			echo '</p></div>';
		}
	);
	return;
}

// Require Composer autoloader.
if ( file_exists( SIMPLE_DARK_MODE_PATH . 'vendor/autoload.php' ) ) {
	require_once SIMPLE_DARK_MODE_PATH . 'vendor/autoload.php';
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function init(): void {
	Plugin::instance();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );
