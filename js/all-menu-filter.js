jQuery(document).ready(function ($) {
  // Handle filter button clicks
  $(document).on("click", ".all-menu-filter-btn", function (e) {
    e.preventDefault();

    var btn = $(this);
    var taxonomy = btn.data("taxonomy");
    var term = btn.data("term");
    var postType = btn.data("post-type");
    var orderby = btn.data("orderby");
    var order = btn.data("order");
    var postsPerPage = btn.data("posts-per-page");
    var uniqueId = btn.data("unique-id");

    // Remove active class from all buttons in this group
    btn.siblings(".all-menu-filter-btn").removeClass("active");
    // Add active class to clicked button
    btn.addClass("active");

    // Send AJAX request
    $.ajax({
      type: "POST",
      url: all_menu_data.ajax_url,
      data: {
        action: "all_menu_filter",
        nonce: all_menu_data.nonce,
        taxonomy: taxonomy,
        term: term,
        post_type: postType,
        orderby: orderby,
        order: order,
        posts_per_page: postsPerPage,
        paged: 1,
        unique_id: uniqueId,
      },
      success: function (response) {
        if (response.success) {
          // Update the posts container
          $("#" + uniqueId).html(response.data.posts);
          // Update pagination - replace if exists, otherwise add
          var paginationDiv = $("#" + uniqueId).next(".all-menu-pagination");
          if (response.data.pagination) {
            if (paginationDiv.length > 0) {
              paginationDiv.replaceWith(response.data.pagination);
            } else {
              $("#" + uniqueId).after(response.data.pagination);
            }
          } else {
            // Remove old pagination if no new pagination
            paginationDiv.remove();
          }
        } else {
          console.error("AJAX error:", response.data);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", error);
      },
    });
  });

  // Handle pagination button clicks
  $(document).on("click", ".all-menu-page-btn", function (e) {
    e.preventDefault();

    var btn = $(this);
    var page = btn.data("page");
    var postType = btn.data("post-type");
    var taxonomy = btn.data("taxonomy");
    var term = btn.data("term");
    var orderby = btn.data("orderby");
    var order = btn.data("order");
    var postsPerPage = btn.data("posts-per-page");
    var uniqueId = btn.data("unique-id");

    // Send AJAX request
    $.ajax({
      type: "POST",
      url: all_menu_data.ajax_url,
      data: {
        action: "all_menu_filter",
        nonce: all_menu_data.nonce,
        taxonomy: taxonomy,
        term: term,
        post_type: postType,
        orderby: orderby,
        order: order,
        posts_per_page: postsPerPage,
        paged: page,
        unique_id: uniqueId,
      },
      success: function (response) {
        if (response.success) {
          // Update the posts container
          $("#" + uniqueId).html(response.data.posts);
          // Update pagination
          if (response.data.pagination) {
            $(".all-menu-pagination").replaceWith(response.data.pagination);
          }
          // Scroll to top of posts
          $("html, body").animate(
            { scrollTop: $("#" + uniqueId).offset().top - 100 },
            300,
          );
        } else {
          console.error("AJAX error:", response.data);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", error);
      },
    });
  });
});
