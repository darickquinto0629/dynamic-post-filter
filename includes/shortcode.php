<?php
/**
 * Dynamic Post Filter - Shortcode Orchestrator
 *
 * Main entry point for the plugin. Loads all modular components and registers shortcodes.
 * This file should be included in the main plugin file (all-menu-filter.php).
 *
 * @package Dynamic_Post_Filter
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ========================================
// LOAD MODULAR COMPONENTS
// ========================================
// Dependencies are loaded in order to ensure functions are available
// when later files need them.

// Configuration: URLs, paths, and settings
require_once plugin_dir_path( __FILE__ ) . 'config.php';

// Data Access: Post retrieval and ACF field access
require_once plugin_dir_path( __FILE__ ) . 'helpers.php';

// Rendering: HTML generation for posts, modals, filters, etc.
require_once plugin_dir_path( __FILE__ ) . 'render.php';

// Query: WP_Query argument assembly
require_once plugin_dir_path( __FILE__ ) . 'query.php';

// Pagination: Page navigation controls
require_once plugin_dir_path( __FILE__ ) . 'pagination.php';

// Assets: Script and style enqueuing
require_once plugin_dir_path( __FILE__ ) . 'assets.php';

// AJAX: Dynamic filtering and pagination
require_once plugin_dir_path( __FILE__ ) . 'ajax.php';

// ========================================
// SHORTCODE REGISTRATION & CALLBACKS
// ========================================

/**
 * Register [all-menu] shortcode
 */
add_shortcode( 'all-menu', 'all_menu_callback' );

/**
 * [all-menu] Shortcode Callback
 * 
 * Renders filterable post grid with Lity 2.4.1 lightbox modals and pagination.
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output
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

	// Enqueue all assets (scripts, styles, and localization)
	all_menu_enqueue_assets();

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
	$query_args = all_menu_build_query_args(
		sanitize_text_field( $atts['post_type'] ),
		intval( $atts['posts_per_page'] ),
		1,
		sanitize_text_field( $atts['orderby'] ),
		sanitize_text_field( $atts['order'] ),
		sanitize_text_field( $atts['taxonomy'] ),
		sanitize_text_field( $atts['term'] )
	);

	// Execute the query
	$query = new WP_Query( $query_args );

	$output = '';

	// Render desktop filter buttons
	$output .= all_menu_render_filters( $atts, $terms, $unique_id );
	
	// Render mobile filter dropdown
	$output .= all_menu_render_dropdown( $atts, $terms, $unique_id );

	// Open posts wrapper container
	$has_url_params = ! empty( $_GET['amf_post_type'] ) && ! empty( $_GET['amf_taxonomy'] ) ? true : false;
	$output .= all_menu_render_posts_wrapper_open( $unique_id, $has_url_params );

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
		$output .= all_menu_render_empty_state();
	}

	// Close posts wrapper
	$output .= all_menu_render_posts_wrapper_close();
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
// FEATURED SHORTCODE (Separate Implementation)
// ========================================

/**
 * Register [featured] shortcode
 */
add_shortcode( 'featured', 'featured_callback' );
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
                  <a class="plus-icon" href="javascript:void(0)" data-modal-id="<?php echo esc_attr( all_menu_get_modal_id( $post->ID ) ); ?>"><img src="<?php echo esc_url( all_menu_get_add_icon() ); ?>" /></a>
              </div>
              <?php 
              // Generate modal for this post
              $modal_id = all_menu_get_modal_id( $post->ID );
              $order_url = all_menu_get_order_url();
				$modals_output .= '<div id="' . esc_attr( $modal_id ) . '" class="lity-modal lity-hide">';
				$modals_output .= '<button class="lity-close" data-lity-close><img src="' . esc_url( all_menu_get_close_icon() ) . '" /></button>';
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

						$modals_output .= '<div><a href="' . esc_url( $order_url ) . '" id="order-now" target="_blank">Order Now</a></div>';

					$modals_output .= '</div>';
				
				// No value.
				else :
						$modals_output .= '<div class="price-item no-details"><div><a href="' . esc_url( $order_url ) . '" id="order-now" target="_blank">Order Now</a></div></div>';
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