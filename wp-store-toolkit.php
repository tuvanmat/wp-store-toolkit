<?php
/**
 * Plugin Name:       WP Store Toolkit
 * Plugin URI:        https://github.com/tuvanmat/wp-store-toolkit
 * Description:       A lightweight, modular toolkit for WordPress and WooCommerce stores. Enable only the small utilities you need — nothing else is loaded.
 * Version:           0.1.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Trong Nga
 * Author URI:        https://github.com/tuvanmat
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-store-toolkit
 * Domain Path:       /languages
 *
 * @package WPStoreToolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WPST_VERSION', '0.1.0' );
define( 'WPST_PLUGIN_FILE', __FILE__ );
define( 'WPST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Class autoloader for the plugin.
 *
 * Maps class names such as WPST_Module_Manager to
 * includes/class-module-manager.php and WPST_Settings to
 * includes/admin/class-settings.php.
 *
 * @param string $class_name The class name to load.
 */
function wpst_autoload( $class_name ) {
	if ( 0 !== strpos( $class_name, 'WPST_' ) ) {
		return;
	}

	$slug = strtolower( str_replace( '_', '-', substr( $class_name, 5 ) ) );

	$candidates = array(
		WPST_PLUGIN_DIR . 'includes/class-' . $slug . '.php',
		WPST_PLUGIN_DIR . 'includes/admin/class-' . $slug . '.php',
		WPST_PLUGIN_DIR . 'includes/modules/class-' . $slug . '.php',
	);

	foreach ( $candidates as $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
			return;
		}
	}
}
spl_autoload_register( 'wpst_autoload' );

require_once WPST_PLUGIN_DIR . 'includes/modules.php';

/**
 * Runs on plugin activation.
 *
 * Seeds the default settings so the Settings page always has a
 * complete options array to work with.
 */
function wpst_activate() {
	if ( false === get_option( 'wpst_settings', false ) ) {
		add_option(
			'wpst_settings',
			array(
				'enabled_modules' => array(),
				'module_settings' => array(),
			)
		);
	}
}
register_activation_hook( __FILE__, 'wpst_activate' );

/**
 * Returns the main plugin instance.
 *
 * @return WPST_Plugin
 */
function wpst() {
	return WPST_Plugin::instance();
}

wpst()->run();
