# Changelog

All notable changes to the Dynamic Post Filter plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] — 2026-07-24

### Added

- **Lity 2.4.1 Lightbox Modal Integration** - New modal preview feature for posts
  - Click plus-icon button to open lightweight modal with post details
  - Modal displays post title and excerpt (up to 20 words)
  - Automatic scroll position restoration after modal closes
  - Event delegation support for AJAX-loaded modals
  - Modal visibility controlled via `class="lity-hide"` (not CSS display:none)
  - Unique modal IDs: `post-modal-{post_id}` for consistent referencing
  - CDN-based library (2kB gzipped) only loaded on pages with [all-menu] shortcode

- **Lity Close Button Handler** - Custom close button support in modals
  - Proper modal close animations via `instance.close()` method
  - Supports both initially-rendered and AJAX-loaded modals
  - Uses `[data-lity-close]` attribute for close buttons

- **Enhanced Modal Rendering**
  - Modals rendered in separate variable for valid XHTML structure
  - Modals inserted after posts wrapper (not inside)
  - Scroll position captured and restored during modal lifecycle

### Changed

- **Code Organization**
  - Moved script/style enqueuing from `all-menu-filter.php` to `includes/shortcode.php`
  - jQuery is now enqueued only when [all-menu] shortcode is present
  - Improved separation of concerns between main plugin file and shortcode logic

- **JavaScript Architecture** (`js/all-menu-filter.js`)
  - Refactored for better event handling and readability
  - Added SELECTORS, CLASSES, and DATA_KEYS constants for maintainability
  - Improved comment structure with clear section headers
  - Enhanced documentation for Lity integration workflow

- **PHP Documentation** (`includes/shortcode.php`)
  - Added comprehensive docblock with Lity 2.4.1 integration details
  - Documented AJAX workflow with modal rendering steps
  - Added technical architecture notes for future maintenance
  - Included usage examples and shortcode attributes reference

### Removed

- `all_menu_filter_enqueue_scripts()` function from main plugin file
  - jQuery enqueue now handled directly in shortcode callback
  - Reduces unnecessary function calls on non-plugin pages

### Technical Details

#### Lightbox Modal Workflow

1. User clicks plus-icon button on a post item
2. JavaScript captures current scroll position
3. Extracts modal ID from `data-modal-id` attribute
4. Creates temporary hidden anchor with `data-lity` attribute
5. Triggers native click event to invoke Lity's event delegation system
6. Lity intercepts and displays modal lightbox
7. Modal remains in DOM for reuse; no re-initialization needed
8. On close, scroll position is automatically restored

#### Browser History Support

- Maintained URL state persistence for filters and pagination
- Browser back/forward navigation continues to work correctly
- Modal interactions do not affect URL history

#### Performance Considerations

- Lity library loaded via CDN (2kB gzipped)
- Only loaded on pages using [all-menu] shortcode
- Dynamic modal elements automatically recognized via Lity event delegation
- No performance impact on pages without the shortcode

#### Security & Compatibility

- All existing nonce verification maintained
- Input sanitization preserved
- Output escaping unchanged
- Backward compatible with existing shortcode usage
- No breaking changes to existing functionality

### Files Modified

- `all-menu-filter.php` — Removed enqueue function (13 lines removed)
- `includes/shortcode.php` — Added Lity modal integration (+580, -182 lines)
- `js/all-menu-filter.js` — Added modal handlers (+643, -182 lines)
- `readme.txt` — Updated documentation (+9 lines)

### Testing

- ✅ Lightbox modals display correctly on post items
- ✅ Plus-icon click triggers modal properly
- ✅ Scroll position preserved after modal close
- ✅ AJAX filtering preserves modal functionality
- ✅ Multiple shortcode instances work independently
- ✅ Browser history navigation maintained
- ✅ Backward compatibility verified (no existing features broken)

---

## [1.1.0] — 2026-06-10

### Added

- Loading indicator spinner for filter and pagination requests
- Visual feedback (spinning loader) during AJAX operations

### Fixed

- CSS styling for inline loader styles
- Enqueue stylesheet for inline CSS loader styles
- Duplicate active class on filter buttons when term is pre-selected

### Changed

- Moved inline CSS to separate stylesheet (`css/all-menu-filter.css`)

---

## [1.0.0] — 2026-05-15

### Added

- Initial release: Dynamic Post Filter v1.0.0
- Dynamic AJAX filtering by taxonomy terms
- Smart pagination adapting to filtered results
- Responsive grid layout with column classes
- ACF integration for custom fields
- Featured image support with fallback
- Security features: nonce verification and input sanitization
- Multiple shortcode instances support
- Customizable post item template

---

[Unreleased]: https://github.com/darickquinto0629/dynamic-post-filter/compare/v1.2.0...main
[1.2.0]: https://github.com/darickquinto0629/dynamic-post-filter/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/darickquinto0629/dynamic-post-filter/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/darickquinto0629/dynamic-post-filter/releases/tag/v1.0.0
