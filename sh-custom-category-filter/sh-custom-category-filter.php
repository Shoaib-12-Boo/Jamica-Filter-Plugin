<?php
/**
 * Plugin Name: Filters
 * Description: Modular plugin with separate Category and Brand filters. Behaving hierarchically.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Include the separate widget classes
require_once plugin_dir_path( __FILE__ ) . 'includes/class-category-filter-widget.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-brand-filter-widget.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-color-filter-widget.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-size-filter-widget.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-attribute-filter-widget.php';
//require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-search.php';

function custom_filter_widgets_assets() {
    ?>
    <style>
        .custom-cat-filter-container { border: 1px solid #e1e1e1; border-radius: 12px; background: #fff; overflow: hidden; margin-bottom: 20px; }
        .cat-filter-header { display: flex; justify-content: space-between; align-items: center; padding: 12px 18px; cursor: pointer; background: #F1F1F1; border-bottom: 1px solid #e1e1e1; }
        .header-text-wrapper h3 { margin: 0; font-size: 17px; color: #b52b27; font-weight: 800; position: relative; display: inline-block; border: none !important; box-shadow: none !important; text-decoration: none !important; padding: 0 !important; }
        .header-text-wrapper h3::before, .header-text-wrapper h3::after { display: none !important; content: none !important; border: none !important; box-shadow: none !important; background: transparent !important; }
        .cat-arrow { transition: transform 0.3s ease; display: inline-flex; align-items: center; }
        .is-open .cat-arrow { transform: rotate(180deg); }
        .cat-filter-body { max-height: 225px; overflow-y: auto; padding: 8px 0; display: none; }
        .jad-category-filter .cat-filter-body { max-height: 255px; }
        .cat-row { padding: 8px 18px; }
        .cat-label { display: flex; align-items: flex-start; cursor: pointer; gap: 12px; position: relative; }
        .cat-filter-input { position: absolute; opacity: 0; width: 0; height: 0; }
        .custom-checkmark { width: 12px; height: 12px; border: 1.5px solid #253b80; border-radius: 2px; flex-shrink: 0; margin-top: 3px; position: relative; }
        .cat-filter-input:checked ~ .custom-checkmark { background-color: #253b80; }
        .custom-checkmark:after { content: ""; position: absolute; display: none; left: 4px; top: 0.5px; width: 3.5px; height: 6px; border: solid white; border-width: 0 1.5px 1.5px 0; transform: rotate(45deg); }
        .cat-filter-input:checked ~ .custom-checkmark:after { display: block; }
        .cat-text { font-size: 14px; color: #333; font-weight: 500; line-height: 1.5; display: block; }
        .cat-text .count { color: #888; font-size: 12px; margin-left: 4px; }
        
        .no-child-msg { padding: 12px 15px; font-size: 13px; color: #777; font-style: italic; }
        
        .jad-clear-filter { font-size: 13px; font-weight: 700; color: #b52b27; text-decoration: underline; margin-left: auto; margin-right: 12px; cursor: pointer; transition: color 0.2s; z-index: 2; position: relative; }
        .jad-clear-filter:hover { color: #888; }
        
        .custom-cat-filter-container { margin-bottom: 12px; border: 1.5px solid #eee; border-radius: 12px; overflow: hidden; background: #fff; transition: box-shadow 0.3s; }
        .custom-cat-filter-container.is-open { box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        .header-text-wrapper { display: flex; align-items: center; width: 100%; }

        /* Global Clear Button */
        .jad-global-clear-wrapper { margin-bottom: 15px; display: flex; box-sizing: border-box; }
        .jad-global-clear-btn { 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            background: #b52b27; 
            color: #fff; 
            padding: 10px 16px; 
            border-radius: 8px; 
            font-size: 14px; 
            font-weight: 700; 
            text-decoration: none; 
            transition: background 0.3s; 
            cursor: pointer; 
            width: 100%; 
            justify-content: center; 
            box-sizing: border-box;
            border: none;
        }
        .jad-global-clear-btn:hover { background: #91201d; color: #fff; }
        .jad-global-clear-btn svg { width: 14px; height: 14px; }

        /* Centered Overlay Loader for PJAX */
        #jad-pjax-loader { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.7); z-index: 99999; justify-content: center; align-items: center; }
        .jad-spinner { width: 50px; height: 50px; border: 5px solid #f3f3f3; border-top: 5px solid #b52b27; border-radius: 50%; animation: jad-spin 1s linear infinite; }
        @keyframes jad-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .jad-mobile-close-header { position: sticky; top: 0; background: #fff; z-index: 101; display: none; justify-content: space-between; align-items: center; padding: 12px 15px; border-bottom: 1.5px solid #eee; gap: 10px; }
        .jad-mobile-close-btn { display: inline-flex; align-items: center; justify-content: center; background: none; border: none; cursor: pointer; padding: 0; color: #333; flex-shrink: 0; width: 24px; }
        .jad-mobile-close-btn svg { width: 22px; height: 22px; }
        
        .jad-mobile-close-title { letter-spacing: 1px; font-size: 20px; font-weight: 600; color: #333; margin: 0; white-space: nowrap; flex-grow: 1; text-align: center; }
        
        .jad-mobile-top-clear-all { background: #b52b27; color: #fff !important; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 700; text-decoration: none !important; display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0; min-width: 24px; margin-right: 0px; }
        .jad-mobile-top-clear-all svg { width: 12px; height: 12px; }

        .jad-mobile-sidebar-footer { position: fixed; bottom: 0; right: 0; width: 85%; max-width: 320px; background: white; padding: 15px 20px 15px 20px; text-align: center; z-index: 1000000; display: none; cursor: pointer; border: none;     border-radius: 0px !important;
        border-top: 1.5px solid #eee;}
        .jad-mobile-sidebar-footer .woocommerce-result-count { margin: 0;
    font-size: 14px;
    color: #fff !important;
    font-weight: 700;
    background: #b52b27;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 6px;
        width: 170px;
    margin-left: 20%;
    }
       	.jad-mobile-sidebar-footer:active { background: #eee; }

        .jad-mobile-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999998; opacity: 0; transition: opacity 0.5s ease; }
        body.jad-mobile-filter-open .jad-mobile-overlay { display: block; opacity: 1; }

        @media (max-width: 991px) {
            .jad-mobile-filter-btn-wrapper { display: block; }
            .jad-mobile-close-header { display: flex; }
            body.jad-mobile-filter-open .jad-mobile-sidebar-footer { display: block; }
            
            /* Hide the old global clear button in mobile sidebar context */
            .jad-filter-sidebar .jad-global-clear-wrapper { display: none !important; }

            /* Apply off-canvas styling immediately to avoid flash before JS logic adds jad-filter-sidebar */
            aside:has(.custom-cat-filter-container),
            #sidebar:has(.custom-cat-filter-container),
            .elementor-widget-sidebar:has(.custom-cat-filter-container),
            .sidebar:has(.custom-cat-filter-container),
            .col-right:has(.custom-cat-filter-container),
            #secondary:has(.custom-cat-filter-container) {
                position: fixed !important;
                top: 0 !important;
                right: 0 !important;
                left: auto !important;
                width: 85% !important;
                max-width: 320px !important;
                height: 100vh !important;
                background: #fff !important;
                z-index: 999999 !important;
                overflow-x: hidden !important;
                overflow-y: auto !important;
                display: block !important;
                transition: transform 0.5s ease-in-out !important;
                transform: translateX(100%) !important;
                padding: 0 !important;
                box-shadow: -2px 0 10px rgba(0,0,0,0.1) !important;
                margin: 0 !important;
            }

            .jad-filter-sidebar {
                position: fixed !important;
                top: 0 !important;
                right: 0 !important;
                left: auto !important;
                width: 85% !important;
                max-width: 320px !important;
                height: 100vh !important;
                background: #fff !important;
                z-index: 999999 !important;
                overflow-x: hidden !important;
                overflow-y: auto !important;
                display: block !important;
                transition: transform 0.5s ease-in-out !important;
                transform: translateX(100%) !important;
                padding: 0 !important;
                box-shadow: -2px 0 10px rgba(0,0,0,0.1) !important;
                margin: 0 !important;
            }
            
            .jad-filter-sidebar .custom-cat-filter-container { margin: 15px !important; }
            
            body.jad-mobile-filter-open aside:has(.custom-cat-filter-container),
            body.jad-mobile-filter-open #sidebar:has(.custom-cat-filter-container),
            body.jad-mobile-filter-open .elementor-widget-sidebar:has(.custom-cat-filter-container),
            body.jad-mobile-filter-open .sidebar:has(.cust
            om-cat-filter-container),
            body.jad-mobile-filter-open .col-right:has(.custom-cat-filter-container),
            body.jad-mobile-filter-open #secondary:has(.custom-cat-filter-container) {
                transform: translateX(0) !important;
            }

            body.jad-mobile-filter-open .jad-filter-sidebar { transform: translateX(0) !important; }
            
            body.jad-mobile-filter-open { overflow: hidden; position: fixed; width: 100%; }
            .header-text-wrapper h3 { font-size: 15px; }
            .cat-text { font-size: 12px; }
        }

        /* --- Ordering Mobile Row Styles --- */
        .sh-ordering-mobile-row .jad-mobile-filter-btn { display: none; }
        .sh-ordering-mobile-row .jad-shortcode-clear-wrapper { display: none !important; }

        @media (max-width: 991px) {
            .sh-ordering-mobile-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; width: 100%; margin-bottom: 20px; flex-wrap: nowrap; }
            .sh-ordering-mobile-row > * { flex: 1; margin: 0 !important; width: auto !important; min-width: 0; }
            .sh-ordering-mobile-row .woocommerce-ordering { flex: 1; }
            .sh-ordering-mobile-row .orderby { width: 100% !important; padding: 8px 5px !important; height: auto !important; font-size: 13px !important; border-radius: 50px !important; }
            
            .sh-ordering-mobile-row .jad-mobile-filter-btn { display: flex; align-items: center; justify-content: center; gap: 4px; padding: 8px 5px; font-size: 13px; white-space: nowrap; border-radius: 50px; border: 1.5px solid gray; background: #fff; color: #333; height: auto; }
            .sh-ordering-mobile-row .jad-mobile-filter-btn svg { width: 14px; height: 14px; }
            
            .sh-ordering-mobile-row .jad-shortcode-clear-wrapper { display: flex !important; margin: 0 !important; }
            .sh-ordering-mobile-row .jad-shortcode-clear-wrapper a { padding: 6px; display: flex; align-items: center; justify-content: center; gap: 4px; padding: 5.5px 10px; font-size: 13px; white-space: nowrap; border-radius: 50px; background: #b52b27; color: #fff !important; height: auto; text-decoration: none; border: 1.5px solid #b52b27; width: 100%; box-sizing: border-box; margin-right: 0px; }
            .sh-ordering-mobile-row .jad-shortcode-clear-wrapper a svg { width: 14px; height: 14px; display: none; } /* Hide the default SVG here too */
        }

        @media screen and (max-width: 768px) {
            #sidebar .primary {
                    border: none !important;
    padding: 0px !important;
            }

            .widget_custom_woo_cat_filter{
                margin-bottom: 9px !important;
            }
            .widget {
    margin-bottom: 0.618em !important;
}

}
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Dropdown Toggle (Works for any filter container with a header)
        $(document).on('click', '.cat-filter-header', function() {
            var $container = $(this).closest('.custom-cat-filter-container');
            $container.toggleClass('is-open');
            $(this).siblings('.cat-filter-body').slideToggle();
        });
        
        // Ensure loader exists in DOM
        if ($('#jad-pjax-loader').length === 0) {
            $('body').append('<div id="jad-pjax-loader"><div class="jad-spinner"></div></div>');
        }

        // --- GLOBAL CLEAR LOGIC ---
        function jadRenderGlobalClear() {
            var urlParams = new URLSearchParams(window.location.search);
            // Check if there are any filter queries
            var hasFilters = false;
            urlParams.forEach(function(value, key) {
                if (key === 'catgorie' || key === 'product_brand' || key === 'brand' || key.startsWith('filter_') || key.startsWith('query_type_')) {
                    hasFilters = true;
                }
            });
            
            $('.jad-global-clear-wrapper').remove();
            
            if (hasFilters) {
                var clearPath = window.location.pathname;
                var globalClearHtml = '<div class="jad-global-clear-wrapper">' +
                    '<a href="' + clearPath + '" class="jad-clear-filter jad-global-clear-btn jad-mobile-top-clear-all">' +
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' +
                    'Clear All</a></div>';
                
                var $firstWidget = $('.custom-cat-filter-container').first();
                if ($firstWidget.length > 0) {
                    $firstWidget.before(globalClearHtml);
                }
            }
        }
        
        // Initial render
        jadRenderGlobalClear();
        // --- END GLOBAL CLEAR ---

        // Helper function to find the most expansive container for the archive
        function jadGetMainWrapper($context) {
            var $wrapper = $context.find('.elementor-location-archive').first();
            if ($wrapper.length === 0) $wrapper = $context.find('main.site-main').first();
            if ($wrapper.length === 0) $wrapper = $context.find('#main').first();
            if ($wrapper.length === 0) $wrapper = $context.find('#content').first();
            if ($wrapper.length === 0) $wrapper = $context.find('.woocommerce .products, .elementor-widget-woocommerce-products .products, ul.products').first();
            return $wrapper;
        }

        function jadPjaxNavigate(url, pushState, triggerId) {
            $('#jad-pjax-loader').css('display', 'flex');
            
            // Save scroll positions
            var windowScroll = $(window).scrollTop();
            var widgetScrolls = {};
            $('.cat-filter-body:visible').each(function() {
                var widgetId = $(this).closest('.custom-cat-filter-container').attr('id');
                if (widgetId) {
                    widgetScrolls[widgetId] = $(this).scrollTop();
                }
            });
            
            var $productsWrapper = jadGetMainWrapper($(document));
            var $widgets = $('.custom-cat-filter-container'); // Select all filter widgets
            
            $widgets.each(function() {
                $(this).css('opacity', '0.5');
            });
            
            if ($productsWrapper.length > 0) {
                 $productsWrapper.css('opacity', '0.5');
            }
            
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    var $html = $('<div>').append($.parseHTML(response));
                    var newProducts = jadGetMainWrapper($html);
                    
                    if (newProducts.length === 0) {
                        var infoMessage = $html.find('.woocommerce-info');
                        if(infoMessage.length > 0) {
                            if ($productsWrapper.length > 0) {
                                $productsWrapper.html(infoMessage.prop('outerHTML'));
                            }
                        } else {
                            if ($productsWrapper.length > 0) {
                                $productsWrapper.html('<p class="woocommerce-info">No products were found matching your selection.</p>');
                            }
                        }
                    } else {
                         if ($productsWrapper.length > 0) {
                             if ($productsWrapper[0].tagName === newProducts[0].tagName && $productsWrapper[0].className === newProducts[0].className) {
                                  $productsWrapper.html(newProducts.html());
                             } else {
                                  $productsWrapper.replaceWith(newProducts);
                                  $productsWrapper = newProducts; 
                             }
                         }
                    }
                    
                    $widgets.each(function() {
                        var widgetId = $(this).attr('id');
                        if (widgetId) {
                            var idSelector = '#' + widgetId;
                            var newWidget = $html.find(idSelector);
                            if (newWidget.length > 0 && $(idSelector).length > 0) {
                                if ($(idSelector).closest($productsWrapper).length === 0) {
                                    $(idSelector).replaceWith(newWidget);
                                    
                                    var $newWidget = $(idSelector);
                                    
                                    // If this widget contains the trigger element, scroll to it
                                    if (triggerId && $newWidget.find('#' + triggerId).length > 0) {
                                         var $el = $newWidget.find('#' + triggerId);
                                         var $body = $el.closest('.cat-filter-body');
                                         var $row = $el.closest('.cat-row');
                                         if ($body.length && $row.length) {
                                             var scrollOffset = $row.offset().top - $body.offset().top + $body.scrollTop();
                                             $body.scrollTop(scrollOffset);
                                         }
                                    } else if (widgetScrolls[widgetId] !== undefined) {
                                        $newWidget.find('.cat-filter-body').scrollTop(widgetScrolls[widgetId]);
                                    }
                                }
                            } else if ($(idSelector).length > 0) {
                                $(idSelector).css('opacity', '1');
                            }
                        }
                    });
                    
                    if ($productsWrapper.length > 0) {
                        $productsWrapper.css('opacity', '1');
                    }
                    
                    // Restore window scroll position
                    $(window).scrollTop(windowScroll);
                    
                    $('#jad-pjax-loader').css('display', 'none');
                    
                    if (pushState) {
                        window.history.pushState({ path: url }, '', url);
                    }
                    
                    jadRenderGlobalClear();
                },
                error: function() {
                    $('#jad-pjax-loader').css('display', 'none');
                    if (pushState) {
                        window.location.href = url;
                    } else {
                        window.location.reload();
                    }
                }
            });
        }
        // Redirect or PJAX on standard Category or Brand Click
        $(document).on('change', '.cat-filter-input:not(.cat-ajax-input)', function(e) {
            var url = $(this).data('url');
            var is_cat_redirect_input = $(this).hasClass('cat-redirect-input');
            var triggerId = $(this).attr('id');
            
            if (is_cat_redirect_input) {
                e.preventDefault();
                jadPjaxNavigate(url, true, triggerId);
            } else {
                window.location.href = url;
            }
        });

        // Add popstate listener for back button PJAX
        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.path) {
                jadPjaxNavigate(window.location.href, false);
            }
        });

        // Handle Clear Filter click
        $(document).on('click', '.jad-clear-filter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var url = $(this).attr('href');
            if (url) {
                jadPjaxNavigate(url, true);
            }
        });

        // Handle WooCommerce Sorting via PJAX
        $(document).on('change', '.woocommerce-ordering .orderby', function(e) {
            e.preventDefault();
            var $form = $(this).closest('form');
            var orderby = $(this).val();
            var url = new URL(window.location.href);
            
            // Set the orderby parameter
            url.searchParams.set('orderby', orderby);
            
            // Also ensure paged is reset to 1 when sorting
            url.searchParams.set('paged', '1');
            
            jadPjaxNavigate(url.toString(), true);
        });

        // AJAX Filtering for lowest level categories
        $(document).on('change', '.cat-ajax-input', function(e) {
            e.preventDefault();
            
            var selectedCats = [];
            $('.cat-ajax-input:checked').each(function() {
                selectedCats.push($(this).data('term-id'));
            });
            
            // Current category fallback if nothing is checked
            var parentCat = 0; <?php if(is_product_category()) { $obj = get_queried_object(); echo "parentCat = {$obj->term_id};"; } ?>
            
            // Add loading state to products wrapper
            var $productsWrapper = $('ul.products');
            if ($productsWrapper.length === 0) {
                // Fallback if ul.products doesn't exist, try typical Elementor/Woo wrappers
                $productsWrapper = $('.woocommerce .products, .elementor-widget-woocommerce-products .products');
            }
            $productsWrapper.css('opacity', '0.5');

            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'jad_ajax_filter_products',
                    category_ids: selectedCats,
                    parent_cat: parentCat
                },
                success: function(response) {
                    $productsWrapper.css('opacity', '1');
                    if(response.success) {
                        $productsWrapper.html(response.data.html);
                    }
                },
                error: function() {
                    $productsWrapper.css('opacity', '1');
                    console.log('Error fetching products');
                }
            });
        });

        // History Fix
        window.addEventListener('pageshow', function(event) {
            const currentUrl = window.location.href;
            const isFilterPage = currentUrl.includes('/product-category/') || 
                                 currentUrl.includes('/product_brand/') || 
                                 currentUrl.includes('/brand/') ||
                                 currentUrl.includes('filter_colour=') || 
                                 currentUrl.includes('/pa_colour/') ||
                                 currentUrl.includes('filter_size=') || 
                                 currentUrl.includes('/pa_size/') ||
                                 currentUrl.includes('taxonomy=pa_colour') ||
                                 currentUrl.includes('taxonomy=pa_size');
                                 
            // If the user navigates away from a filter page, uncheck everything so the back button is clean
            if (!isFilterPage) {
                $('.cat-filter-input').prop('checked', false);
            } else {
                // Restore checked state based on HTML attributes if navigating back
                // This prevents the JS from accidentally unchecking what PHP just rendered
                $('.cat-filter-input').each(function() {
                    if ($(this).attr('checked')) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
            }
        });

        // --- Mobile Filter JS ---
        function jadInitMobileFilter() {
            var $widgets = $('.custom-cat-filter-container');
            if ($widgets.length > 0) {
                // Find the containing sidebar or create a wrapper class
                var $sidebar = $widgets.first().closest('aside, #sidebar, .elementor-widget-sidebar, .sidebar, .col-right, #secondary');
                if ($sidebar.length === 0) {
                    $sidebar = $widgets.parent(); // fallback
                }
                
                $sidebar.addClass('jad-filter-sidebar');
                
                // Add header if it doesn't exist
                if ($sidebar.find('.jad-mobile-close-header').length === 0) {
                    var closeBtnHtml = '<button class="jad-mobile-close-btn" aria-label="Close Filters"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>';
                    var clearUrl = window.location.pathname;
                    var clearAllBtnHtml = '<a href="' + clearUrl + '" class="jad-mobile-top-clear-all jad-clear-filter">Clear All</a>';
                    
                    $sidebar.prepend('<div class="jad-mobile-close-header">' + closeBtnHtml + '<h3 class="jad-mobile-close-title">FILTER</h3>' + clearAllBtnHtml + '</div>');
                }
                
                // Always update "Clear All" visibility based on current filters
                var urlParams = new URLSearchParams(window.location.search);
                var hasFilters = false;
                urlParams.forEach(function(value, key) {
                    if (key === 'catgorie' || key === 'product_brand' || key === 'brand' || key.startsWith('filter_') || key.startsWith('query_type_')) {
                        hasFilters = true;
                    }
                });
                
                if (hasFilters) {
                    $sidebar.find('.jad-mobile-top-clear-all').show();
                } else {
                    $sidebar.find('.jad-mobile-top-clear-all').hide();
                }
                
                function formatResultCount(rawText) {
                    if (!rawText) return '';
                    // Remove sorting text like "Sorted by popularity"
                    rawText = rawText.replace(/Sorted by [^\s]+/gi, '').trim();
                    // Extract total results number
                    var match = rawText.match(/of\s+([\d,]+)\s+results/i);
                    if (match) {
                        return 'Shows ' + match[1] + ' results';
                    }
                    match = rawText.match(/all\s+([\d,]+)\s+results/i);
                    if (match) {
                        return 'Shows ' + match[1] + ' results';
                    }
                    
                    // Update the text for a single result
                    if (rawText.toLowerCase().includes('showing the single result')) {
                        return 'Shows single result';
                    }
                    
                    return rawText;
                }

                // Add footer (result count button) if it doesn't exist
                if ($('.jad-mobile-sidebar-footer').length === 0) {
                    var $resCount = $('.woocommerce-result-count').not('.jad-mobile-sidebar-footer .woocommerce-result-count').first();
                    var resultCountText = formatResultCount($resCount.text());
                    
                    $('body').append('<button class="jad-mobile-sidebar-footer"><p class="woocommerce-result-count">' + resultCountText + '</p></button>');
                } else {
                    // Update result count text if footer already exists (on AJAX update)
                    var $resCount = $('.woocommerce-result-count').not('.jad-mobile-sidebar-footer .woocommerce-result-count').first();
                    if ($resCount.length > 0) {
                        $('.jad-mobile-sidebar-footer .woocommerce-result-count').text(formatResultCount($resCount.text()));
                    }
                }
                
                // Add overlay to body if doesn't exist
                if ($('.jad-mobile-overlay').length === 0) {
                    $('body').append('<div class="jad-mobile-overlay"></div>');
                }
            }
        }

        jadInitMobileFilter();

        $(document).on('click', '.jad-mobile-filter-btn', function(e) {
            e.preventDefault();
            $('body').addClass('jad-mobile-filter-open');
        });

        $(document).on('click', '.jad-mobile-close-btn, .jad-mobile-overlay, .jad-mobile-sidebar-footer', function(e) {
            e.preventDefault();
            $('body').removeClass('jad-mobile-filter-open');
        });

        // Intercept PJAX success to re-initialize mobile filters if replaced
        $(document).ajaxComplete(function(event, xhr, settings) {
             setTimeout(jadInitMobileFilter, 100);
        });

    });
    </script>
    <?php
}
add_action('wp_head', 'custom_filter_widgets_assets');

