<?php
/**
 * Dynamic Post Filter Shortcode
 *
 * @package Dynamic_Post_Filter
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'all-menu', 'all_menu_callback' );

/**
 * Get featured image URL with fallback
 *
 * @param int $post_id Post ID
 * @return string Featured image URL or fallback
 */
function all_menu_get_featured_image( $post_id ) {
	$featured_image_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
	$fallback_image_url = '/wp-content/uploads/2026/06/beef-brisket.jpg';
	return $featured_image_url ? $featured_image_url : $fallback_image_url;
}

/**
 * Get post excerpt with fallback to content
 *
 * @param int $post_id Post ID
 * @return string Post excerpt or content
 */
function all_menu_get_excerpt( $post_id ) {
	return ! empty( get_the_excerpt( $post_id ) ) ? get_the_excerpt( $post_id ) : get_the_content( null, false, $post_id );
}

/**
 * Generate modal ID for a post
 *
 * @param int $post_id Post ID
 * @return string Modal ID (post-modal-{post_id})
 */
function all_menu_get_modal_id( $post_id ) {
	return 'post-modal-' . $post_id;
}

/**
 * Get starting price field for a post
 *
 * @param int $post_id Post ID
 * @return string Starting price value or empty
 */
function all_menu_get_starting_price( $post_id ) {
	return get_field( 'starting_at', $post_id );
}

/**
 * Sort terms by custom menu order
 *
 * @param array $terms Array of term objects
 * @return array Sorted terms
 */
function all_menu_sort_terms( $terms ) {
	$custom_order = array(
		'bbq',
		'sandwiches',
		'sides',
		'desserts',
		'drinks',
	);

	usort( $terms, function( $a, $b ) use ( $custom_order ) {
		$a_pos = array_search( $a->slug, $custom_order );
		$b_pos = array_search( $b->slug, $custom_order );

		// Put terms not found in the array at the end
		if ( $a_pos === false ) {
			$a_pos = 999;
		}

		if ( $b_pos === false ) {
			$b_pos = 999;
		}

		return $a_pos - $b_pos;
	} );

	return $terms;
}

/**
 * Render a single post item HTML
 *
 * @param int $post_id Post ID
 * @param string $featured_image_url Featured image URL
 * @param string $modal_id Modal identifier
 * @param string $starting_at Starting price field value
 * @return string Post item HTML
 */
function all_menu_render_post( $post_id, $featured_image_url, $modal_id, $starting_at ) {
	$excerpt = all_menu_get_excerpt( $post_id );
	
	$html = '<li class="post-item">';
	$html .= '<div class="featured-image-wrapper"><div class="menu-featured-image" data-modal-id="' . esc_attr( $modal_id ) . '" style="background-image: url(' . esc_url( $featured_image_url ) . ')"></div></div>';
	$html .= '<div class="featured-content">';
	$html .= '<h4 class="ddc-font">' . get_the_title() . '</h4>';
	$html .= '<p class="excerpt">' . wp_trim_words( $excerpt, 7, '...' ) . '</p>';
	$html .= '<div class="dashed-line"></div>';
	$html .= '<div class="fc-bottom">';
	
	if ( $starting_at ) {
		$html .= '<div><p class="ddc-font">Starting at <span class="sp-font color-rust">$' . esc_html( $starting_at ) . '</span></p></div>';
	}
	
	$html .= '<a class="plus-icon" href="javascript:void(0)" data-modal-id="' . esc_attr( $modal_id ) . '"><img src="/wp-content/uploads/2026/06/Add-Circle-Alternate-Streamline-Ultimate.svg" /></a>';
	$html .= '</div>';
	$html .= '</div>';
	$html .= '</li>';
	
	return $html;
}

/**
 * Render price section with repeater loop and order-now button
 *
 * @param int $post_id Post ID
 * @return string Price section HTML
 */
