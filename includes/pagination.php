<?php
/**
 * Pagination Helpers
 *
 * Pagination rendering with ellipsis logic and state preservation.
 *
 * @package Dynamic_Post_Filter
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate a pagination button with data attributes (internal helper)
 *
 * Private helper for pagination rendering. Not intended for external use.
 * Builds a single page button with filter parameters for AJAX request.
 *
 * @param int $page_num Page number
 * @param array $atts Shortcode attributes (post_type, taxonomy, term, orderby, order, posts_per_page)
 * @param string $unique_id Unique container ID
 * @param string $label Button label text
 * @return string Button HTML
 */
function _all_menu_render_page_button( $page_num, $atts, $unique_id, $label ) {
	return '<button class="all-menu-page-btn" data-page="' . intval( $page_num ) . '" data-post-type="' . esc_attr( $atts['post_type'] ) . '" data-taxonomy="' . esc_attr( $atts['taxonomy'] ) . '" data-term="' . esc_attr( $atts['term'] ) . '" data-orderby="' . esc_attr( $atts['orderby'] ) . '" data-order="' . esc_attr( $atts['order'] ) . '" data-posts-per-page="' . intval( $atts['posts_per_page'] ) . '" data-unique-id="' . esc_attr( $unique_id ) . '">' . esc_html( $label ) . '</button>';
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
		$pagination_html .= _all_menu_render_page_button( $prev_page, $atts, $unique_id, 'Prev' );
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
				$pagination_html .= _all_menu_render_page_button( $i, $atts, $unique_id, $i );
			}

			$last_shown = $i;
		}
	}

	// Next page link
	if ( $query->query_vars['paged'] < $query->max_num_pages ) {
		$next_page = $query->query_vars['paged'] + 1;
		$pagination_html .= _all_menu_render_page_button( $next_page, $atts, $unique_id, 'Next' );
	}

	$pagination_html .= '</div>';
	return $pagination_html;
}
