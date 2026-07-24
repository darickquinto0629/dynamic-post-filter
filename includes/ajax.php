<?php
/**
 * AJAX Handler
 *
 * Handles AJAX requests for post filtering, pagination, and dynamic content updates.
 *
 * @package Dynamic_Post_Filter
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Hook into both authenticated and non-authenticated AJAX actions
// Allows both logged-in users and guests to filter posts
add_action( 'wp_ajax_all_menu_filter', 'all_menu_filter_ajax' );
add_action( 'wp_ajax_nopriv_all_menu_filter', 'all_menu_filter_ajax' );

/**
 * AJAX Handler for Post Filtering & Pagination
 * 
 * PRIMARY RESPONSIBILITY:
 * Receive filter/pagination parameters from JavaScript, execute filtered WP_Query,
 * render posts and modals, and return complete HTML via JSON response.
 * 
 * SECURITY:
 *  - Nonce verification: Checks wp_verify_nonce() before processing
 *  - Input sanitization: All POST data sanitized with sanitize_text_field() or intval()
 *  - Output escaping: All HTML output escaped with esc_attr(), esc_url(), esc_html()
 *  - Post status: Only queries published posts (post_status = 'publish')
 * 
 * REQUEST PARAMETERS (POST):
 *  - nonce (string): Security token for CSRF protection
 *  - post_type (string): Post type to query
 *  - taxonomy (string): Taxonomy slug for filtering
 *  - term (string): Term slug to filter by (empty for "All Items")
 *  - paged (int): Current page number (1-based)
 *  - orderby (string): Order by field (date, title, etc)
 *  - order (string): Order direction (ASC, DESC)
 *  - posts_per_page (int): Posts per page limit
 *  - unique_id (string): Container ID for reference (used in selector attributes)
 * 
 * RESPONSE FORMAT (JSON):
 *  {
 *    "success": true/false,
 *    "data": {
 *      "posts": "<ul>post items HTML</ul>",
 *      "modals": "<div>lity modals</div>",
 *      "pagination": "<div>pagination buttons</div>"
 *    }
 *  }
 * 
 * WORKFLOW:
 *  1. Verify nonce for security
 *  2. Sanitize and extract POST parameters
 *  3. Build WP_Query arguments with taxonomy tax_query if needed
 *  4. Execute WP_Query
 *  5. Loop through posts: collect posts HTML and modals HTML separately
 *  6. Generate pagination HTML for next/prev/page numbers
 *  7. Return JSON response with all three components
 *  8. JavaScript receives and updates DOM with new content
 *  9. Lity automatically detects new modals via event delegation
 * 
 * IMPORTANT NOTES:
 *  - Posts and modals collected in separate variables (for valid HTML structure)
 *  - Empty term parameter: Queries ALL posts without tax_query filter
 *  - Modal ID format: "post-modal-{post_id}" (matches button data-modal-id)
 *  - Excerpt fallback: Uses get_the_excerpt() with fallback to get_the_content()
 *  - Word trimming: Modal excerpt limited to 20 words for readability
 * 
 * @return void Sends JSON response and exits
 */
function all_menu_filter_ajax() {
	// Verify security nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'all_menu_nonce' ) ) {
		wp_send_json_error( 'Nonce verification failed' );
	}

	// Extract and sanitize POST parameters with defaults
	$post_type      = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : 'post';
	$taxonomy       = isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : '';
	$term           = isset( $_POST['term'] ) ? sanitize_text_field( $_POST['term'] ) : '';
	$orderby        = isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'date';
	$order          = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'DESC';
	$posts_per_page = isset( $_POST['posts_per_page'] ) ? intval( $_POST['posts_per_page'] ) : 10;
	$paged          = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;

	// Build query arguments for WP_Query
	$query_args = all_menu_build_query_args(
		$post_type,
		$posts_per_page,
		$paged,
		$orderby,
		$order,
		$taxonomy,
		$term
	);

	// Execute query
	$query = new WP_Query( $query_args );

	$posts_output = '';
	$modals_output = '';

	// ========================================
	// POST ITEM TEMPLATE - CUSTOMIZE THIS SECTION
	// Developers: Modify the HTML structure below to match your design requirements.
	// All post data (title, excerpt, custom fields, etc.) can be accessed within this loop.
	//
	// ⚠️  ACF FIELDS USED IN THIS PROJECT:
	// This template uses the ACF field 'starting_at' for pricing display.
	// Your project may use different field names or custom fields.
	// Update the get_field() calls below with your actual field names/keys.
	// ========================================

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();
			$featured_image_url = all_menu_get_featured_image( $post_id );
			$starting_at = all_menu_get_starting_price( $post_id );
			$modal_id = all_menu_get_modal_id( $post_id );
			
			$posts_output .= all_menu_render_post( $post_id, $featured_image_url, $modal_id, $starting_at );
			$modals_output .= all_menu_render_modal( $post_id, $featured_image_url, $modal_id );
		}
	} else {
		$posts_output .= '<li><p>No posts found.</p></li>';
	}

	wp_reset_postdata();

	// Generate pagination HTML
	$pagination_html = '';
	if ( intval( $posts_per_page ) > 0 ) {
		$pagination_html = all_menu_get_pagination( $query, array(
			'post_type'      => $post_type,
			'posts_per_page' => $posts_per_page,
			'orderby'        => $orderby,
			'order'          => $order,
			'taxonomy'       => $taxonomy,
			'term'           => $term,
		), isset( $_POST['unique_id'] ) ? sanitize_text_field( $_POST['unique_id'] ) : '' );
	}

	wp_send_json_success( array(
		'posts'      => $posts_output,
		'modals'     => $modals_output,
		'pagination' => $pagination_html,
	) );
}