function all_menu_render_price_section( $post_id ) {
	$html = '';
	
	// Check rows exists.
	if( have_rows('prices', $post_id) ):
		$html .= '<div class="price-item">';
		$html .= '<div>';
		
		// Loop through rows.
		while( have_rows('prices', $post_id) ) : the_row();
			// Load sub field value.
			$details = get_sub_field('details');
			$price = get_sub_field('price');
			
			$price_display = $price ? '<span class="sp-font color-rust">$'. esc_html( $price ). '</span>' : '';
			$html .= '<p class="ddc-font">' .  $details . $price_display . '</p>';
		endwhile;
		
		$html .= '</div>';
		$html .= '<div><a href="https://gquebbq.orderexperience.net/locations?_gl=1%2au4jpjn%2a_ga%2aNjIxNTM1MDc2LjE3NzE4Njk5NTA.%2a_ga_TLXQSNCW3N%2aczE3ODA5MzgwMTQkbzI2JGcxJHQxNzgwOTM4NzMwJGoyNyRsMCRoMA.." id="order-now" target="_blank">Order Now</a></div>';
		$html .= '</div>';
	else :
		$html .= '<div class="price-item no-details"><div><a href="https://gquebbq.orderexperience.net/locations?_gl=1%2au4jpjn%2a_ga%2aNjIxNTM1MDc2LjE3NzE4Njk5NTA.%2a_ga_TLXQSNCW3N%2aczE3ODA5MzgwMTQkbzI2JGcxJHQxNzgwOTM4NzMwJGoyNyRsMCRoMA.." id="order-now" target="_blank">Order Now</a></div></div>';
	endif;
	
	return $html;
}

/**
 * Render modal HTML for a post
 *
 * @param int $post_id Post ID
 * @param string $featured_image_url Featured image URL
 * @param string $modal_id Modal identifier
 * @return string Modal HTML
 */
function all_menu_render_modal( $post_id, $featured_image_url, $modal_id ) {
	$html = '<div id="' . esc_attr( $modal_id ) . '" class="lity-modal lity-hide">';
	$html .= '<button class="lity-close" data-lity-close><img src="/wp-content/uploads/2026/06/close-button.svg" /></button>';
	$html .= '<div class="menu-featured-image" style="background-image: url(' . esc_url( $featured_image_url ) . ')"></div>';
	$html .= '<div class="lity-modal-content">';
	$html .= '<h3 class="ddc-font">' . get_the_title( $post_id ) . '</h3>';
	$html .= '<p class="modal-p">' . get_the_content( null, false, $post_id ) . '</p>';
	$html .= '<div class="dashed-line"></div>';
	$html .= all_menu_render_price_section( $post_id );
	$html .= '</div>';
	$html .= '</div>';
	
	return $html;
}

/**
 * Shortcode callback for [all-menu]
 * 
 * PRIMARY RESPONSIBILITY:
 * Renders filterable post grid with Lity 2.4.1 lightbox modals and pagination.
 * Handles both server-side rendering on page load and client-side updates via AJAX.
 * 
 * KEY FEATURES:
 * - Filter buttons: Dynamic taxonomy-based filtering with "All Items" option
 * - URL state persistence: Filter selections and pagination preserved in URL
 * - Browser history support: Back/forward navigation works correctly
 * - Lity 2.4.1 modals: Click plus-icon to open lightbox with post details
 * - Excerpt fallback: Uses excerpt or post content if excerpt unavailable
 * - Multi-instance support: Multiple shortcodes can be used on same page
 * 
 * SHORTCODE ATTRIBUTES:
 *  - post_type (string, default: 'post'): Post type to display
 *  - posts_per_page (int, default: 12): Posts per page for pagination
 *  - orderby (string, default: 'date'): Order by field
 *  - order (string, default: 'DESC'): Order direction (ASC/DESC)
 *  - taxonomy (string, default: ''): Taxonomy slug for filter buttons
 *  - term (string, default: ''): Initial term to filter by
 * 
 * USAGE EXAMPLES:
 *  [all-menu post_type="menu" posts_per_page="12" taxonomy="menu-categories"]
 *  [all-menu post_type="post" posts_per_page="9" taxonomy="categories" orderby="title" order="ASC"]
 * 
 * LIGHTBOX INTEGRATION (Lity 2.4.1):
 *  - Library loaded via CDN (2kB gzipped) only on pages with [all-menu] shortcode
 *  - Automatic event delegation for dynamically added elements via AJAX
 *  - Modal ID format: "post-modal-{post_id}" for unique, consistent referencing
 *  - Modal visibility: Uses class="lity-hide" for proper initialization (NOT CSS display:none)
 *  - Scroll preservation: JavaScript captures and restores scroll position on modal close
 *  - Content rendering: Post title + excerpt (up to 20 words) displayed in modal
 * 
 * AJAX WORKFLOW:
 *  1. User clicks filter button or pagination button
 *  2. JavaScript collects filter state and sends AJAX POST request
 *  3. AJAX handler (all_menu_filter_ajax) validates nonce and executes WP_Query
 *  4. Response includes: posts HTML + modals HTML + pagination HTML
 *  5. JavaScript replaces container content with new results
 *  6. Lity automatically recognizes new modals via event delegation (no re-init needed)
 * 
 * TECHNICAL ARCHITECTURE:
 *  - Modals: Rendered in separate variable, inserted AFTER posts wrapper (valid XHTML)
 *  - Filter buttons: Generated from get_terms() for specified taxonomy
 *  - "All Items" button: Empty term shows all posts without taxonomy filtering
 *  - Nonce security: all_menu_nonce used for AJAX verification (prevents CSRF)
 *  - Authentication: Supports both authenticated users (wp_ajax_) and guests (wp_ajax_nopriv_)
 *  - Unique IDs: Each shortcode instance gets unique ID for multi-instance support
 *  - Hidden content: If URL has filter params, initial page content hidden via CSS until AJAX completes
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output: filter buttons + posts grid + modals + pagination
 */
