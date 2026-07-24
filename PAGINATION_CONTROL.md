# Pagination Control Documentation

## Overview

The Dynamic Post Filter plugin now supports optional pagination control via the `enable_pagination` shortcode attribute. This allows developers to toggle pagination on/off independently of post limits, without affecting filtering, modals, or other functionality.

## Usage

### Enable Pagination (Default)

```php
[all-menu]
[all-menu enable_pagination='yes']
[all-menu posts_per_page='12' enable_pagination='yes']
```

**Result:** Pagination buttons appear (Prev, Next, page numbers)

### Disable Pagination

```php
[all-menu enable_pagination='no']
[all-menu posts_per_page='8' enable_pagination='no']
[all-menu post_type='menu' posts_per_page='20' taxonomy='menu-categories' enable_pagination='no']
```

**Result:** No pagination buttons. All posts (up to `posts_per_page` limit) display on a single page.

## How It Works

### Shortcode Registration

The `enable_pagination` attribute is registered with a default value of `'yes'`:

```php
// includes/shortcode.php
$atts = shortcode_atts(
    array(
        'posts_per_page'    => 8,
        'enable_pagination' => 'yes',  // NEW: Toggle pagination
        // ... other attributes
    ),
    $atts,
    'all-menu'
);
```

### Data Attribute Propagation

The setting is passed through the DOM via data attributes on all interactive elements:

**Filter Buttons** (`includes/render.php`):

```php
$html .= '<button ... data-enable-pagination="' . $enable_pagination . '">Filter</button>';
```

**Dropdown** (`includes/render.php`):

```html
<select class="menu-filter-dropdown" data-enable-pagination="yes"></select>
```

**Pagination Buttons** (`includes/pagination.php`):

```php
$html .= '<button ... data-enable-pagination="' . $enable_pagination . '">Next</button>';
```

### JavaScript Extraction

The JavaScript (`js/all-menu-filter.js`) extracts the flag from button data attributes:

```javascript
function getButtonData($btn) {
  return {
    // ... other data
    enablePagination: $btn.data("enable-pagination") || "yes",
  };
}
```

When a user clicks a filter button, the value is extracted and sent to AJAX:

```javascript
var ajaxData = {
  action: "all_menu_filter",
  // ... other parameters
  enable_pagination: btnData.enablePagination, // Sent to backend
};
```

### AJAX Handling

The AJAX handler (`includes/ajax.php`) receives and respects the flag:

```php
// Extract enable_pagination from POST
$enable_pagination = isset( $_POST['enable_pagination'] ) ?
    sanitize_text_field( $_POST['enable_pagination'] ) : 'yes';

// Only generate pagination if enabled
$pagination_enabled = strtolower( $enable_pagination ) === 'yes';
if ( $pagination_enabled && intval( $posts_per_page ) > 0 ) {
    $pagination_html = all_menu_get_pagination( $query, $atts, $unique_id );
}
```

### Initial Page Load

The shortcode callback (`includes/shortcode.php`) also respects the flag:

```php
$enable_pagination = strtolower( $atts['enable_pagination'] ) === 'yes';
if ( $enable_pagination && intval( $atts['posts_per_page'] ) > 0 ) {
    $pagination_html = all_menu_get_pagination( $query, $atts, $unique_id );
    $output .= $pagination_html;
}
```

## Feature Properties

### Backward Compatibility ✅

- Default value is `'yes'` (preserves existing behavior)
- Existing shortcodes continue to work unchanged
- No breaking changes to any functionality

### Independent Control ✅

- Pagination toggle separate from `posts_per_page`
- Can disable pagination while showing all 12 posts
- Or enable pagination with 5 posts per page

### AJAX Integration ✅

- Setting persists across filter changes
- Clicking different filter buttons maintains pagination state
- Pagination state follows user through entire session

### Non-Breaking ✅

- Filters work correctly with or without pagination
- Modals open/close normally
- Sorting unaffected
- Mobile dropdown works identically

## Complete Examples

### Multi-category Menu with Pagination

```php
[all-menu
    post_type='menu'
    posts_per_page='12'
    taxonomy='menu-categories'
    enable_pagination='yes'
]
```

