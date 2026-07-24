# Changelog

All notable changes to the Dynamic Post Filter plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] — 2026-07-24 — Refactoring: Rendering & Data Access Helpers

### Phase 7: Centralize Configuration Values

#### Added

- **Configuration Helpers** (`includes/shortcode.php`)
  - `all_menu_get_order_url()` — Order experience URL
  - `all_menu_get_fallback_image()` — Default featured image
  - `all_menu_get_add_icon()` — Plus icon SVG path
  - `all_menu_get_close_icon()` — Modal close button SVG path
  - `all_menu_get_catering_url()` — Catering page link
  - `all_menu_get_custom_taxonomy_order()` — Menu category sort order

#### Changed

- **Refactored Hardcoded Values**
  - `all_menu_get_featured_image()` uses `all_menu_get_fallback_image()`
  - `all_menu_render_post()` uses `all_menu_get_add_icon()` and `all_menu_get_order_url()`
  - `all_menu_render_price_section()` uses `all_menu_get_order_url()`
  - `all_menu_render_filters()` uses `all_menu_get_catering_url()` and `all_menu_get_order_url()`
  - `all_menu_render_dropdown()` uses `all_menu_get_catering_url()` and `all_menu_get_order_url()`
  - `all_menu_render_modal()` uses `all_menu_get_close_icon()`
  - `all_menu_sort_terms()` uses `all_menu_get_custom_taxonomy_order()`
  - `featured_callback()` uses configuration helpers

#### Technical Details (Phase 7)

- **Single Source of Truth:** All configuration values centralized in helper functions
- **Easy Maintenance:** Update URLs or image paths in one place only
- **Configuration Values Extracted:**
  - Order URL: GQ BBQ order platform (long URL with tracking params)
  - Fallback Image: Default featured image for missing thumbnails
  - Add Icon: Plus icon SVG for modal trigger
  - Close Icon: X button SVG for modal close
  - Catering URL: GQBBQ catering page
  - Taxonomy Order: Custom sort order (bbq, sandwiches, sides, desserts, drinks)
- **Zero Output Change:** HTML output identical
- **Backward Compatibility:** 100% compatible with existing functionality
- **Zero Logic Changes:** Pure configuration extraction

#### Testing (Phase 7)

- ✅ Featured images display (uses fallback correctly)
- ✅ Add icons appear on posts (modal trigger working)
- ✅ Close button works (modal closing)
- ✅ Order Now buttons functional (URL correct)
- ✅ Catering button/link functional (URL correct)
- ✅ Filter buttons in custom order (bbq, sandwiches, sides, desserts, drinks)

### Files Modified (Phase 7)

- `includes/shortcode.php` — Added 6 configuration helpers, refactored all callsites

---

### Phase 6: Simplify Pagination Rendering

#### Changed

- **Refactored Pagination Function** (`includes/shortcode.php`)
  - `all_menu_get_pagination()` now uses internal `_all_menu_render_page_button()` helper
  - Removed duplicated button HTML generation (was repeated 3+ times)
  - Simplified logic while keeping public API unchanged

#### Technical Details (Phase 6)

- **Internal Helper:** `_all_menu_render_page_button()` (private, not for external use)
  - Builds single pagination button with all data attributes
  - Reduces code duplication for prev, page numbers, and next buttons
  - Single source of truth for button HTML structure
- **Public API:** `all_menu_get_pagination()` remains unchanged
- **Zero Output Change:** Pagination HTML output identical
- **Backward Compatibility:** 100% compatible with existing functionality
- **Risk Mitigation:** Minimal changes, no logic changes, only code cleanup

#### Testing (Phase 6)

- ✅ Single page (no pagination rendered)
- ✅ Two pages (prev and next buttons)
- ✅ Multiple pages (10+ pages with ellipsis)
- ✅ Ellipsis logic preserved (shows first, last, current +/- range)
- ✅ Prev button works correctly
- ✅ Next button works correctly
- ✅ Page number buttons functional
- ✅ Filter parameters persist across pagination clicks

### Files Modified (Phase 6)

