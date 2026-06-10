# Dynamic Post Filter

A powerful WordPress plugin that displays posts and custom post types with dynamic AJAX-based taxonomy filtering and seamless pagination.

## Purpose

The Dynamic Post Filter plugin is designed to showcase any post type (posts, menu items, products, team members, custom post types) with an interactive filter system. Users can filter posts by taxonomy terms and browse through pages without reloading the entire page. The plugin uses AJAX for smooth interactions and supports ACF custom fields out of the box.

## Features

✅ **Dynamic AJAX Filtering** - Filter posts by taxonomy without page reload  
✅ **Smart Pagination** - Pagination that adapts to filtered results  
✅ **Loading Indicator** - Spinning loader shows during filter/pagination requests  
✅ **Responsive Grid Layout** - Supports responsive column classes (featured-col-2, featured-col-3)  
✅ **ACF Integration** - Display custom ACF field values (e.g., pricing)  
✅ **Featured Images** - Background image support with fallback placeholder  
✅ **Published Posts Only** - Automatically filters to show only published content  
✅ **Security** - Nonce verification and input sanitization  
✅ **Multiple Instances** - Unique ID system allows multiple shortcodes on same page  
✅ **Customizable Template** - Easy-to-modify post item HTML structure

## Installation

### Step 1: Download & Install

1. Download the `all-menu-filter` plugin folder
2. Upload to `/wp-content/plugins/` directory
3. Go to WordPress Admin → Plugins
4. Find "All Menu Filter" and click **Activate**

### Step 2: Basic Setup

1. Ensure you have a custom taxonomy (e.g., `menu-category`)
2. Assign taxonomy terms to your posts
3. Create/edit a page where you want to display the menu

### Step 3: Add the Shortcode

Insert this shortcode on your page:

```
[all-menu post_type="post" taxonomy="category" posts_per_page="10"]
```

## Shortcode Parameters

| Parameter        | Default | Description                                                |
| ---------------- | ------- | ---------------------------------------------------------- |
| `post_type`      | `post`  | Post type to display (e.g., 'post', 'menu-item')           |
| `taxonomy`       | ``      | Taxonomy slug to filter by (e.g., 'category', 'menu-type') |
| `posts_per_page` | `10`    | Number of items to display per page                        |
| `orderby`        | `date`  | Sort posts by field (e.g., 'date', 'title', 'menu_order')  |
| `order`          | `DESC`  | Sort direction ('ASC' or 'DESC')                           |
| `term`           | ``      | Pre-select a specific term by slug (e.g., 'appetizers')    |

## Usage Examples

### Example 1: Display All Menu Items with Category Filter

```
[all-menu post_type="menu" taxonomy="menu_category" posts_per_page="12"]
```

### Example 2: Display Posts Sorted by Title with Pre-selected Filter

```
[all-menu post_type="post" taxonomy="category" term="featured" orderby="title" order="ASC"]
```

### Example 3: Display Custom Post Type with 20 Items Per Page

```
[all-menu post_type="product" taxonomy="product_type" posts_per_page="20"]
```

## How It Works

### Initial Load

1. Shortcode is processed and WP_Query retrieves posts with specified parameters
2. Posts are rendered with the template (featured image, title, excerpt, price)
3. Pagination is generated based on total posts
4. Filter buttons are displayed with active state

### Filter Interaction

1. User clicks a filter button
2. JavaScript sends AJAX request with filter parameters
3. Server queries posts matching the filter
4. **Pagination automatically resets to page 1** for the filtered set
5. Posts and pagination are updated without page reload

### Pagination

1. User clicks a page number or Previous/Next button
2. JavaScript sends AJAX request with the new page number
3. Filter parameters are preserved (user stays within filtered results)
4. Page scrolls smoothly to top of posts
5. Posts and pagination update

### Loading Indicator

The plugin displays a loading spinner overlay when users interact with filters or pagination:

- **When it appears:** After clicking a filter button or pagination button
- **What it looks like:** Semi-transparent white overlay with rotating blue spinner
- **When it disappears:** Immediately when results load or if an error occurs
- **Purpose:** Provides visual feedback that the page is processing the request

The loading indicator is automatically positioned over the posts container and does not require any configuration.

## Customization Guide

### Modifying the Post Item Template

The plugin uses a customizable post item template. Look for this comment in the code:

```php
// ========================================
// POST ITEM TEMPLATE - CUSTOMIZE THIS SECTION
// Developers: Modify the HTML structure below to match your design requirements.
// All post data (title, excerpt, custom fields, etc.) can be accessed within this loop.
// ========================================
```

**Locations to edit:**

- `/includes/shortcode.php` - Line ~110 (initial load template)
- `/includes/shortcode.php` - Line ~204 (AJAX filter template)

**Available Variables in Template:**

```php
$post_id           // Current post ID
$featured_image_url // Featured image URL
$starting_at       // ACF field value (customizable)
get_the_title()    // Post title
get_the_content()  // Post content
get_field('field_name', $post_id)  // ACF custom fields
```