add_action('widgets_init', function(){
    register_widget('Custom_Woo_Category_Filter_Widget');
    register_widget('Custom_Woo_Brand_Filter_Widget');
    register_widget('Custom_Woo_Color_Filter_Widget');
    register_widget('Custom_Woo_Size_Filter_Widget');
    register_widget('Custom_Woo_Attribute_Filter_Widget');
});

// Mobile toggle button hook
// add_action('woocommerce_before_shop_loop', 'jad_mobile_filter_toggle_button', 15);
function jad_mobile_filter_toggle_button() {
    echo '<div class="jad-mobile-filter-btn-wrapper">';
    echo '<button class="jad-mobile-filter-btn">';
    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>';
    echo ' Filters';
    echo '</button>';
    echo '</div>';
}

// Shortcode for Mobile Filter Button
add_shortcode('jad_mobile_filter_button', 'jad_mobile_filter_button_shortcode');
function jad_mobile_filter_button_shortcode() {
    ob_start();
    ?>
    <button class="jad_shortcode_filter_btn jad-mobile-filter-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
        Filters
    </button>
    <?php
    return ob_get_clean();
}

// Shortcode for Clear All Filters Button
add_shortcode('jad_clear_all_filters_button', 'jad_clear_all_filters_button_shortcode');
function jad_clear_all_filters_button_shortcode() {
    $urlParams = $_GET;
    $hasFilters = false;
    $filter_keys = ['catgorie', 'product_brand', 'brand'];
    
    foreach ($urlParams as $key => $value) {
        if (in_array($key, $filter_keys) || strpos($key, 'filter_') === 0 || strpos($key, 'query_type_') === 0) {
            $hasFilters = true;
            break;
        }
    }

    if (!$hasFilters) {
        return '';
    }

    $clearPath = strtok($_SERVER["REQUEST_URI"], '?');
    ob_start();
    ?>
    <div class="jad-shortcode-clear-wrapper">
        <a href="<?php echo esc_url($clearPath); ?>" class="jad-clear-filter jad-global-clear-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            Clear All Filter
        </a>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Fix WooCommerce queried object hijacking when a brand filter is active.
 * This ensures the page title remains the category and child categories show.
 */
add_action( 'wp', 'jad_fix_brand_query_hijack' );
function jad_fix_brand_query_hijack() {
    global $wp_query;
    
    // Check if both product_cat and product_brand are being queried
    $product_cat = $wp_query->get( 'product_cat' );
    $product_brand = $wp_query->get( 'product_brand' );
    
    if ( !empty($product_cat) && !empty($product_brand) ) {
        // WordPress might have chosen product_brand as the main queried object, mutating the page title and hiding subcategories.
        // We force it back to the product_cat term.
        $cat_term = get_term_by( 'slug', $product_cat, 'product_cat' );
        if ( $cat_term && !is_wp_error($cat_term) ) {
            $wp_query->queried_object = $cat_term;
            $wp_query->queried_object_id = $cat_term->term_id;
            $wp_query->is_tax = true;
        }
    }
}

// Disable showing child category products on parent category pages
add_action( 'pre_get_posts', 'jad_isolate_category_products', 99 );
function jad_isolate_category_products( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    
    // Check if catgorie is applied
    $filter_cats = isset($_GET['catgorie']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['catgorie'])))) : array();
    
    // We want to apply this logic if we are on a product category page OR if the product_cat query is present (which happens when brands are filtered on a category page).
    $is_cat_page = is_product_category();
    $cat_slug = $query->get('product_cat');
    
    if ( $is_cat_page || !empty($cat_slug) ) {
        $tax_query = $query->get( 'tax_query' );
        if ( ! is_array( $tax_query ) ) {
            $tax_query = array();
        }
        
        $term = get_queried_object();
        $term_id = 0;
        
        // Find the active taxonomy object depending on priorities
        if ( $is_cat_page && $term && isset( $term->term_id ) ) {
             $term_id = $term->term_id;
        } elseif ( !empty($cat_slug) ) {
             $term_obj = get_term_by('slug', $cat_slug, 'product_cat');
             if ( $term_obj && !is_wp_error($term_obj) ) {
                  $term_id = $term_obj->term_id;
             }
        }
        
        if ( $term_id ) {
            // Find existing product_cat and remove it, we will rebuild it
            foreach ( $tax_query as $key => $tq ) {
                if ( isset( $tq['taxonomy'] ) && 'product_cat' === $tq['taxonomy'] ) {
                    unset( $tax_query[ $key ] );
                }
            }
            
            // Re-index array
            $tax_query = array_values($tax_query);
            
            if ( !empty($filter_cats) ) {
                // If they selected child categories, show products from those selected child categories
                $tax_query[] = array(
                    'taxonomy'         => 'product_cat',
                    'field'            => 'slug',
                    'terms'            => $filter_cats,
                    'include_children' => true,
                    'operator'         => 'IN',
                );
            } else {
                // If no subcategories selected, behave normally by showing all children
                $tax_query[] = array(
                    'taxonomy'         => 'product_cat',
                    'field'            => 'term_id',
                    'terms'            => $term_id,
                    'include_children' => true,
                    'operator'         => 'IN',
                );
            }
            
            $query->set( 'tax_query', $tax_query );
        }
    } elseif ( ( is_shop() || is_product_tag() || is_product_taxonomy() || is_search() ) && !empty($filter_cats) ) {
        // Handling the edge case where they might search and filter shop
        $tax_query = $query->get( 'tax_query' );
        if ( ! is_array( $tax_query ) ) {
            $tax_query = array();
        }
        $tax_query[] = array(
            'taxonomy'         => 'product_cat',
            'field'            => 'slug',
            'terms'            => $filter_cats,
            'include_children' => true,
            'operator'         => 'IN',
        );
        $query->set( 'tax_query', $tax_query );
    }
}

