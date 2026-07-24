<?php
/**
 * Data Access Helpers
 *
 * Helpers for accessing and retrieving post data, excerpts, images, and metadata.
 * These functions handle ACF fields and WordPress post functions.
 *
 * @package Dynamic_Post_Filter
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get featured image URL with fallback
 *
 * @param int $post_id Post ID
 * @return string Featured image URL or fallback
 */
function all_menu_get_featured_image( $post_id ) {
	$featured_image_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
	$fallback_image_url = all_menu_get_fallback_image();
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
	$custom_order = all_menu_get_custom_taxonomy_order();

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