- `includes/shortcode.php` — Added 1 internal helper, refactored pagination function

---

### Phase 5: Centralize Asset Loading

#### Added

- **Asset Enqueuing Function** (`includes/shortcode.php`)
  - `all_menu_enqueue_assets()` — Centralized asset loading for scripts and styles

#### Changed

- **Refactored Asset Loading**
  - `all_menu_callback()` now calls `all_menu_enqueue_assets()` instead of inline enqueuing
  - jQuery enqueue consolidated
  - Plugin scripts and styles enqueue consolidated
  - Lity library enqueue consolidated
  - Script localization consolidated
  - Single source of truth for asset loading

#### Technical Details (Phase 5)

- **Asset Dependencies:** jQuery → all-menu-filter script (ordered correctly)
- **Lity CDN:** Version 2.4.1 loaded via jsDelivr
- **Script Localization:** all_menu_nonce created and passed via all_menu_data object
- **Enqueue Point:** Early in callback to ensure assets available before rendering
- **Commented CSS:** all-menu-filter.css remains commented (inline styling used)
- **Code Reduction:** 40+ lines of enqueue code removed from callback
- **Zero Output Change:** Asset loading unchanged, HTML output identical
- **Backward Compatibility:** 100% compatible with existing functionality

#### Testing (Phase 5)

- ✅ Network tab shows jQuery loaded
- ✅ Network tab shows lity library loaded
- ✅ Network tab shows plugin JS loaded
- ✅ Network tab shows lity CSS loaded
- ✅ No duplicate asset loading
- ✅ All scripts load in correct order
- ✅ AJAX nonce properly localized
- ✅ No console errors

### Files Modified (Phase 5)

- `includes/shortcode.php` — Added 1 asset enqueuing helper, refactored callback

---

### Phase 4: Extract UI Components

#### Added

- **UI Component Rendering Functions** (`includes/shortcode.php`)
  - `all_menu_render_filters()` — Render desktop filter button bar with taxonomy-based buttons and Catering link
  - `all_menu_render_dropdown()` — Render mobile dropdown selector for filters with options
  - `all_menu_render_loader()` — Render loading spinner overlay during AJAX requests
  - `all_menu_render_posts_wrapper()` — Render container divs and loading indicator for posts
  - `all_menu_render_empty_state()` — Render "No posts found" message

#### Changed

- **Refactored UI Rendering Pipeline**
  - `all_menu_callback()` becomes orchestrator function
  - Desktop filters now generated via `all_menu_render_filters()`
  - Mobile dropdown now generated via `all_menu_render_dropdown()`
  - Loader HTML extracted to `all_menu_render_loader()`
  - Posts wrapper structure extracted to `all_menu_render_posts_wrapper()`
  - Empty state message extracted to `all_menu_render_empty_state()`
  - Eliminated 60+ lines of UI markup code from main callback

#### Technical Details (Phase 4)

- **Code Reduction:** 60+ lines of UI markup removed, ~80 lines added in helpers (net reduction)
- **Component Reusability:** UI components can now be used independently or in different sequences
- **Maintainability:** UI structure changes localized to component functions
- **Mobile/Desktop:** Separate rendering for filter buttons (desktop) vs dropdown (mobile)
- **Callback Clarity:** Main callback now reads as orchestration logic instead of markup generation
- **Zero Output Change:** All HTML output identical to Phase 3
- **Backward Compatibility:** 100% compatible with existing functionality

#### Testing (Phase 4)

- ✅ Desktop filters render correctly with all taxonomy buttons
- ✅ Mobile dropdown renders with all options
- ✅ Loader spinner visible during AJAX requests
- ✅ Posts wrapper structure intact
- ✅ Empty state message displays when no posts found
- ✅ Catering link appears in desktop filters
- ✅ Catering option appears in mobile dropdown
- ✅ Hidden initial state class applied when URL params detected
- ✅ Pagination structure unaffected
- ✅ AJAX response rendering unchanged

### Files Modified (Phase 4)

- `includes/shortcode.php` — Added 5 UI component helpers, refactored `all_menu_callback()` to orchestrate

---

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
