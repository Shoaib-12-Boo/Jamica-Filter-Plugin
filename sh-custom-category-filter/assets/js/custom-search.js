jQuery(document).ready(function ($) {
    let debounceTimer;
    const $searchInput = $('#jad-custom-search-input');
    const $searchResults = $('#jad-search-results');
    const $searchLoader = $('.jad-search-loader');
    const $searchContainer = $('.jad-custom-search-container');

    if ($searchInput.length === 0) return;

    // Handle typing in the search box
    $searchInput.on('input', function () {
        const query = $(this).val().trim();

        clearTimeout(debounceTimer);

        if (query.length < 2) {
            $searchResults.hide().empty();
            $searchLoader.hide();
            return;
        }

        $searchLoader.show();

        debounceTimer = setTimeout(function () {
            $.ajax({
                url: jadSearchAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'jad_custom_search',
                    query: query
                },
                success: function (response) {
                    $searchLoader.hide();
                    if (response.success) {
                        $searchResults.html(response.data.html).show();
                    }
                },
                error: function () {
                    $searchLoader.hide();
                    console.log('Error fetching search results');
                }
            });
        }, 500); // 500ms debounce
    });

    // Close results when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest($searchContainer).length) {
            $searchResults.hide();
        }
    });

    // Re-open results if clicking input again and there are results
    $searchInput.on('focus', function () {
        if ($searchResults.children().length > 0 && $(this).val().trim().length >= 2) {
            $searchResults.show();
        }
    });
});