// AJAX Handler for lowest level category filtering
add_action('wp_ajax_jad_ajax_filter_products', 'jad_ajax_filter_products_handler');
add_action('wp_ajax_nopriv_jad_ajax_filter_products', 'jad_ajax_filter_products_handler');

function jad_ajax_filter_products_handler() {
    $category_ids = isset($_POST['category_ids']) ? array_map('intval', $_POST['category_ids']) : array();
    $parent_cat = isset($_POST['parent_cat']) ? intval($_POST['parent_cat']) : 0;
    
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Temporarily show all matching for the AJAX update
    );

    if ( !empty($category_ids) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $category_ids,
                'operator' => 'IN',     // If they select multiple, show products in ANY of those categories
                'include_children' => true,
            ),
        );
    } elseif ($parent_cat > 0) {
        // If nothing is checked, fallback to the parent category
         $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $parent_cat,
                'operator' => 'IN',
                'include_children' => true,
            ),
        );
    }

    $query = new WP_Query( $args );
    
    ob_start();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            wc_get_template_part( 'content', 'product' );
        }
    } else {
        echo '<p class="woocommerce-info">No products were found matching your selection.</p>';
    }

    wp_reset_postdata();
    $html = ob_get_clean();

    wp_send_json_success( array( 'html' => $html ) );
}

