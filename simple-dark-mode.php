<?php
/**
 * Plugin Name: Simple Dark Mode
 * Plugin URI: https://github.com/jtzl/simple-dark-mode
 * Description: Automatic dark mode styling based on visitor OS preference using CSS prefers-color-scheme
 * Version: 1.0.0-beta.1
 * Requires at least: 6.9
 * Requires PHP: 8.2
 * Author: JT G.
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-dark-mode
 * Domain Path: /languages
 *
 * @package JTZL\Simple_Dark_Mode
 */

declare(strict_types=1);

namespace JTZL\Simple_Dark_Mode;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'SIMPLE_DARK_MODE_VERSION', '1.0.0-beta.1' );
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

/**
 * Plugin activation callback.
 *
 * Clears the DI container cache to ensure fresh compilation.
 *
 * @return void
 */
function activate(): void {
	ContainerFactory::clear_cache();
}

/**
 * Plugin deactivation callback.
 *
 * Clears the DI container cache for cleanup.
 *
 * @return void
 */
function deactivate(): void {
	ContainerFactory::clear_cache();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );
register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivate' );