function all_menu_callback( $atts = array() ) {
	// Parse shortcode attributes with defaults
	$atts = shortcode_atts(
		array(
			'post_type'      => 'post',      // Default post type
			'posts_per_page' => 8,          // Posts per page for pagination
			'orderby'        => 'date',
			'order'          => 'ASC',
			'taxonomy'       => '',          // Custom taxonomy
			'term'           => '',          // Term to filter by
		),
		$atts,
		'all-menu'
	);

	// Generate unique ID for this shortcode instance
	$unique_id = 'all-menu-' . uniqid();

	// Enqueue scripts
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script(
		'all-menu-filter',
		ALL_MENU_FILTER_URL . 'js/all-menu-filter.js',
		array( 'jquery' ),
		ALL_MENU_FILTER_VERSION,
		true
	);

	// Enqueue styles
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
		)
	);

	// Get all terms for the taxonomy if specified
$terms = array();

if ( ! empty( $atts['taxonomy'] ) ) {

    $terms = get_terms(
        array(
            'taxonomy'   => sanitize_text_field( $atts['taxonomy'] ),
            'hide_empty' => true,
        )
    );

    // Sort terms by custom menu order
    $terms = all_menu_sort_terms( $terms );
}

	// Build WP_Query arguments
	$query_args = array(
		'post_type'      => sanitize_text_field( $atts['post_type'] ),
		'posts_per_page' => intval( $atts['posts_per_page'] ),
		'paged'          => 1,
		'orderby'        => sanitize_text_field( $atts['orderby'] ),
		'order'          => sanitize_text_field( $atts['order'] ),
		'post_status'    => 'publish',
	);

	// Add taxonomy filter if specified
	if ( ! empty( $atts['taxonomy'] ) && ! empty( $atts['term'] ) ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => sanitize_text_field( $atts['taxonomy'] ),
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['term'] ),
			),
		);
	}

	// Execute the query
	$query = new WP_Query( $query_args );

	$output = '';

	// Display taxonomy filter buttons if taxonomy is set
	if ( ! empty( $atts['taxonomy'] ) && ! is_wp_error( $terms ) && count( $terms ) > 0 ) {
		$output .= '<div id="menu-filter-container" class="all-menu-filters">';
		// Only mark "All Items" as active if no term is pre-selected
		$active_class_all = ( empty( $atts['term'] ) ) ? 'active' : '';
		$output .= '<button class="all-menu-filter-btn ' . esc_attr( $active_class_all ) . '" data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-term="" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-unique-id="' . esc_attr( $unique_id ) . '">All Items</button>';

		foreach ( $terms as $term ) {
			$active_class = ( ! empty( $atts['term'] ) && $atts['term'] === $term->slug ) ? 'active' : '';
			$output .= '<button class="all-menu-filter-btn ' . esc_attr( $active_class ) . '" data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-term="' . esc_attr( $term->slug ) . '" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-unique-id="' . esc_attr( $unique_id ) . '">' . esc_html( $term->name ) . '</button>';
		}

        $output .= '<a class="all-menu-filter-btn catering-btn" href="https://gquebbq.com/catering-bbq/" target="_blank">Catering</a>';

		$output .= '</div>';
	}
	
	// Mobile Menu
    $output .= '<select class="menu-filter-dropdown" data-unique-id="' . esc_attr( $unique_id ) . '">';
    
    $output .= '<option value="">All Items</option>';
    
    foreach ( $terms as $term ) {
        $selected = ( ! empty( $atts['term'] ) && $atts['term'] === $term->slug ) ? 'selected' : '';
    
        $output .= '<option
            value="' . esc_attr( $term->slug ) . '"
            ' . $selected . '
            data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '"
            data-post-type="' . esc_attr( $atts['post_type'] ) . '"
            data-orderby="' . esc_attr( $atts['orderby'] ) . '"
            data-order="' . esc_attr( $atts['order'] ) . '"
            data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '"
        >' . esc_html( $term->name ) . '</option>';
    }
    
    // Catering link option
    $output .= '<option value="catering">Catering</option>';
    
    $output .= '</select>';

	// Posts wrapper with loading indicator
	$output .= '<div class="all-menu-posts-wrapper">';
	$output .= '<div class="all-menu-loader" data-loader-id="' . esc_attr( $unique_id ) . '">';
	$output .= '<div class="all-menu-spinner"></div>';
	$output .= '</div>';

	// Check if URL has filter params to determine if we should hide initial content
	$has_url_params = ! empty( $_GET['amf_post_type'] ) && ! empty( $_GET['amf_taxonomy'] ) ? true : false;
	$hidden_class = $has_url_params ? ' amf-hidden-initial' : '';

	// Posts container
	$output .= '<ul id="' . esc_attr( $unique_id ) . '" class="custom-post-loop' . esc_attr( $hidden_class ) . '">';

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

	// Collect modals separately to place them outside the <ul>
	$modals_output = '';

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();
			$featured_image_url = all_menu_get_featured_image( $post_id );
			$starting_at = all_menu_get_starting_price( $post_id );
			$modal_id = all_menu_get_modal_id( $post_id );
			
			$output .= all_menu_render_post( $post_id, $featured_image_url, $modal_id, $starting_at );
			$modals_output .= all_menu_render_modal( $post_id, $featured_image_url, $modal_id );
		}
	} else {
		$output .= '<li><p>No posts found.</p></li>';
	}

	$output .= '</ul>';
	$output .= '</div>'; // Close posts wrapper
	$output .= $modals_output; // Add modals AFTER the wrapper

	// Add pagination if pagination is enabled
	if ( intval( $atts['posts_per_page'] ) > 0 ) {
		$pagination_html = all_menu_get_pagination( $query, $atts, $unique_id );
		$output .= $pagination_html;
	}

	// Reset post data
	wp_reset_postdata();

	return $output;
}

