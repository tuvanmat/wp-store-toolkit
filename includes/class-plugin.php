<?php
/**
 * Main plugin class.
 *
 * @package WPStoreToolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Coordinates the plugin: loads translations, boots the module
 * manager and, in the admin, the settings page.
 */
final class WPST_Plugin {

	/**
	 * The single instance of this class.
	 *
	 * @var WPST_Plugin|null
	 */
	private static $instance = null;

	/**
	 * The module manager.
	 *
	 * @var WPST_Module_Manager|null
	 */
	private $modules = null;

	/**
	 * Returns the single instance, creating it on first use.
	 *
	 * @return WPST_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor — use instance() instead.
	 */
	private function __construct() {}

	/**
	 * Hooks the plugin into WordPress.
	 */
	public function run() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'boot_modules' ) );

		if ( is_admin() ) {
			$settings = new WPST_Settings();
			$settings->register_hooks();
		}
	}

	/**
	 * Loads the plugin translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'wp-store-toolkit',
			false,
			dirname( plugin_basename( WPST_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Creates the module manager and initialises every enabled module.
	 */
	public function boot_modules() {
		$this->modules = new WPST_Module_Manager();
		$this->modules->load_enabled_modules();
	}

	/**
	 * Returns the module manager, once modules have booted.
	 *
	 * @return WPST_Module_Manager|null
	 */
	public function modules() {
		return $this->modules;
	}

	/**
	 * Reads one value from the plugin settings.
	 *
	 * @param string $key     Setting key: 'enabled_modules' or 'module_settings'.
	 * @param mixed  $default Value returned when the key is missing.
	 * @return mixed
	 */
	public function get_setting( $key, $default = null ) {
		$settings = get_option( 'wpst_settings', array() );

		if ( is_array( $settings ) && array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		return $default;
	}
}