/**
 * Helper to get terms for a taxonomy that are attached to products in the current category
 * Incorporating the 'catgorie' filter so counts are correct
 */
function jad_get_terms_for_current_category( $taxonomy, $parent_to_show = 0 ) {
    $cat_slug = get_query_var('product_cat');
    
    // Determine the context robustly
    $is_featured_page = (is_page('featured-products') || strpos($_SERVER['REQUEST_URI'], '/featured-products') !== false);
    $is_sale_page = (is_page('sale-items') || strpos($_SERVER['REQUEST_URI'], '/sale-items') !== false);
    $is_tag_page = is_product_tag();
    $is_brand_page = is_tax('product_brand') || is_tax('brand');
    
    // We only need a strict category context if we are heavily filtering by current subcategories
    if ( is_product_category() ) {
         $current_cat = get_queried_object();
    } elseif ( !empty($cat_slug) ) {
         $current_cat = get_term_by('slug', $cat_slug, 'product_cat');
    } else {
         $current_cat = false;
    }

    // We always want to join products within the base category of the page (if on a category page)
    // regardless of whether specific category filters (catgorie) are applied.
    // This ensures sibling categories show up in the filter list.
    $cat_ids = array();
    if ( $current_cat && isset($current_cat->term_id) ) {
        $child_ids = get_term_children( $current_cat->term_id, 'product_cat' );
        if ( !is_wp_error($child_ids) ) {
            $cat_ids = array_merge($cat_ids, $child_ids);
        }
        $cat_ids[] = $current_cat->term_id;
    }

    global $wpdb;
    
    $parent_clause = '';
    if ( $parent_to_show > 0 ) {
        $parent_clause = $wpdb->prepare( " AND tt.parent = %d ", $parent_to_show );
    }
    
    $join_clause = '';
    $where_clause = '';
    
    // If we are on a specific category, filter by that category and its children
    if ( $current_cat && !empty($cat_ids) ) {
        $cat_ids_str = implode(',', array_map('intval', $cat_ids));
        $join_clause .= " INNER JOIN {$wpdb->term_relationships} AS tr2 ON tr2.object_id = p.ID
                          INNER JOIN {$wpdb->term_taxonomy} AS tt2 ON tt2.term_taxonomy_id = tr2.term_taxonomy_id ";
        $where_clause .= " AND tt2.taxonomy = 'product_cat' AND tt2.term_id IN ($cat_ids_str) ";
    }
    
    // If we are on the featured products page, filter by visibility
    if ( $is_featured_page ) {
        $featured_term = get_term_by('slug', 'featured', 'product_visibility');
        if ( !$featured_term ) {
            $featured_term = get_term_by('name', 'featured', 'product_visibility');
        }
        if ( $featured_term && !is_wp_error($featured_term) ) {
            $join_clause .= " INNER JOIN {$wpdb->term_relationships} AS tr_feat ON tr_feat.object_id = p.ID
                              INNER JOIN {$wpdb->term_taxonomy} AS tt_feat ON tt_feat.term_taxonomy_id = tr_feat.term_taxonomy_id ";
            $where_clause .= $wpdb->prepare(" AND tt_feat.taxonomy = 'product_visibility' AND tt_feat.term_id = %d ", $featured_term->term_id);
        }
    }
    
    // If we are on the sale items page, filter by sale products
    if ( $is_sale_page ) {
        $join_clause .= " INNER JOIN {$wpdb->postmeta} AS pm_sale ON pm_sale.post_id = p.ID ";
        $where_clause .= " AND pm_sale.meta_key = '_sale_price' AND pm_sale.meta_value > 0 ";
    }
    
    // If we are on a product tag page, filter by the current tag
    if ( $is_tag_page ) {
        $current_tag = get_queried_object();
        if ( $current_tag && isset($current_tag->term_id) ) {
            $join_clause .= " INNER JOIN {$wpdb->term_relationships} AS tr_tag ON tr_tag.object_id = p.ID
                              INNER JOIN {$wpdb->term_taxonomy} AS tt_tag ON tt_tag.term_taxonomy_id = tr_tag.term_taxonomy_id ";
            $where_clause .= $wpdb->prepare(" AND tt_tag.taxonomy = 'product_tag' AND tt_tag.term_id = %d ", $current_tag->term_id);
        }
    }
    
    // If we are on a brand archive page, filter by the current brand
    if ( $is_brand_page ) {
        $current_brand = get_queried_object();
        if ( $current_brand && isset($current_brand->term_id) ) {
            $join_clause .= " INNER JOIN {$wpdb->term_relationships} AS tr_brand_page ON tr_brand_page.object_id = p.ID
                              INNER JOIN {$wpdb->term_taxonomy} AS tt_brand_page ON tt_brand_page.term_taxonomy_id = tr_brand_page.term_taxonomy_id ";
            $where_clause .= $wpdb->prepare(" AND tt_brand_page.taxonomy = %s AND tt_brand_page.term_id = %d ", $current_brand->taxonomy, $current_brand->term_id);
        }
    }

    // Include WooCommerce Catalog Visibility and Out of Stock checks
    $hide_out_of_stock = get_option( 'woocommerce_hide_out_of_stock_items' );
    if ( $hide_out_of_stock === 'yes' ) {
        $join_clause .= " INNER JOIN {$wpdb->postmeta} AS pm_stock ON pm_stock.post_id = p.ID ";
        $where_clause .= " AND pm_stock.meta_key = '_stock_status' AND pm_stock.meta_value = 'instock' ";
    }
    
    // Exclude hidden products (exclude-from-catalog / exclude-from-search)
    $term_exclude = get_term_by( 'slug', 'exclude-from-catalog', 'product_visibility' );
    if ( $term_exclude && !is_wp_error($term_exclude) ) {
        $exclude_id = $term_exclude->term_taxonomy_id;
        $join_clause .= " LEFT JOIN {$wpdb->term_relationships} AS tr_vis ON tr_vis.object_id = p.ID AND tr_vis.term_taxonomy_id = {$exclude_id} ";
        $where_clause .= " AND tr_vis.object_id IS NULL ";
    }
    
    // Process active $_GET category, brand, and attribute filters to restrict the term counts (Faceted Search)
    $active_tax_filters = array();
    
    // 1. Categories
    $filter_cats_get = isset($_GET['catgorie']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['catgorie'])))) : array();
    if ( !empty($filter_cats_get) && $taxonomy !== 'product_cat' ) {
        $active_tax_filters['product_cat'] = $filter_cats_get;
    }
    
    // 2. Brands (Support both product_brand and brand slugs)
    $filter_brands_get = isset($_GET['product_brand']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['product_brand'])))) : array();
    if ( empty($filter_brands_get) && isset($_GET['brand']) ) {
        $filter_brands_get = array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['brand']))));
    }

    if ( !empty($filter_brands_get) && $taxonomy !== 'product_brand' && $taxonomy !== 'brand' ) {
        $active_tax_filters['product_brand'] = $filter_brands_get;
    }

    // 3. Attributes
    foreach ( $_GET as $key => $value ) {
        if ( strpos( $key, 'filter_' ) === 0 && $key !== 'filter_product_brand' && $key !== 'filter_brand' ) {
            $attr_taxonomy = 'pa_' . str_replace( 'filter_', '', $key );
            $terms_get = array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($value))));
            if ( !empty($terms_get) && $taxonomy !== $attr_taxonomy ) {
                $active_tax_filters[$attr_taxonomy] = $terms_get;
            }
        }
    }
    
    // Inject the active tax filters into the SQL query
    $join_count = 3; // Start after tr2
    foreach ( $active_tax_filters as $tax_slug => $term_slugs ) {
        $term_slugs_sql = "'" . implode("','", array_map('esc_sql', $term_slugs)) . "'";
        $tr_alias = "tr{$join_count}";
        $tt_alias = "tt{$join_count}";
        $t_alias = "t{$join_count}";
        
        $join_clause .= " INNER JOIN {$wpdb->term_relationships} AS {$tr_alias} ON {$tr_alias}.object_id = p.ID
                          INNER JOIN {$wpdb->term_taxonomy} AS {$tt_alias} ON {$tt_alias}.term_taxonomy_id = {$tr_alias}.term_taxonomy_id
                          INNER JOIN {$wpdb->terms} AS {$t_alias} ON {$t_alias}.term_id = {$tt_alias}.term_id ";
                          
        $where_clause .= $wpdb->prepare(" AND {$tt_alias}.taxonomy = %s AND {$t_alias}.slug IN ($term_slugs_sql) ", $tax_slug);
        
        $join_count++;
    }
    
    // Grouping by tax term to get a direct product count that actually intersects with the selected products
    $query = "
        SELECT t.term_id, COUNT(DISTINCT p.ID) as filtered_count
        FROM {$wpdb->terms} AS t
        INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
        INNER JOIN {$wpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->posts} AS p ON p.ID = tr.object_id
        {$join_clause}
        WHERE tt.taxonomy = %s
        {$parent_clause}
        {$where_clause}
        AND p.post_type = 'product'
        AND p.post_status = 'publish'
        GROUP BY t.term_id
        ORDER BY t.name ASC
    ";
    
    $results = $wpdb->get_results( $wpdb->prepare( $query, $taxonomy ) );
    
    $terms = array();
    foreach ( $results as $row ) {
        $term = get_term( $row->term_id, $taxonomy );
        if ( ! is_wp_error( $term ) && $term ) {
            // Overwrite the native global count with our deeply filtered count
            $term->count = $row->filtered_count;
            $terms[] = $term;
        }
    }
    
    return $terms;
}

