<?php
/**
 * Base class for toolkit modules.
 *
 * @package WPStoreToolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Every feature of the toolkit is a module.
 *
 * A module is completely self-contained: it declares its own id,
 * title and description, registers its own settings fields and only
 * runs when the site owner enables it on the Settings page. Disabled
 * modules are never instantiated, so they cost nothing at runtime.
 */
abstract class WPST_Module {

	/**
	 * Unique module id, e.g. 'read-more-product'.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Human-readable module title, in English (translatable).
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * One-sentence module description, in English (translatable).
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Returns the module id.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Returns the module title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Returns the module description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Whether this module is enabled in the plugin settings.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$enabled = wpst()->get_setting( 'enabled_modules', array() );

		return is_array( $enabled ) && in_array( $this->id, $enabled, true );
	}

	/**
	 * Reads one of this module's own settings.
	 *
	 * @param string $key     Setting key inside the module's settings array.
	 * @param mixed  $default Value returned when the key is missing.
	 * @return mixed
	 */
	protected function get_module_setting( $key, $default = null ) {
		$all = wpst()->get_setting( 'module_settings', array() );

		if ( is_array( $all ) && isset( $all[ $this->id ] ) && is_array( $all[ $this->id ] ) && array_key_exists( $key, $all[ $this->id ] ) ) {
			return $all[ $this->id ][ $key ];
		}

		return $default;
	}

	/**
	 * Registers the module's settings fields on the Settings page.
	 *
	 * Called only in the admin, whether or not the module is enabled,
	 * so its options stay editable. Modules with no settings may leave
	 * the default empty implementation.
	 */
	public function register_settings() {}

	/**
	 * Initialises the module's runtime behaviour.
	 *
	 * Called only when the module is enabled.
	 */
	abstract public function init();
}
