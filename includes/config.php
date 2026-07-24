<?php
/**
 * Configuration Helpers
 *
 * Centralized configuration values for URLs, icons, and settings.
 * All configuration is accessible via helper functions for easy maintenance.
 *
 * @package Dynamic_Post_Filter
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Order Now URL for GQ BBQ
 *
 * @return string Order experience URL with tracking parameters
 */
function all_menu_get_order_url() {
	return 'https://gquebbq.orderexperience.net/locations?_gl=1%2au4jpjn%2a_ga%2aNjIxNTM1MDc2LjE3NzE4Njk5NTA.%2a_ga_TLXQSNCW3N%2aczE3ODA5MzgwMTQkbzI2JGcxJHQxNzgwOTM4NzMwJGoyNyRsMCRoMA..';
}

/**
 * Get fallback featured image URL
 *
 * @return string Fallback image URL for missing post thumbnails
 */
function all_menu_get_fallback_image() {
	return '/wp-content/uploads/2026/06/beef-brisket.jpg';
}

/**
 * Get add/plus icon SVG path
 *
 * @return string Plus icon SVG URL for modal trigger
 */
function all_menu_get_add_icon() {
	return '/wp-content/uploads/2026/06/Add-Circle-Alternate-Streamline-Ultimate.svg';
}

/**
 * Get close button icon SVG path
 *
 * @return string Close button SVG URL for modal close
 */
function all_menu_get_close_icon() {
	return '/wp-content/uploads/2026/06/close-button.svg';
}

/**
 * Get catering page URL
 *
 * @return string Catering page link
 */
function all_menu_get_catering_url() {
	return 'https://gquebbq.com/catering-bbq/';
}

/**
 * Get custom taxonomy sort order
 *
 * @return array Ordered array of taxonomy term slugs
 */
function all_menu_get_custom_taxonomy_order() {
	return array(
		'bbq',
		'sandwiches',
		'sides',
		'desserts',
		'drinks',
	);
}