/**
 * Helper to build the toggle URL for attributes/brands
 */
function jad_get_filter_toggle_url( $taxonomy, $term_slug, $query_key = '' ) {
    if ( empty( $query_key ) ) {
        if ( strpos( $taxonomy, 'pa_' ) === 0 ) {
            $query_key = 'filter_' . substr( $taxonomy, 3 );
        } else {
            $query_key = $taxonomy;
        }
    }

    $current_val = isset( $_GET[ $query_key ] ) ? sanitize_text_field( $_GET[ $query_key ] ) : '';
    
    $is_currently_checked = false;
    if ( $current_val === $term_slug ) {
        $is_currently_checked = true;
    } elseif ( is_tax( $taxonomy ) && get_queried_object() && isset(get_queried_object()->slug) && get_queried_object()->slug === $term_slug ) {
        $is_currently_checked = true;
    }
    
    $is_unchecking_current_tax = false;
    if ( is_tax( $taxonomy ) && get_queried_object() && isset(get_queried_object()->slug) && get_queried_object()->slug === $term_slug && $is_currently_checked ) {
        $is_unchecking_current_tax = true;
    }

    if ( is_product_category() ) {
        $base_url = get_term_link( get_queried_object() );
    } elseif ( is_tax( $taxonomy ) && ! $is_unchecking_current_tax ) {
        $base_url = get_term_link( get_queried_object() );
    } elseif ( is_shop() || $is_unchecking_current_tax ) {
        $base_url = get_permalink( wc_get_page_id( 'shop' ) );
    } else {
        global $wp;
        $base_url = home_url( add_query_arg( array(), $wp->request ) );
    }
    
    $query_args = $_GET;
    unset( $query_args['paged'] );
    
    if ( $is_currently_checked ) {
        unset( $query_args[ $query_key ] );
    } else {
        // Single select logic: unconditionally set query param to just this term slug
        $query_args[ $query_key ] = $term_slug;
    }
    
    return add_query_arg( $query_args, $base_url );
}

