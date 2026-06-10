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
 * Shortcode callback for [all-menu]
 *
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function all_menu_callback( $atts = array() ) {
	// Parse shortcode attributes with defaults
	$atts = shortcode_atts(
		array(
			'post_type'      => 'post',      // Default post type
			'posts_per_page' => 10,          // Posts per page for pagination
			'orderby'        => 'date',
			'order'          => 'DESC',
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

		$output .= '</div>';
	}

	// Posts container
	$output .= '<ul id="' . esc_attr( $unique_id ) . '" class="custom-post-loop">';

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
			$featured_image_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
			$fallback_image_url = '/wp-content/uploads/2026/06/beef-brisket.jpg';
			$featured_image_url = $featured_image_url ? $featured_image_url : $fallback_image_url;
			$starting_at = get_field( 'starting_at', $post_id );
			
			$output .= '<li class="post-item">';
			$output .= '<div class="menu-featured-image" style="background-image: url(' . esc_url( $featured_image_url ) . ')"></div>';
			$output .= '<div class="featured-content">';
			$output .= '<h4 class="ddc-font">' . get_the_title() . '</h4>';
			$output .= '<p class="excerpt">' . wp_trim_words( get_the_content(), 7, '...' ) . '</p>';
			$output .= '<div class="dashed-line"></div>';
			$output .= '<div class="fc-bottom">';
			
			if ( $starting_at ) {
				$output .= '<div><p class="ddc-font">Starting at <span class="sp-font color-rust">$' . esc_html( $starting_at ) . '</span></p></div>';
			}
			
			$output .= '<div class="plus-icon"><img src="/wp-content/uploads/2026/06/Add-Circle-Alternate-Streamline-Ultimate.svg" /></div>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '</li>';
		}
	} else {
		$output .= '<li><p>No posts found.</p></li>';
	}

	$output .= '</ul>';

	// Add pagination if pagination is enabled
	if ( intval( $atts['posts_per_page'] ) > 0 ) {
		$pagination_html = all_menu_get_pagination( $query, $atts, $unique_id );
		$output .= $pagination_html;
	}

	// Reset post data
	wp_reset_postdata();

	return $output;
}

// AJAX handler for filtering posts
add_action( 'wp_ajax_all_menu_filter', 'all_menu_filter_ajax' );
add_action( 'wp_ajax_nopriv_all_menu_filter', 'all_menu_filter_ajax' );

/**
 * AJAX handler for filtering posts
 */
function all_menu_filter_ajax() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'all_menu_nonce' ) ) {
		wp_send_json_error( 'Nonce verification failed' );
	}

	// Get POST data
	$post_type      = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : 'post';
	$taxonomy       = isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : '';
	$term           = isset( $_POST['term'] ) ? sanitize_text_field( $_POST['term'] ) : '';
	$orderby        = isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'date';
	$order          = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'DESC';
	$posts_per_page = isset( $_POST['posts_per_page'] ) ? intval( $_POST['posts_per_page'] ) : 10;
	$paged          = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;

	// Build query arguments
	$query_args = array(
		'post_type'      => $post_type,
		'posts_per_page' => $posts_per_page,
		'paged'          => $paged,
		'orderby'        => $orderby,
		'order'          => $order,
		'post_status'    => 'publish',
	);

	// Add taxonomy filter if specified
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

	$output = '';

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
			$featured_image_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
			$fallback_image_url = '/wp-content/uploads/2026/06/beef-brisket.jpg';
			$featured_image_url = $featured_image_url ? $featured_image_url : $fallback_image_url;
			$starting_at = get_field( 'starting_at', $post_id );
			
			$output .= '<li class="post-item">';
			$output .= '<div class="menu-featured-image" style="background-image: url(' . esc_url( $featured_image_url ) . ')"></div>';
			$output .= '<div class="featured-content">';
			$output .= '<h4 class="ddc-font">' . get_the_title() . '</h4>';
			$output .= '<p class="excerpt">' . wp_trim_words( get_the_content(), 7, '...' ) . '</p>';
			$output .= '<div class="dashed-line"></div>';
			$output .= '<div class="fc-bottom">';
			
			if ( $starting_at ) {
				$output .= '<div><p class="ddc-font">Starting at <span class="sp-font color-rust">$' . esc_html( $starting_at ) . '</span></p></div>';
			}
			
			$output .= '<div class="plus-icon"><img src="/wp-content/uploads/2026/06/Add-Circle-Alternate-Streamline-Ultimate.svg" /></div>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '</li>';
		}
	} else {
		$output .= '<li><p>No posts found.</p></li>';
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
		'posts'      => $output,
		'pagination' => $pagination_html,
	) );
}

/**
 * Generate pagination HTML
 *
 * @param WP_Query $query The query object.
 * @param array    $atts  The shortcode attributes.
 * @param string   $unique_id The unique ID for the container.
 * @return string Pagination HTML
 */
function all_menu_get_pagination( $query, $atts, $unique_id ) {
	if ( $query->max_num_pages <= 1 ) {
		return '';
	}

	$pagination_html = '<div class="all-menu-pagination">';

	// Previous page link
	if ( $query->query_vars['paged'] > 1 ) {
		$prev_page = $query->query_vars['paged'] - 1;
		$pagination_html .= '<button class="all-menu-page-btn" data-page="' . intval( $prev_page ) . '" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-term="' . esc_attr( $atts['term'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-unique-id="' . esc_attr( $unique_id ) . '">← Previous</button>';
	}

	// Page number links
	for ( $i = 1; $i <= $query->max_num_pages; $i++ ) {
		if ( $i === $query->query_vars['paged'] ) {
			$pagination_html .= '<span class="all-menu-page-num active">' . intval( $i ) . '</span>';
		} else {
			$pagination_html .= '<button class="all-menu-page-btn" data-page="' . intval( $i ) . '" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-term="' . esc_attr( $atts['term'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-unique-id="' . esc_attr( $unique_id ) . '">' . intval( $i ) . '</button>';
		}
	}

	// Next page link
	if ( $query->query_vars['paged'] < $query->max_num_pages ) {
		$next_page = $query->query_vars['paged'] + 1;
		$pagination_html .= '<button class="all-menu-page-btn" data-page="' . intval( $next_page ) . '" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-term="' . esc_attr( $atts['term'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-unique-id="' . esc_attr( $unique_id ) . '">Next →</button>';
	}

	$pagination_html .= '</div>';
	return $pagination_html;
}
?>