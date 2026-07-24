<?php
/**
 * Rendering Helpers
 *
 * HTML rendering functions for posts, modals, filters, dropdowns, and UI elements.
 * All functions return HTML strings without echoing.
 *
 * @package Dynamic_Post_Filter
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
	
	$html .= '<a class="plus-icon" href="javascript:void(0)" data-modal-id="' . esc_attr( $modal_id ) . '"><img src="' . esc_url( all_menu_get_add_icon() ) . '" /></a>';
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
	$order_url = all_menu_get_order_url();
	
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
		$html .= '<div><a href="' . esc_url( $order_url ) . '" id="order-now" target="_blank">Order Now</a></div>';
		$html .= '</div>';
	else :
		$html .= '<div class="price-item no-details"><div><a href="' . esc_url( $order_url ) . '" id="order-now" target="_blank">Order Now</a></div></div>';
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
	$html .= '<button class="lity-close" data-lity-close><img src="' . esc_url( all_menu_get_close_icon() ) . '" /></button>';
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
 * Render desktop filter buttons
 *
 * @param array $atts Shortcode attributes
 * @param array $terms Terms for filter buttons
 * @param string $unique_id Unique container ID
 * @return string Filter button HTML or empty string
 */
function all_menu_render_filters( $atts, $terms, $unique_id ) {
	if ( empty( $atts['taxonomy'] ) || is_wp_error( $terms ) || count( $terms ) === 0 ) {
		return '';
	}

	$html = '<div id="menu-filter-container" class="all-menu-filters">';
	
	// Only mark "All Items" as active if no term is pre-selected
	$active_class_all = empty( $atts['term'] ) ? 'active' : '';
	$enable_pagination = isset( $atts['enable_pagination'] ) ? esc_attr( $atts['enable_pagination'] ) : 'yes';
	$html .= '<button class="all-menu-filter-btn ' . esc_attr( $active_class_all ) . '" data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-term="" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-unique-id="' . esc_attr( $unique_id ) . '" data-enable-pagination="' . $enable_pagination . '">All Items</button>';

	foreach ( $terms as $term ) {
		$active_class = ( ! empty( $atts['term'] ) && $atts['term'] === $term->slug ) ? 'active' : '';
		$enable_pagination = isset( $atts['enable_pagination'] ) ? esc_attr( $atts['enable_pagination'] ) : 'yes';
		$html .= '<button class="all-menu-filter-btn ' . esc_attr( $active_class ) . '" data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-term="' . esc_attr( $term->slug ) . '" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-unique-id="' . esc_attr( $unique_id ) . '" data-enable-pagination="' . $enable_pagination . '">' . esc_html( $term->name ) . '</button>';
	}

	$html .= '<a class="all-menu-filter-btn catering-btn" href="' . esc_url( all_menu_get_catering_url() ) . '" target="_blank">Catering</a>';
	$html .= '</div>';

	return $html;
}

/**
 * Render mobile filter dropdown
 *
 * @param array $atts Shortcode attributes
 * @param array $terms Terms for dropdown options
 * @param string $unique_id Unique container ID
 * @return string Dropdown HTML
 */
function all_menu_render_dropdown( $atts, $terms, $unique_id ) {
	$enable_pagination = isset( $atts['enable_pagination'] ) ? esc_attr( $atts['enable_pagination'] ) : 'yes';
	$html = '<select class="menu-filter-dropdown" data-unique-id="' . esc_attr( $unique_id ) . '" data-enable-pagination="' . $enable_pagination . '">';
	$html .= '<option value="">All Items</option>';
	
	foreach ( $terms as $term ) {
		$selected = ( ! empty( $atts['term'] ) && $atts['term'] === $term->slug ) ? 'selected' : '';
		$enable_pagination = isset( $atts['enable_pagination'] ) ? esc_attr( $atts['enable_pagination'] ) : 'yes';
		$html .= '<option value="' . esc_attr( $term->slug ) . '" ' . $selected . ' data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-enable-pagination="' . $enable_pagination . '">' . esc_html( $term->name ) . '</option>';
	}
	
	$html .= '<option value="catering" data-url="' . esc_url( all_menu_get_catering_url() ) . '">Catering</option>';
	$html .= '</select>';

	return $html;
}

/**
 * Render loading spinner indicator
 *
 * @param string $unique_id Unique container ID
 * @return string Loader HTML with spinner
 */
function all_menu_render_loader( $unique_id ) {
	$html = '<div class="all-menu-loader" data-loader-id="' . esc_attr( $unique_id ) . '">';
	$html .= '<div class="all-menu-spinner"></div>';
	$html .= '</div>';

	return $html;
}

/**
 * Render posts container wrapper opening
 *
 * @param string $unique_id Unique container ID
 * @param bool $has_url_params Whether URL has filter parameters
 * @return string Posts wrapper opening HTML
 */
function all_menu_render_posts_wrapper_open( $unique_id, $has_url_params ) {
	$html = '<div class="all-menu-posts-wrapper">';
	$html .= all_menu_render_loader( $unique_id );
	
	$hidden_class = $has_url_params ? ' amf-hidden-initial' : '';
	$html .= '<ul id="' . esc_attr( $unique_id ) . '" class="custom-post-loop' . esc_attr( $hidden_class ) . '">';

	return $html;
}

/**
 * Render posts container wrapper closing
 *
 * @return string Posts wrapper closing HTML
 */
function all_menu_render_posts_wrapper_close() {
	return '</ul></div>';
}

/**
 * Render empty state message
 *
 * @return string Empty state HTML
 */
function all_menu_render_empty_state() {
	return '<li><p>No posts found.</p></li>';
}