### Example: Adding a Custom Field Display

```php
$custom_field = get_field('custom_field_name', $post_id);
if ( $custom_field ) {
    $output .= '<p>Custom: ' . esc_html( $custom_field ) . '</p>';
}
```

### Styling

Add CSS for these classes:

- `.all-menu-posts-wrapper` - Posts container wrapper (has position: relative)
- `.custom-post-loop` - Posts container (ul element)
- `.post-item` - Individual post item (li element)
- `.menu-featured-image` - Featured image background
- `.featured-content` - Content wrapper
- `.all-menu-filters` - Filter buttons container
- `.all-menu-filter-btn` - Individual filter button
- `.all-menu-pagination` - Pagination container
- `.all-menu-page-btn` - Pagination button
- `.all-menu-loader` - Loading indicator overlay
- `.all-menu-loader.active` - Loading indicator when visible
- `.all-menu-spinner` - Spinning animation element

#### Customizing the Loading Indicator

The loading spinner uses built-in CSS styles. To customize the appearance, add CSS to your theme:

```css
/* Change spinner color */
.all-menu-spinner {
  border-top-color: #ff6b6b !important; /* Your color */
}

/* Change overlay background opacity */
.all-menu-loader {
  background-color: rgba(255, 255, 255, 0.5) !important; /* Less transparent */
}

/* Change spinner size */
.all-menu-spinner {
  width: 50px !important;
  height: 50px !important;
}

/* Change animation speed */
.all-menu-spinner {
  animation: spin 0.8s linear infinite !important; /* Faster */
}
```

### WP Rocket Caching Compatibility

If using WP Rocket, exclude pages with the `[all-menu]` shortcode from caching:

1. Go to **WP Rocket Settings** → **Caching**
2. Scroll to **Never Cache URLs**
3. Add the menu page URL (e.g., `/menu/`)

Alternatively, add to your theme's `functions.php`:

```php
add_filter( 'rocket_cache_reject_uri', function( $rejected_uris ) {
    $rejected_uris[] = '/menu/';  // Adjust URL as needed
    return $rejected_uris;
});
```

## Requirements

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **jQuery:** Automatically enqueued by the plugin

## Optional Dependencies

- **Advanced Custom Fields (ACF):** For displaying custom field values like pricing
- **WP Rocket:** For caching configuration

## Technical Details

### Security Features

✅ Nonce verification on all AJAX requests  
✅ Input sanitization with `sanitize_text_field()`  
✅ Output escaping with `esc_attr()`, `esc_html()`, `esc_url()`  
✅ Only displays published posts

### Performance Considerations

- Uses efficient WP_Query with post_status filtering
- AJAX requests bypass page caching
- Pagination parameters stored in HTML data attributes
- Unique ID system prevents conflicts with multiple instances

## File Structure

```
all-menu-filter/
├── all-menu-filter.php          # Main plugin file
├── includes/
│   └── shortcode.php            # Shortcode logic & AJAX handlers
├── js/
│   └── all-menu-filter.js       # Frontend JavaScript
├── readme.txt                   # Plugin readme
└── README.md                    # This file
```

## Troubleshooting

### Filters Not Working

1. **Check JavaScript Console** - Look for AJAX errors
2. **Verify Nonce** - Clear browser cache and reload page
3. **Check WP Rocket** - Ensure page is not cached
4. **Verify Taxonomy** - Confirm taxonomy name in shortcode matches actual taxonomy

### Pagination Shows Wrong Count

1. **Clear Cache** - If using WP Rocket, clear all caches
2. **Check Published Posts** - Only published posts are counted
3. **Verify Filter** - Ensure filter is set correctly

### Images Not Displaying

1. **Check URL** - Verify featured image URL is correct
2. **Fallback Image Path** - Update fallback path in code if needed
3. **File Permissions** - Ensure image files are readable

## Developer Notes

### Hooks & Filters

Currently, the plugin uses standard WordPress hooks:

- `wp_ajax_all_menu_filter` - AJAX action (logged-in users)
- `wp_ajax_nopriv_all_menu_filter` - AJAX action (non-logged users)

### Extending the Plugin

To add custom functionality:

1. Modify the post item template in `shortcode.php`
2. Add custom JavaScript in `all-menu-filter.js`
3. Use WordPress hooks and filters for extending

## License

GPL v2 or later - See LICENSE file

## Author

Darick L. Quinto  
[jollity.io](https://jollity.io)

## Support

For issues, feature requests, or contributions, please visit the GitHub repository.

---

**Version:** 1.1.0  
**Last Updated:** 2026-06-10

## Changelog

### v1.1.0

- Added loading indicator spinner for filter and pagination requests
- Improved user experience with visual feedback during AJAX operations
- Fixed CSS styling for inline loader styles

### v1.0.0

- Initial release
