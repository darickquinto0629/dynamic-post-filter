<?php
/**
 * Asset Enqueuing
 *
 * Centralized script and style loading for the shortcode.
 * jQuery, plugin scripts, Lity library, and script localization.
 *
 * @package Dynamic_Post_Filter
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue all scripts and styles for the shortcode
 *
 * Centralizes asset loading for jQuery, plugin scripts/styles,
 * Lity 2.4.1 modal library, and script localization.
 *
 * @return void
 */
function all_menu_enqueue_assets() {
	// Enqueue jQuery as dependency
	wp_enqueue_script( 'jquery' );

	// Enqueue plugin JavaScript
	wp_enqueue_script(
		'all-menu-filter',
		ALL_MENU_FILTER_URL . 'js/all-menu-filter.js',
		array( 'jquery' ),
		ALL_MENU_FILTER_VERSION,
		true
	);

	// Enqueue plugin styles (commented out - using inline styling)
	// wp_enqueue_style(
	// 	'all-menu-filter',
	// 	ALL_MENU_FILTER_URL . 'css/all-menu-filter.css',
	// 	array(),
	// 	ALL_MENU_FILTER_VERSION
	// );

	// Enqueue Lity modal library (only on pages using this shortcode)
	wp_enqueue_script(
		'lity',
		'https://cdn.jsdelivr.net/npm/lity@2.4.1/dist/lity.min.js',
		array(),
		'2.4.1',
		true
	);

	// Enqueue Lity CSS
	wp_enqueue_style(
		'lity',
		'https://cdn.jsdelivr.net/npm/lity@2.4.1/dist/lity.min.css',
		array(),
		'2.4.1'
	);

	// Localize script with AJAX URL and nonce
	wp_localize_script(
		'all-menu-filter',
		'all_menu_data',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'all_menu_nonce' ),
			'enable_pagination' => 'yes', // Default pagination enabled
		)
	);
}
