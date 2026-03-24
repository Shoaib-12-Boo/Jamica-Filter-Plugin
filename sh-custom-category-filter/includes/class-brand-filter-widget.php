<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Custom_Woo_Brand_Filter_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'custom_woo_brand_filter',
            'JADeals Brand Filter Pro',
            array('description' => 'Professional Brand filter with empty child message.')
        );
    }

    public function widget($args, $instance) {
        if ( is_front_page() || is_home() ) {
            return;
        }

        $title = apply_filters('widget_title', $instance['title']);
        $current_term_id = 0;
        $current_term_slug = '';
        $parent_to_show = 0; 
        
        $brand_query_var = get_query_var('product_brand');
        
        if ( isset($_GET['term']) && $_GET['taxonomy'] === 'product_brand' ) {
            $current_term_slug = sanitize_text_field($_GET['term']);
        } elseif ( isset($_GET['product_brand']) ) {
            $current_term_slug = sanitize_text_field($_GET['product_brand']);
        } elseif ( isset($_GET['filter_product_brand']) ) {
            $current_term_slug = sanitize_text_field($_GET['filter_product_brand']);
        } elseif (!empty($brand_query_var)) {
            $current_term_slug = $brand_query_var;
        }

        // Ensure this works for brands
        $on_brand_page = false;
        if (!empty($brand_query_var)) {
             $term = get_term_by('slug', $brand_query_var, 'product_brand');
             if ($term && !is_wp_error($term)) {
                 $current_term_id = $term->term_id;
                 $on_brand_page = true;
             }
        } elseif ( is_tax('product_brand') ) {
            $current_obj = get_queried_object();
            if ($current_obj && isset($current_obj->term_id)) {
                $current_term_id = $current_obj->term_id;
                $current_term_slug = $current_obj->slug;
                $on_brand_page = true;
            }
        }

        // Brand hierarchical drill-down disabled to allow multiple selections across brands.
        // if ( $on_brand_page ) {
        //     $parent_to_show = $current_term_id;
        //     $children = get_term_children( $current_term_id, 'product_brand' );
        //     if ( empty( $children ) || is_wp_error( $children ) ) {
        //         if (isset($term) && isset($term->parent)) {
        //              $parent_to_show = $term->parent;
        //         } elseif (isset($current_obj) && isset($current_obj->parent)) {
        //              $parent_to_show = $current_obj->parent;
        //         }
        //     }
        // }

        // Determine if this panel should be open by default (Open on Desktop, Closed on Mobile unless filtered)
        $current_filters = isset($_GET['product_brand']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['product_brand'])))) : array();
        $is_panel_open = !wp_is_mobile();
        if (wp_is_mobile() && !empty($current_filters)) {
            $is_panel_open = true;
        }

        // Fetch terms early so we can hide the widget if empty
        // Unconditionally use the custom helper to adapt to featured-products and other pages
        $brands = jad_get_terms_for_current_category('product_brand', $parent_to_show);

        echo $args['before_widget'];

        if ( empty($brands) || is_wp_error($brands) ) {
            echo '<div id="jad-brand-filter" class="custom-cat-filter-container" style="display:none;"></div>';
            echo $args['after_widget'];
            return;
        }

        $current_filters = isset($_GET['product_brand']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['product_brand'])))) : array();
        if (empty($current_filters) && is_tax('product_brand')) {
            $current_obj = get_queried_object();
            if ($current_obj && isset($current_obj->slug)) {
                $current_filters[] = $current_obj->slug;
            }
        }
        $has_active_filters = !empty($current_filters);
        $clear_url = '';
        if ($has_active_filters) {
            $current_cat_slug_for_url = get_query_var( 'product_cat' );
            if (!empty($current_cat_slug_for_url)) {
                $base_url = get_term_link($current_cat_slug_for_url, 'product_cat');
            } elseif (is_product_category() && isset(get_queried_object()->taxonomy) && get_queried_object()->taxonomy === 'product_cat') {
                $base_url = get_term_link(get_queried_object());
            } else {
                global $wp;
                $base_url = home_url( add_query_arg( array(), $wp->request ) );
                if (empty($wp->request)) $base_url = home_url('/');
            }
            if (is_wp_error($base_url)) {
                 $base_url = home_url('/');
            }
            $query_args = $_GET;
            unset($query_args['paged']);
            unset($query_args['product_brand']);
            $clear_url = add_query_arg($query_args, $base_url);
        }
        ?>
        <div id="jad-brand-filter" class="custom-cat-filter-container <?php echo $is_panel_open ? 'is-open' : ''; ?>">
            <div class="cat-filter-header" id="brandFilterToggle">
                <div class="header-text-wrapper">
                    <h3><?php echo !empty($title) ? $title : 'Brands'; ?></h3>
                </div>
                <?php if ($has_active_filters && !empty($clear_url)): ?>
                    <a href="<?php echo esc_url($clear_url); ?>" class="jad-clear-filter">Clear</a>
                <?php endif; ?>
                <span class="cat-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#253b80" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>

            <div class="cat-filter-body scrollable-cat-list" id="brandFilterBody" style="<?php echo $is_panel_open ? 'display: block;' : ''; ?>">
                <?php
                if ( !empty($brands) && !is_wp_error($brands) ) :
                    foreach ($brands as $brand) :
                        if ( $brand->count == 0 && !in_array($brand->slug, $current_filters) ) {
                            continue;
                        }

                        $checkbox_id = 'brand-' . $brand->term_id;
                        $is_parent = ($brand->parent == 0) ? 'true' : 'false';
                        
                        $input_class = 'cat-filter-input cat-redirect-input';
                        
                        // Parse current filter from URL
                        $current_filters = isset($_GET['product_brand']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['product_brand'])))) : array();
                        
                        if (empty($current_filters) && is_tax('product_brand')) {
                            $current_obj = get_queried_object();
                            if ($current_obj && isset($current_obj->slug)) {
                                $current_filters[] = $current_obj->slug;
                            }
                        }
                        
                        $is_checked = in_array($brand->slug, $current_filters) ? 'checked="checked"' : '';
                        
                        $new_filters = $current_filters;
                        if (in_array($brand->slug, $new_filters)) {
                            // Removing if already checked
                            $new_filters = array_filter($new_filters, function($v) use ($brand) { return $v !== $brand->slug; });
                        } else {
                            // Adding to filters array
                            $new_filters[] = $brand->slug;
                        }
                        
                        $current_cat_slug_for_url = get_query_var( 'product_cat' );
                        if (!empty($current_cat_slug_for_url)) {
                            // If base is a category, we stay on it
                            $base_url = get_term_link($current_cat_slug_for_url, 'product_cat');
                        } elseif (is_product_category() && isset(get_queried_object()->taxonomy) && get_queried_object()->taxonomy === 'product_cat') {
                            $base_url = get_term_link(get_queried_object());
                        } elseif (!empty(get_query_var('product_brand')) && count($new_filters) === 1 && in_array(get_query_var('product_brand'), $new_filters)) {
                            // We are reverting to the single brand archive page naturally
                            $base_url = get_term_link(get_query_var('product_brand'), 'product_brand');
                        } elseif (is_tax('product_brand') && count($new_filters) === 1 && isset(get_queried_object()->taxonomy) && get_queried_object()->taxonomy === 'product_brand' && in_array(get_queried_object()->slug, $new_filters)) {
                            $base_url = get_term_link(get_queried_object());
                        } else {
                            // Otherwise fallback to shop page for multi-filtering
                            global $wp;
                            $base_url = home_url( add_query_arg( array(), $wp->request ) );
                            if (empty($wp->request)) $base_url = home_url('/');
                        }                   
                        if (is_wp_error($base_url)) {
                            $base_url = home_url('/');
                        }
                        
                        $query_args = $_GET;
                        unset($query_args['paged']);
                        
                        // Since WooCommerce might do weird things if we are on a brand tax page and push to shop, we must explicitly provide product_brand.
                        if (!empty($new_filters)) {
                            // If it's a single brand and we're on that brand's archive page, no need for the query var because the URL handles it
                            if (is_tax('product_brand') && count($new_filters) === 1 && current($new_filters) === get_queried_object()->slug) {
                                unset($query_args['product_brand']);
                            } else {
                                $query_args['product_brand'] = implode(',', $new_filters);
                            }
                        } else {
                            unset($query_args['product_brand']);
                        }
                        
                        $toggle_url = add_query_arg($query_args, $base_url);
                        ?>
                        <div class="cat-row">
                            <label class="cat-label" for="<?php echo esc_attr($checkbox_id); ?>">
                                <input type="checkbox" class="<?php echo esc_attr($input_class); ?>" 
                                       id="<?php echo esc_attr($checkbox_id); ?>" 
                                       value="<?php echo esc_attr($brand->term_id); ?>" 
                                       data-url="<?php echo esc_url($toggle_url); ?>"
                                       data-is-parent="<?php echo esc_attr($is_parent); ?>"
                                       <?php echo $is_checked; ?>>
                                <span class="custom-checkmark"></span>
                                <span class="cat-text">
                                    <?php echo esc_html($brand->name); ?> 
                                    <span class="count">(<?php echo esc_html($brand->count); ?>)</span>
                                </span>
                            </label>
                        </div>
                    <?php endforeach;
                else:
                    // Message for no child brands
                    echo '<div class="no-child-msg">No sub-brands found</div>';
                endif;
                ?>
            </div>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Brands';
        echo '<p><label>Title:</label><input class="widefat" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr($title).'"></p>';
    }
}