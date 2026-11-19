<?php
/**
 * Module manager.
 *
 * @package WPStoreToolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Keeps the registry of available modules and boots the enabled ones.
 *
 * Modules register themselves through the 'wpst_register_modules'
 * filter, which keeps the core free of hard-coded module lists: the
 * plugin bootstrap (includes/modules.php) is the only place that
 * knows which modules ship with the toolkit.
 */
class WPST_Module_Manager {

	/**
	 * Registered module instances, keyed by module id.
	 *
	 * @var WPST_Module[]
	 */
	private $modules = array();

	/**
	 * Collects module instances from the registration filter.
	 */
	public function __construct() {
		/**
		 * Filters the list of available toolkit modules.
		 *
		 * @param WPST_Module[] $modules Module instances, in display order.
		 */
		$registered = apply_filters( 'wpst_register_modules', array() );

		if ( is_array( $registered ) ) {
			foreach ( $registered as $module ) {
				if ( $module instanceof WPST_Module && '' !== $module->get_id() ) {
					$this->modules[ $module->get_id() ] = $module;
				}
			}
		}
	}

	/**
	 * Returns every registered module, keyed by module id.
	 *
	 * @return WPST_Module[]
	 */
	public function get_modules() {
		return $this->modules;
	}

	/**
	 * Returns one registered module by id, or null when unknown.
	 *
	 * @param string $id Module id.
	 * @return WPST_Module|null
	 */
	public function get_module( $id ) {
		return isset( $this->modules[ $id ] ) ? $this->modules[ $id ] : null;
	}

	/**
	 * Initialises each module the site owner has enabled.
	 */
	public function load_enabled_modules() {
		foreach ( $this->modules as $module ) {
			if ( $module->is_enabled() ) {
				$module->init();
			}
		}
	}
}
