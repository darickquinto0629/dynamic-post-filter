=== All Menu Filter ===
Contributors: Your Name
Tags: custom post type, filter, taxonomy, ajax
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin for displaying custom post types with dynamic AJAX taxonomy filtering.

== Description ==

All Menu Filter is a simple yet powerful plugin that allows you to display custom post types with dynamic AJAX filtering based on taxonomies. Simply use the shortcode and users can click on taxonomy terms to filter posts without page reload.

== Usage ==

Use the `[all-menu]` shortcode with the following attributes:

- `post_type` - The custom post type to display (default: 'post')
- `posts_per_page` - Number of posts to display (default: -1, show all)
- `orderby` - Order posts by 'date', 'title', etc. (default: 'date')
- `order` - Order direction 'ASC' or 'DESC' (default: 'DESC')
- `taxonomy` - The custom taxonomy to filter by
- `term` - Initial term to filter by (optional)

== Examples ==

Display portfolio posts filtered by portfolio_category:
`[all-menu post_type="portfolio" taxonomy="portfolio_category"]`

Display events with 20 posts per page:
`[all-menu post_type="events" posts_per_page="20" taxonomy="event_type"]`

== Installation ==

1. Extract the plugin files to `/wp-content/plugins/all-menu-filter/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the `[all-menu]` shortcode in pages or posts

== Changelog ==

= 1.0.0 =
* Initial release

== Support ==

For support, please visit https://example.com
