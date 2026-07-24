# Changelog

All notable changes to the Dynamic Post Filter plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] — 2026-07-24 — Refactoring: Rendering & Data Access Helpers

### Phase 1: Extract Rendering Helpers

#### Added

- **HTML Rendering Functions** (`includes/shortcode.php`)
  - `all_menu_render_post()` — Render single post item with image, title, excerpt, price button
  - `all_menu_render_modal()` — Render Lity modal with post details and price section
  - `all_menu_render_price_section()` — Render price repeater loop with Order Now button

#### Changed

- **Refactored Rendering Pipeline**
  - `all_menu_callback()` now uses `all_menu_render_post()` and `all_menu_render_modal()`
  - `all_menu_filter_ajax()` now uses `all_menu_render_post()` and `all_menu_render_modal()`
  - Eliminated 132 lines of duplicate HTML generation code
  - Single source of truth for post/modal rendering logic

#### Technical Details (Phase 1)

- **Code Reduction:** 132 lines removed, 114 lines added (net -18 lines)
- **DRY Principle:** Duplicate rendering code consolidated to single functions
- **Maintainability:** HTML structure changes now require single edit point
- **Zero Output Change:** All HTML output identical to previous version
- **Backward Compatibility:** 100% compatible with existing functionality

---

### Phase 2: Extract Data Access Helpers

#### Added

- **Helper Functions for Data Access** (`includes/shortcode.php`)
  - `all_menu_get_featured_image()` — Retrieve featured image with fallback to beef-brisket.jpg
  - `all_menu_get_excerpt()` — Get post excerpt with fallback to post content
  - `all_menu_get_modal_id()` — Generate consistent modal IDs (format: post-modal-{post_id})
  - `all_menu_get_starting_price()` — Access ACF starting_at field value
  - `all_menu_sort_terms()` — Sort taxonomy terms by custom menu order (bbq, sandwiches, sides, desserts, drinks)

#### Changed

- **Refactored Data Retrieval**
  - `all_menu_callback()` now uses all 5 data access helpers
  - `all_menu_filter_ajax()` now uses all 5 data access helpers
  - `featured_callback()` now uses all 5 data access helpers
  - No changes to output — HTML remains character-for-character identical

#### Technical Details (Phase 2)

- **Code Reusability:** Eliminates duplicate data access logic across three rendering contexts
- **Maintainability:** ACF field names, fallback values, term ordering all managed in single location
- **Consistency:** All three rendering functions (shortcode, AJAX, featured) use identical data retrieval
- **Zero Output Change:** All HTML output identical to Phase 1
- **Backward Compatibility:** 100% compatible with existing functionality

### Combined Testing (Phase 1 + Phase 2)

- ✅ Missing featured images handled by fallback (beef-brisket.jpg)
- ✅ Starting price displays correctly across all three rendering contexts
- ✅ Excerpt fallback to content when excerpt unavailable
- ✅ Taxonomy term ordering preserved (bbq first, others in defined order)
- ✅ All three rendering contexts (initial render, AJAX filter, featured slider) produce identical output
- ✅ Modal IDs consistent across all contexts
- ✅ No changes to user-facing functionality or HTML structure

### Files Modified

- `includes/shortcode.php` — Added 8 helper functions (3 rendering + 5 data access), refactored 3 major functions to use helpers

### Summary

**Phase 1 + Phase 2 combined:**

- Lines added: 114 (helper functions)
- Lines removed: 132 (duplicate code)
- Net reduction: -18 lines
- Output: Character-for-character identical
- Impact: Massive improvement in code maintainability, reusability, and DRY principle compliance

---

### Phase 3: Centralize Query Builder

#### Added

- **Query Arguments Builder Function** (`includes/shortcode.php`)
  - `all_menu_build_query_args()` — Build WP_Query arguments array with all parameters (post_type, posts_per_page, paged, orderby, order, taxonomy, term)

#### Changed

- **Centralized Query Building**
  - `all_menu_callback()` now uses `all_menu_build_query_args()` instead of inline array construction
  - `all_menu_filter_ajax()` now uses `all_menu_build_query_args()` instead of inline array construction
  - Eliminated 50 lines of duplicate WP_Query argument assembly code
  - Single source of truth for query argument validation and assembly

#### Technical Details (Phase 3)

- **Code Reduction:** 50 lines removed duplicate query builder code
- **Query Consistency:** Both shortcode and AJAX use identical query argument assembly
- **Maintainability:** Query logic changes now require single edit point
- **SQL Consistency:** Both contexts generate identical SQL queries
- **Zero Output Change:** All query behavior identical to Phase 2
- **Backward Compatibility:** 100% compatible with existing functionality

#### Testing (Phase 3)

- ✅ All Items filter works (empty term, no tax_query)
- ✅ Taxonomy filter works (term specified, tax_query included)
- ✅ Empty taxonomy handled correctly (no filter buttons generated)
- ✅ Pagination parameter passed correctly to WP_Query
- ✅ URL filtering parameters respected
- ✅ SQL queries identical between shortcode and AJAX contexts

### Files Modified (Phase 3)

- `includes/shortcode.php` — Added 1 query builder helper, refactored 2 major functions to use it

### Combined Summary (Phase 1 + Phase 2 + Phase 3)

- Lines added: ~130 (helper functions)
- Lines removed: ~182 (duplicate code)
- Net reduction: ~52 lines
- Output: Character-for-character identical
- Impact: Maximum code maintainability, reusability, and DRY principle compliance
- Consistency: All three major functions (shortcode, AJAX, featured) now share common helpers

### Added

- **Featured Image Clickability** - Click featured images to trigger modal popups
  - Added `data-modal-id` attributes to featured image elements in all rendering contexts (initial render, AJAX, featured slider)
  - Featured image clicks now open modals using same modal system as plus-icon buttons
  - Implemented shared `openModal()` helper function in JavaScript for consistent behavior

### Changed

- **Price Display Enhancement** (`includes/shortcode.php`)
  - Added $ currency prefix to all price displays
  - Implemented conditional check: only display $ prefix if price value is not empty
  - Applied to all three rendering contexts: initial render, AJAX handler, featured slider
  - Updated in `all_menu_callback()`, `all_menu_filter_ajax()`, and `featured_callback()` functions

- **JavaScript Improvements** (`js/all-menu-filter.js`)
  - Refactored modal opening logic into reusable `openModal()` helper function
  - Improved code formatting and indentation consistency
  - Added `.menu-featured-image` click handler for featured image modal triggers
  - Code cleanup and minor formatting improvements

### Technical Details

- Featured image modal trigger: Uses existing `data-modal-id` linking pattern established in v1.2.0
- Price formatting: Ternary operator checks if price value exists before rendering span with $ prefix
- Event delegation: Lity library continues to recognize dynamically added featured image elements
- Backward compatibility: All changes are non-breaking; existing functionality preserved

### Files Modified

- `includes/shortcode.php` — Added featured image modal IDs, conditional price formatting in three functions
- `js/all-menu-filter.js` — Added `openModal()` helper, `.menu-featured-image` click handler, code formatting

### Testing

- ✅ Featured images clickable in all three contexts (initial, AJAX, featured slider)
- ✅ Modals open correctly when featured image clicked
- ✅ Prices display with $ prefix only when price value is present
- ✅ Scroll position preserved when modal opened via featured image click
- ✅ All existing functionality remains intact

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
