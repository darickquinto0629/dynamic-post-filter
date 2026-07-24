<?php
/**
 * Query Builder
 *
 * WP_Query argument assembly for post filtering and taxonomy filtering.
 *
 * @package Dynamic_Post_Filter
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build WP_Query arguments array
 *
 * @param string $post_type Post type to query
 * @param int $posts_per_page Posts per page for pagination
 * @param int $paged Current page number (1-based)
 * @param string $orderby Order by field
 * @param string $order Order direction (ASC/DESC)
 * @param string $taxonomy Taxonomy slug for filtering (optional)
 * @param string $term Term slug to filter by (optional)
 * @return array WP_Query arguments
 */
function all_menu_build_query_args( $post_type, $posts_per_page, $paged, $orderby, $order, $taxonomy = '', $term = '' ) {
	// Build base query arguments
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

	return $query_args;
}