/**
 * Make WooCommerce shortcodes respect our custom $_GET filters
 * This fixes the issue where filtering on pages with [featured_products] does nothing.
 */
add_filter( 'woocommerce_shortcode_products_query', 'jad_filter_shortcode_products_query', 99, 3 );
function jad_filter_shortcode_products_query( $query_args, $attributes, $type ) {
    if ( ! isset( $query_args['tax_query'] ) || ! is_array( $query_args['tax_query'] ) ) {
        $query_args['tax_query'] = array();
    }
    
    // 1. Categories
    $filter_cats = isset($_GET['catgorie']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['catgorie'])))) : array();
    if ( !empty($filter_cats) ) {
        $query_args['tax_query'][] = array(
            'taxonomy'         => 'product_cat',
            'field'            => 'slug',
            'terms'            => $filter_cats,
            'include_children' => true,
            'operator'         => 'IN',
        );
    }
    
    // 2. Brands
    $filter_brands = isset($_GET['product_brand']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['product_brand'])))) : array();
    if ( empty($filter_brands) && isset($_GET['brand']) ) {
        $filter_brands = array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['brand']))));
    }
    if ( !empty($filter_brands) ) {
        $query_args['tax_query'][] = array(
            'taxonomy'         => 'product_brand',
            'field'            => 'slug',
            'terms'            => $filter_brands,
            'operator'         => 'IN',
        );
    }
    
    // 3. Attributes (filter_size, filter_colour, etc.)
    foreach ( $_GET as $key => $val ) {
        if ( strpos($key, 'filter_') === 0 ) {
            $attr_slug = 'pa_' . substr($key, 7);
            $terms = array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($val))));
            if (!empty($terms)) {
                $query_args['tax_query'][] = array(
                    'taxonomy' => $attr_slug,
                    'field'    => 'slug',
                    'terms'    => $terms,
                    'operator' => 'IN'
                );
            }
        }
    }
    
    return $query_args;
}