// ========================================
// AJAX HANDLER & HOOK REGISTRATION
// ========================================
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
	$query_args = array(
		'post_type'      => $post_type,
		'posts_per_page' => $posts_per_page,
		'paged'          => $paged,
		'orderby'        => $orderby,
		'order'          => $order,
		'post_status'    => 'publish',
	);

	// Add taxonomy filter if both taxonomy and term are specified
	if ( ! empty( $taxonomy ) && ! empty( $term ) ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $term,
			),
		);
	}

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

/**
 * Generate pagination HTML with state preservation
 * 
 * Creates previous/page number/next buttons with data attributes
 * for URL state management. Each button includes all filter parameters
 * needed to reconstruct the query.
 * 
 * @param WP_Query $query The query object with pagination data
 * @param array    $atts  The shortcode attributes (post_type, taxonomy, term, etc)
 * @param string   $unique_id The unique ID for the container
 * @return string Pagination HTML, or empty string if only 1 page
 */
function all_menu_get_pagination( $query, $atts, $unique_id ) {
	if ( $query->max_num_pages <= 1 ) {
		return '';
	}

	$pagination_html = '<div class="all-menu-pagination" data-unique-id="' . esc_attr( $unique_id ) . '">';

	// Previous page link
	if ( $query->query_vars['paged'] > 1 ) {
		$prev_page = $query->query_vars['paged'] - 1;
		$pagination_html .= '<button class="all-menu-page-btn" data-page="' . intval( $prev_page ) . '" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-term="' . esc_attr( $atts['term'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-unique-id="' . esc_attr( $unique_id ) . '">Prev</button>';
	}

	// Page number links with ellipsis for large pagination
	$current_page = $query->query_vars['paged'];
	$total_pages = $query->max_num_pages;
	$range = 2; // Number of pages to show on each side of current page
	$last_shown = 0;

	for ( $i = 1; $i <= $total_pages; $i++ ) {
		// Determine which pages to show
		$show_page = false;

		// Show all pages if total pages is less than 5
		if ( $total_pages < 5 ) {
			$show_page = true;
		}
		// For 5+ pages, apply ellipsis logic
		else {
			// Always show first page
			if ( $i === 1 ) {
				$show_page = true;
			}
			// Always show last page
			elseif ( $i === $total_pages ) {
				$show_page = true;
			}
			// Show pages around current page
			elseif ( $i >= $current_page - $range && $i <= $current_page + $range ) {
				$show_page = true;
			}
		}

		if ( $show_page ) {
			// Add ellipsis if there's a gap
			if ( $last_shown > 0 && $i - $last_shown > 1 ) {
				$pagination_html .= '<span class="all-menu-page-ellipsis">...</span>';
			}

			if ( $i === $current_page ) {
				$pagination_html .= '<span class="all-menu-page-num active">' . intval( $i ) . '</span>';
			} else {
				$pagination_html .= '<button class="all-menu-page-btn" data-page="' . intval( $i ) . '" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-term="' . esc_attr( $atts['term'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-unique-id="' . esc_attr( $unique_id ) . '">' . intval( $i ) . '</button>';
			}

			$last_shown = $i;
		}
	}

	// Next page link
	if ( $query->query_vars['paged'] < $query->max_num_pages ) {
		$next_page = $query->query_vars['paged'] + 1;
		$pagination_html .= '<button class="all-menu-page-btn" data-page="' . intval( $next_page ) . '" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-term="' . esc_attr( $atts['term'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-unique-id="' . esc_attr( $unique_id ) . '">Next</button>';
	}

	$pagination_html .= '</div>';
	return $pagination_html;
}


// Ignore this. this is not the part of the plugin. this is the featured menu on my GQBBQ site. 
// Register the 'featured' shortcode and attach it to the callback function
add_shortcode('featured', 'featured_callback');
function featured_callback() {
    
    wp_enqueue_style(
        'slick-css',
        'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css'
    );

    wp_enqueue_style(
        'slick-theme-css',
        'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css'
    );

    wp_enqueue_script(
        'slick-js',
        'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
        array('jquery'),
        '1.8.1',
        true
    );   
    
    // Initialize global variable to store featured post IDs
    global $featured_post_ids;
    $featured_post_ids = [];
    
    // Retrieve the ACF field 'weekly_featured' which contains an array of post objects
    $featured_posts = get_field('weekly_featured');
if( $featured_posts ): 
    // Count total posts to determine responsive column class
    $post_count = count( $featured_posts ); ?>
<ul class="featured-slider">
    <?php 
    $modals_output = '';
    foreach( $featured_posts as $post ): 
        // Add post ID to global variable for use in other functions
        $featured_post_ids[] = $post->ID;
        $featured_image_url = all_menu_get_featured_image( $post->ID );
        
        // Setup this post for WP functions (variable must be named $post).
        setup_postdata($post);
        // Apply responsive column class: featured-col-2 for 1-2 posts, featured-col-3 for 3 posts
        $class = ( $post_count <= 2 ) ? 'featured-col-2' : ( $post_count == 3 ? 'featured-col-3' : '' ); ?>
        <!-- Apply responsive class attribute (escaped for security) -->
        <li class="<?php echo esc_attr( $class ); ?>">
          <!-- Display featured image as background (URL escaped for security) -->
          <div class="featured-image-wrapper"><div class="menu-featured-image" data-modal-id="<?php echo esc_attr( all_menu_get_modal_id( $post->ID ) ); ?>" style="background-image: url(<?php echo esc_url( $featured_image_url ); ?>) "></div></div>
          <div class="featured-content">
              <!-- Output post title -->
              <h4 class="ddc-font"><?php echo $post->post_title; ?></h4>
              <!-- Output post excerpt (first 20 words) -->
              <?php 
              $excerpt = all_menu_get_excerpt( $post->ID );
              echo '<p class="excerpt">' . wp_trim_words( $excerpt, 20, "..." ) . '</p>'; 
              ?>
              <div class="dashed-line"></div>
              <div class="fc-bottom">
                  <?php $starting_price = all_menu_get_starting_price( $post->ID ); ?>
                  <?php if( $starting_price ): ?>
                      <div>
                          <p class="ddc-font">Starting at <span class="sp-font color-rust">$<?php echo esc_html( $starting_price ); ?></span></p>
                      </div>
                  <?php endif; ?>
                  <a class="plus-icon" href="javascript:void(0)" data-modal-id="<?php echo esc_attr( all_menu_get_modal_id( $post->ID ) ); ?>"><img src="/wp-content/uploads/2026/06/Add-Circle-Alternate-Streamline-Ultimate.svg" /></a>
              </div>
              <?php 
              // Generate modal for this post
              $modal_id = all_menu_get_modal_id( $post->ID );
				$modals_output .= '<div id="' . esc_attr( $modal_id ) . '" class="lity-modal lity-hide">';
				$modals_output .= '<button class="lity-close" data-lity-close><img src="/wp-content/uploads/2026/06/close-button.svg" /></button>';
				$modals_output .= '<div class="menu-featured-image" style="background-image: url(' . esc_url( $featured_image_url ) . ')"></div>';		
				$modals_output .= '<div class="lity-modal-content">';
				$modals_output .= '<h3 class="ddc-font">' . get_the_title( $post->ID ) . '</h3>';
				$modals_output .= '<p class="modal-p">' . get_the_content( null, false, $post->ID ) . '</p>';
				$modals_output .= '<div class="dashed-line"></div>';
				
				// Check rows exists.
				if( have_rows('prices', $post->ID) ):
					$modals_output .= '<div class="price-item">';

						$modals_output .= '<div>';
							// Loop through rows.
							while( have_rows('prices', $post->ID) ) : the_row();

								// Load sub field value.
								$details = get_sub_field('details');
								$price = get_sub_field('price');
								
							$price_display = $price ? '<span class="sp-font color-rust">$'. esc_html( $price ). '</span>' : '';
							$modals_output .= '<p class="ddc-font">' . $details . $price_display . '</p>';

							// End loop.
							endwhile;
						$modals_output .= '</div>';

						$modals_output .= '<div><a href="https://gquebbq.orderexperience.net/locations?_gl=1%2au4jpjn%2a_ga%2aNjIxNTM1MDc2LjE3NzE4Njk5NTA.%2a_ga_TLXQSNCW3N%2aczE3ODA5MzgwMTQkbzI2JGcxJHQxNzgwOTM4NzMwJGoyNyRsMCRoMA.." id="order-now" target="_blank">Order Now</a></div>';

					$modals_output .= '</div>';
				
				// No value.
				else :
						$modals_output .= '<div class="price-item no-details"><div><a href="https://gquebbq.orderexperience.net/locations?_gl=1%2au4jpjn%2a_ga%2aNjIxNTM1MDc2LjE3NzE4Njk5NTA.%2a_ga_TLXQSNCW3N%2aczE3ODA5MzgwMTQkbzI2JGcxJHQxNzgwOTM4NzMwJGoyNyRsMCRoMA.." id="order-now" target="_blank">Order Now</a></div></div>';
				endif;
				
				$modals_output .= '</div>';
				$modals_output .= '</div>';		  
              ?>
              
              
          </div>
          <?php //print_r($post); ?>
        </li>
    <?php endforeach; ?>
</ul>

    <?php echo $modals_output; ?>
    <?php 
    // Critical: Reset the global post object after using setup_postdata() to ensure the rest of the page works correctly
    wp_reset_postdata(); ?>
<?php 
    else:
        // Fallback message when no featured posts are available
        echo 'no menu found';
    endif; 
?>

<?php    
}

?>