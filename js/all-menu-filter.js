jQuery(document).ready(function ($) {
  // ========================================
  // CONSTANTS & SELECTORS
  // ========================================
  var SELECTORS = {
    filterBtn: ".all-menu-filter-btn",
    pageBtn: ".all-menu-page-btn",
    postLoop: ".custom-post-loop",
    pagination: ".all-menu-pagination",
    loader: ".all-menu-loader",
  };

  var CLASSES = {
    active: "active",
    hiddenInitial: "amf-hidden-initial",
  };

  var DATA_KEYS = {
    initialPaged: "amf_initial_paged",
    urlTerm: "amf_url_term",
  };

  // ========================================
  // LIGHTBOX MODAL HANDLER (Lity 2.4.1)
  // ========================================
  //
  // This section handles the interaction between the plus-icon buttons and the
  // Lity 2.4.1 lightbox library. The implementation uses dynamic anchor creation
  // to trigger Lity's built-in event delegation system while preserving scroll
  // position and preventing default anchor behavior.
  //
  // Key Implementation Details:
  // 1. Capture scroll position before modal opens
  // 2. Extract modal ID from data-modal-id attribute
  // 3. Create temporary hidden anchor with data-lity attribute
  // 4. Trigger native click event to invoke Lity's automatic handling
  // 5. Clean up and restore scroll position post-render
  //
  // Technical Notes:
  // - Lity automatically initializes on page load and handles event delegation
  // - No manual Lity API calls needed (e.g., no lity.init() required)
  // - Dynamic DOM elements automatically trigger Lity listeners
  // - Modal visibility controlled via class="lity-hide" in HTML (not CSS)
  // - Prevents default anchor scroll behavior by using javascript:void(0) href

  var scrollPos = 0;

  // Helper function to open modal for both plus-icon and featured-image
  function openModal(modalId) {
    if (!modalId) return false;

    scrollPos = $(window).scrollTop();

    // Create temporary anchor element with Lity data attribute
    // This leverages Lity's automatic event delegation system
    var tempLink = $(
      '<a href="#' + modalId + '" data-lity style="display:none;"></a>',
    ).appendTo("body");

    // Trigger click event - Lity intercepts and handles modal display
    tempLink[0].click();

    // Cleanup: Remove temporary element and restore user scroll position
    // 50ms delay allows Lity to complete modal rendering
    setTimeout(function () {
      tempLink.remove();
      $(window).scrollTop(scrollPos);
    }, 50);

    return false;
  }

  $(document).on("click", ".plus-icon", function (e) {
    e.preventDefault();
    e.stopPropagation();

    var modalId = $(this).data("modal-id");
    openModal(modalId);
  });

  $(document).on("click", ".menu-featured-image", function (e) {
    e.preventDefault();
    e.stopPropagation();

    var modalId = $(this).data("modal-id");
    openModal(modalId);
  });

  // ========================================
  // LITY CLOSE BUTTON HANDLER
  // ========================================
  //
  // Handles close button clicks within Lity modals using the lity:open event.
  // This allows custom close button elements with [data-lity-close] to properly
  // close the modal by calling Lity's instance.close() method.
  //
  // Implementation:
  // - Listens for Lity's 'lity:open' event after modal display
  // - Attaches click handler to close button within modal instance
  // - Calls instance.close() to properly trigger modal close animations
  //
  // Why This Approach:
  // - Lity's standard data-lity-close attribute doesn't reliably work on custom elements
  // - Using the instance's close() method ensures proper cleanup and animations
  // - Event delegation supports both initially-rendered and AJAX-loaded modals

  $(document).on("lity:open", function (e, instance) {
    instance.element().on("click", "[data-lity-close]", function () {
      instance.close();
    });
  });

  // ========================================
  // UTILITY FUNCTIONS
  // ========================================

  /**
   * Parse and extract URL parameters for filter state restoration
   *
   * URL Parameters (prefixed with 'amf_' to avoid collisions):
   *  - amf_post_type: The post type being filtered
   *  - amf_taxonomy: The taxonomy being used for filters
   *  - amf_term: The selected taxonomy term (empty string for "All Items")
   *  - amf_paged: Current page number
   *
   * @returns {Object} Parsed URL parameters with defaults
   *  - postType: string or null
   *  - taxonomy: string or null
   *  - term: string (empty for "All Items")
   *  - paged: number (default 1)
   */
  function getUrlParams() {
    try {
      var params = new URLSearchParams(window.location.search);
      return {
        postType: params.get("amf_post_type"),
        taxonomy: params.get("amf_taxonomy"),
        term: params.get("amf_term") || "",
        paged: parseInt(params.get("amf_paged")) || 1,
      };
    } catch (e) {
      console.warn("Error parsing URL params:", e);
      return { postType: null, taxonomy: null, term: "", paged: 1 };
    }
  }

  /**
   * Update browser URL to reflect current filter/pagination state
   *
   * Uses HTML5 History API (pushState) to maintain browser history and enable
   * back/forward navigation while keeping page state synchronized with URL.
   *
   * Implementation Notes:
   *  - "All Items" filters use clean URLs (no amf_term parameter)
   *  - Regular filters include amf_term parameter for state restoration
   *  - State object stored with pushState enables popstate handler restoration
   *  - Silently fails on browsers without History API support (graceful degradation)
   *
   * @param {string} taxonomy - Taxonomy identifier
   * @param {string} postType - Post type identifier
   * @param {string} term - Term slug (empty string for "All Items")
   * @param {number} paged - Page number (1-based)
   */
  function updateUrlState(taxonomy, postType, term, paged) {
    try {
      if (!window.history || !window.history.pushState) return;

      var params = new URLSearchParams();
      params.set("amf_post_type", postType);
      params.set("amf_taxonomy", taxonomy);
      params.set("amf_term", term);
      params.set("amf_paged", paged);

      var newUrl = window.location.pathname + "?" + params.toString();
      window.history.pushState(
        { taxonomy: taxonomy, postType: postType, term: term, paged: paged },
        "",
        newUrl,
      );
    } catch (e) {
      console.warn("Error updating URL state:", e);
    }
  }

  /**
   * Extract filter/pagination button attributes into normalized data object
   *
   * Button data attributes set by shortcode backend via PHP:
   *  - data-taxonomy: Custom taxonomy slug
   *  - data-term: Term slug (empty for "All Items")
   *  - data-post-type: Post type identifier
   *  - data-orderby: Sort field (date, title, etc.)
   *  - data-order: Sort direction (ASC/DESC)
   *  - data-posts-per-page: Results per page
   *  - data-unique-id: Shortcode instance identifier for multi-instance support
   *
   * @param {jQuery} $btn - Filter or pagination button element
   * @returns {Object} Normalized button configuration
   */
  function getButtonData($btn) {
    return {
      taxonomy: $btn.data("taxonomy"),
      term: $btn.data("term"),
      postType: $btn.data("post-type"),
      orderby: $btn.data("orderby"),
      order: $btn.data("order"),
      postsPerPage: $btn.data("posts-per-page"),
      uniqueId: $btn.data("unique-id"),
      enablePagination: $btn.data("enable-pagination") || "yes",
    };
  }

  /**
   * Restore posts container visibility after loading
   * @param {string} uniqueId - Container unique ID
   */
  function restoreVisibility(uniqueId) {
    $("#" + uniqueId)
      .removeClass(CLASSES.hiddenInitial)
      .css("visibility", "visible")
      .css("opacity", "1");
  }

  /**
   * Show loading indicator
   * @param {string} uniqueId - Container unique ID
   */
  function showLoading(uniqueId) {
    $('[data-loader-id="' + uniqueId + '"]').addClass(CLASSES.active);
  }

  /**
   * Hide loading indicator
   * @param {string} uniqueId - Container unique ID
   */
  function hideLoading(uniqueId) {
    $('[data-loader-id="' + uniqueId + '"]').removeClass(CLASSES.active);
  }

  /**
   * Remove old pagination and update with new content
   * @param {string} uniqueId - Container unique ID
   * @param {string} paginationHtml - New pagination HTML
   */
  function updatePagination(uniqueId, paginationHtml) {
    $(SELECTORS.pagination + '[data-unique-id="' + uniqueId + '"]').remove();
    if (paginationHtml) {
      $("#" + uniqueId).after(paginationHtml);
    }
  }

  /**
   * Perform AJAX request to load filtered posts
   * @param {Object} ajaxData - Data to send in AJAX request
   * @param {string} uniqueId - Container unique ID
   * @param {Object} options - Additional options
   */
  function loadFilteredPosts(ajaxData, uniqueId, options) {
    options = options || {};
    var shouldScroll = options.shouldScroll || false;

    $.ajax({
      type: "POST",
      url: all_menu_data.ajax_url,
      data: ajaxData,
      success: function (response) {
        hideLoading(uniqueId);

        if (response.success) {
          // Update posts
          $("#" + uniqueId).html(response.data.posts);
          restoreVisibility(uniqueId);

          // Append modals after the posts container
          if (response.data.modals) {
            $("#" + uniqueId).after(response.data.modals);
          }

          // Update pagination
          updatePagination(uniqueId, response.data.pagination);

          // Scroll to top if requested
          if (shouldScroll) {
            $("html, body").animate(
              { scrollTop: $("#" + uniqueId).offset().top - 100 },
              300,
            );
          }
        } else {
          console.error("AJAX error:", response.data);
        }
      },
      error: function (xhr, status, error) {
        hideLoading(uniqueId);
        console.error("AJAX error:", error);
        restoreVisibility(uniqueId);
      },
    });
  }

  // ========================================
  // URL STATE MANAGEMENT - INITIALIZATION
  // ========================================

  /**
   * Initialize filters from URL parameters on page load
   */
  function initializeFromUrl() {
    try {
      var params = getUrlParams();

      // Only initialize if URL has our filter parameters
      if (params.postType && params.taxonomy) {
        // Hide initial page 1 content that was server-rendered
        $(SELECTORS.postLoop).css("visibility", "hidden").css("opacity", "0");
        $(SELECTORS.pagination).remove();

        // Wait for DOM and event handlers to be ready
        setTimeout(function () {
          // Find the matching filter button for this URL state
          var matchingBtn = $(
            '[data-post-type="' +
              params.postType +
              '"][data-taxonomy="' +
              params.taxonomy +
              '"][data-term="' +
              params.term +
              '"]' +
              SELECTORS.filterBtn,
          ).first();

          if (matchingBtn.length > 0) {
            // Store the desired page and term for use during the filter click
            $(document).data(DATA_KEYS.initialPaged, params.paged);
            $(document).data(DATA_KEYS.urlTerm, params.term);

            // Trigger the filter button click to load the correct page
            matchingBtn.click();
          }
        }, 500); // 500ms to ensure DOM readiness
      }
    } catch (e) {
      console.warn("Error initializing from URL:", e);
    }
  }

  // Initialize on document ready
  if (document.readyState === "loading") {
    $(document).on("DOMContentLoaded", initializeFromUrl);
  } else {
    setTimeout(initializeFromUrl, 100);
  }

  // ========================================
  // FILTER BUTTON HANDLER
  // ========================================
  //
  // Handles taxonomy filter button clicks from the initial render.
  // Responsibilities:
  //  1. Update active button state
  //  2. Initiate loading indicator
  //  3. Determine page to load (from URL on first initialization, page 1 otherwise)
  //  4. Update browser URL with selected filter
  //  5. Trigger AJAX to fetch filtered posts and modals
  //  6. Clear temporary initialization state after completion
  //
  // Special Case - URL Restoration:
  //  When page loads with filter parameters in URL (browser back/reload),
  //  initializeFromUrl() stores the desired page in temporary data attributes.
  //  The filter handler checks for these and uses them instead of defaulting to page 1.

  $(document).on("click", SELECTORS.filterBtn + ":not(a)", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var btnData = getButtonData($btn);
    var initialPaged = $(document).data(DATA_KEYS.initialPaged);
    var urlTerm = $(document).data(DATA_KEYS.urlTerm);

    // Update active filter button state
    $btn.siblings(SELECTORS.filterBtn).removeClass(CLASSES.active);
    $btn.addClass(CLASSES.active);

    // Display loading indicator
    showLoading(btnData.uniqueId);

    // Determine page number:
    // If restoring from URL (initialPaged is set), use that page
    // Otherwise, start from page 1 for new filter selection
    var pageToUse = initialPaged ? initialPaged : 1;

    // Determine term:
    // If restoring from URL (urlTerm is set), use that term
    // Otherwise, use button's data-term attribute
    var termToUse = btnData.term || urlTerm || btnData.term;

    // Update browser URL to reflect current filter state
    updateUrlState(btnData.taxonomy, btnData.postType, termToUse, pageToUse);

    // Prepare AJAX request data
    var ajaxData = {
      action: "all_menu_filter",
      nonce: all_menu_data.nonce,
      taxonomy: btnData.taxonomy,
      term: termToUse,
      post_type: btnData.postType,
      orderby: btnData.orderby,
      order: btnData.order,
      posts_per_page: btnData.postsPerPage,
      paged: pageToUse,
      unique_id: btnData.uniqueId,
      enable_pagination: btnData.enablePagination,
    };

    // Clear old pagination to prevent duplicates after AJAX response
    $(
      SELECTORS.pagination + '[data-unique-id="' + btnData.uniqueId + '"]',
    ).remove();

    // Fetch filtered posts from backend
    loadFilteredPosts(ajaxData, btnData.uniqueId);

    // Clear temporary URL restoration data after first use
    if (initialPaged) {
      $(document).removeData(DATA_KEYS.initialPaged);
      $(document).removeData(DATA_KEYS.urlTerm);
    }
  });

  // ========================================
  // PAGINATION BUTTON HANDLER
  // ========================================
  //
  // Handles pagination button clicks to navigate through filtered results.
  // Responsibilities:
  //  1. Display loading indicator
  //  2. Update browser URL with new page number
  //  3. Trigger AJAX to fetch results for requested page
  //  4. Scroll to top of posts container after load (improved UX)
  //
  // Design Note:
  //  Pagination maintains current filter (term) while only changing page number.
  //  URL state remains consistent so browser back/forward navigation works correctly.

  $(document).on("click", SELECTORS.pageBtn, function (e) {
    e.preventDefault();

    var $btn = $(this);
    var btnData = getButtonData($btn);
    var page = $btn.data("page");

    // Display loading indicator during AJAX request
    showLoading(btnData.uniqueId);

    // Update URL with current page number while maintaining filter term
    updateUrlState(btnData.taxonomy, btnData.postType, btnData.term, page);

    // Prepare AJAX request data
    var ajaxData = {
      action: "all_menu_filter",
      nonce: all_menu_data.nonce,
      taxonomy: btnData.taxonomy,
      term: btnData.term,
      post_type: btnData.postType,
      orderby: btnData.orderby,
      order: btnData.order,
      posts_per_page: btnData.postsPerPage,
      paged: page,
      unique_id: btnData.uniqueId,
      enable_pagination: btnData.enablePagination,
    };

    // Clear old pagination to prevent duplicates after AJAX response
    $(
      SELECTORS.pagination + '[data-unique-id="' + btnData.uniqueId + '"]',
    ).remove();

    // Fetch paginated results and scroll to top for better UX
    loadFilteredPosts(ajaxData, btnData.uniqueId, { shouldScroll: true });
  });

  // ========================================
  // Make the featured items a slider on mobile.
  // ========================================
  jQuery(function ($) {
    $(".featured-slider").slick({
      mobileFirst: true,

      slidesToShow: 1,
      slidesToScroll: 1,

      adaptiveHeight: false,

      // centerMode: true,
      // centerPadding: '30px',

      autoplay: true,
      autoplaySpeed: 3000,

      arrows: false,
      dots: true,

      responsive: [
        {
          breakpoint: 767,
          settings: "unslick",
        },
      ],
    });
  });

  // ========================================
  // BROWSER HISTORY HANDLER
  // ========================================
  //
  // Handles browser back/forward navigation (popstate event).
  // Restores filter and pagination state when user clicks browser back/forward.
  //
  // Implementation:
  //  1. Retrieve state object saved during updateUrlState()
  //  2. Find matching filter button for the historical state
  //  3. Store page and term in temporary data for filter handler
  //  4. Trigger filter button click to load that state
  //
  // Technical Notes:
  //  - State object created by pushState is passed in e.state
  //  - Filter button match requires taxonomy, postType, AND term to be identical
  //  - Temporary storage prevents accidental page 1 reset on filter click

  $(window).on("popstate", function (e) {
    try {
      // Only proceed if popstate has valid state object with required data
      if (e.state && e.state.postType && e.state.taxonomy) {
        // Find the filter button matching this historical state
        // Selector matches: data-post-type, data-taxonomy, and data-term attributes
        var matchingBtn = $(
          '[data-post-type="' +
            e.state.postType +
            '"][data-taxonomy="' +
            e.state.taxonomy +
            '"][data-term="' +
            e.state.term +
            '"]' +
            SELECTORS.filterBtn,
        ).first();

        // If matching filter button found, restore that state
        if (matchingBtn.length > 0) {
          // Store the historical page and term for filter handler to use
          $(document).data(DATA_KEYS.initialPaged, e.state.paged);
          $(document).data(DATA_KEYS.urlTerm, e.state.term);

          // Trigger filter click to load and display the historical state
          matchingBtn.click();
        }
      }
    } catch (e) {
      console.warn("Error handling popstate:", e);
    }
  });
});

// Mobile Filter Dropdown Menu
(function ($) {
  $(document).on("change", ".menu-filter-dropdown", function () {
    const term = $(this).val();

    // Redirect for Catering
    if (term === "catering") {
      window.location.href = "https://gquebbq.com/catering-bbq/";
      return;
    }

    const $button = $('.all-menu-filter-btn[data-term="' + term + '"]');

    if ($button.length) {
      $button.trigger("click");
    } else {
      $('.all-menu-filter-btn[data-term=""]').trigger("click");
    }
  });
})(jQuery);