**Result:** Shows 12 posts per page with prev/next buttons. Users can browse through all menu items.

### Single-page Menu Display (No Pagination)

```php
[all-menu
    post_type='menu'
    posts_per_page='24'
    enable_pagination='no'
]
```

**Result:** Shows up to 24 posts on one page. Ideal for restaurants showing entire menu at once.

### Featured Menu with Category Filters (No Pagination)

```php
[all-menu
    post_type='featured-item'
    taxonomy='item-type'
    posts_per_page='8'
    enable_pagination='no'
]
```

**Result:** Shows 8 featured items with category filters. No pagination — great for limited selections.

### Blog Posts with Pagination

```php
[all-menu
    post_type='post'
    posts_per_page='5'
    taxonomy='category'
    enable_pagination='yes'
]
```

**Result:** Blog post listing with 5 posts per page and category filters.

## Code Flow Diagram

```
[all-menu enable_pagination='no']
    ↓
shortcode_atts() → 'enable_pagination' = 'no'
    ↓
all_menu_render_filters() → adds data-enable-pagination="no" to buttons
    ↓
User clicks filter button
    ↓
getButtonData() → extracts enablePagination: "no" from button
    ↓
AJAX request → enable_pagination: "no" sent to backend
    ↓
all_menu_filter_ajax() →
    $pagination_enabled = (strtolower('no') === 'yes') = FALSE
    → pagination HTML not generated
    ↓
Response: {posts: "...", modals: "...", pagination: ""}
    ↓
JavaScript updates DOM → no pagination buttons rendered
```

## Technical Validation

### Security ✅

- Input sanitized: `sanitize_text_field( $_POST['enable_pagination'] )`
- Only accepts 'yes' or 'no' values (case-insensitive)
- Defaults to 'yes' for safety

### Performance ✅

- Zero overhead when enabled (normal pagination flow)
- Single boolean check when disabled (negligible cost)
- No additional database queries

### Browser Support ✅

- Works with all modern browsers
- jQuery-based (same as existing implementation)
- HTML5 data attributes supported universally

## Troubleshooting

### Pagination Still Shows When `enable_pagination='no'`

**Cause:** Possibly a JavaScript caching issue or incorrect attribute name.

**Solution:**

1. Clear browser cache
2. Verify shortcode syntax: `enable_pagination='no'` (lowercase, with quotes)
3. Check that `posts_per_page` > 0 (pagination requires this)

### `enable_pagination` Attribute Not Recognized

**Cause:** Plugin not updated to latest version.

**Solution:** Ensure you're running version 1.3.0 or later. Check:

```php
// In wp-admin/plugins.php or by reading all-menu-filter.php
Version: 1.3.0
```

### Pagination State Lost After Filter Change

**Cause:** This is expected behavior when pagination is enabled. User should expect to return to page 1 when changing filters.

**Solution:** This is by design. URL state preservation focuses on filter selection, not pagination state.

## Migration Guide

### From Static Pagination

If you previously had hardcoded pagination limits in `posts_per_page`, you can now disable pagination entirely:

**Before:** Showed 5 posts with pagination (users could navigate pages)

```php
[all-menu posts_per_page='5']
```

**After:** Show all 10 items without pagination

```php
[all-menu posts_per_page='10' enable_pagination='no']
```

### From Manual Shortcode Variants

If you had two separate shortcodes (one with pagination, one without), consolidate them:

**Before:**

```php
[all-menu post_type='menu']              <!-- With pagination -->
[all-menu post_type='special' posts_per_page='24']  <!-- Without -->
```

**After:**

```php
[all-menu post_type='menu' enable_pagination='yes']
[all-menu post_type='special' posts_per_page='24' enable_pagination='no']
```

## Future Enhancements

Potential improvements for future versions:

- `pagination_style` attribute (buttons vs. 'Load More')
- `pagination_position` attribute (top/bottom/both)
- Persistent pagination state via localStorage
- Custom pagination template support
- AJAX loading animation customization

## Support

For issues or questions about pagination control, refer to:

- CHANGELOG.md (Phase 9)
- Code comments in `includes/pagination.php`, `includes/render.php`, `includes/ajax.php`
- Main shortcode documentation in `includes/shortcode.php`
