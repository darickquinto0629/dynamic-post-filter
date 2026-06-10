<?php
/**
 * Plugin Name: Dynamic Post Filter
 * Plugin URI: https://example.com/dynamic-post-filter
 * Description: Display posts and custom post types with dynamic AJAX taxonomy filtering and pagination
 * Version: 1.0.0
 * Author: Darick L. Quinto
 * Author URI: https://jollity.io
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dynamic-post-filter
 * Domain Path: /languages
 *
 * @package Dynamic_Post_Filter
 */

// Prevent direct access to the plugin file
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'ALL_MENU_FILTER_VERSION', '1.0.0' );
define( 'ALL_MENU_FILTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ALL_MENU_FILTER_URL', plugin_dir_url( __FILE__ ) );

// Include the shortcode file
require_once ALL_MENU_FILTER_PATH . 'includes/shortcode.php';

// Hook to enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'all_menu_filter_enqueue_scripts' );

/**
 * Enqueue plugin scripts and styles
 */
function all_menu_filter_enqueue_scripts() {
	// Only enqueue on pages that use the shortcode
	if ( ! is_admin() ) {
		wp_enqueue_script( 'jquery' );
	}
}

// Activation hook
register_activation_hook( __FILE__, 'all_menu_filter_activation' );

/**
 * Plugin activation
 */
function all_menu_filter_activation() {
	// Placeholder for activation tasks
	flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook( __FILE__, 'all_menu_filter_deactivation' );

/**
 * Plugin deactivation
 */
function all_menu_filter_deactivation() {
	// Placeholder for deactivation tasks
	flush_rewrite_rules();
}
?>