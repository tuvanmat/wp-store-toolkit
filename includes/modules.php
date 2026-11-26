<?php
/**
 * Module registry.
 *
 * This file is the single place that lists the modules shipping with
 * the toolkit. Each module is added here when it lands, one at a
 * time, following docs/ROADMAP.md.
 *
 * @package WPStoreToolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the toolkit's built-in modules.
 *
 * @param WPST_Module[] $modules Module instances registered so far.
 * @return WPST_Module[]
 */
function wpst_register_builtin_modules( $modules ) {
	$modules[] = new WPST_Read_More();

	// Modules are appended here as they are ported into the toolkit.
	return $modules;
}
add_filter( 'wpst_register_modules', 'wpst_register_builtin_modules' );
