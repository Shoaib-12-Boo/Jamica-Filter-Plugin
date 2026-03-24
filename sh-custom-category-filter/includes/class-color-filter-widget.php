<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Custom_Woo_Color_Filter_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'custom_woo_color_filter',
            'JADeals Color Filter Pro',
            array('description' => 'Professional Color filter with empty child message.')
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

        if ( isset($_GET['term']) && $_GET['taxonomy'] === 'pa_colour' ) {
            $current_term_slug = sanitize_text_field($_GET['term']);
        } elseif ( isset($_GET['filter_colour']) ) {
            $current_term_slug = sanitize_text_field($_GET['filter_colour']);
        }
        
        $on_color_page = is_tax('pa_colour');

        if ( $on_color_page ) {
            $current_obj = get_queried_object();
            $current_term_id = $current_obj->term_id;
            $current_term_slug = $current_obj->slug;
            
            
            // Default to showing children of the current color
            $parent_to_show = $current_term_id;
            
            // Check if current color has children
            $children = get_term_children( $current_term_id, 'pa_colour' );
            
            // If it has NO children, show its siblings (children of its parent) 
            if ( empty( $children ) || is_wp_error( $children ) ) {
                $parent_to_show = $current_obj->parent;
            }
        }

        // Determine if this panel should be open by default (Open on Desktop, Closed on Mobile unless filtered)
        $current_filters = isset($_GET['filter_colour']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['filter_colour'])))) : array();
        $is_panel_open = !wp_is_mobile();
        if (wp_is_mobile() && !empty($current_filters)) {
            $is_panel_open = true;
        }

        // Fetch colors early so we can hide the widget if empty
        // Unconditionally use the custom helper to adapt to featured-products and other pages
        $colors = jad_get_terms_for_current_category('pa_colour', $parent_to_show);

        echo $args['before_widget'];

        if ( empty($colors) || is_wp_error($colors) ) {
            echo '<div id="jad-color-filter" class="custom-cat-filter-container" style="display:none;"></div>';
            echo $args['after_widget'];
            return;
        }

        $current_filters = isset($_GET['filter_colour']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['filter_colour'])))) : array();
        if (empty($current_filters) && is_tax('pa_colour')) {
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
            unset($query_args['filter_colour']);
            unset($query_args['query_type_colour']);
            $clear_url = add_query_arg($query_args, $base_url);
        }
        ?>
        <div id="jad-color-filter" class="custom-cat-filter-container <?php echo $is_panel_open ? 'is-open' : ''; ?>">
            <div class="cat-filter-header" id="colorFilterToggle">
                <div class="header-text-wrapper">
                    <h3><?php echo !empty($title) ? $title : 'Colors'; ?></h3>
                </div>
                <?php if ($has_active_filters && !empty($clear_url)): ?>
                    <a href="<?php echo esc_url($clear_url); ?>" class="jad-clear-filter">Clear</a>
                <?php endif; ?>
                <span class="cat-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#253b80" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>

            <div class="cat-filter-body scrollable-cat-list" id="colorFilterBody" style="<?php echo $is_panel_open ? 'display: block;' : ''; ?>">
                <?php
                if ( !empty($colors) && !is_wp_error($colors) ) :
                    foreach ($colors as $color) :
                        if ( $color->count == 0 && !in_array($color->slug, $current_filters) ) {
                            continue;
                        }

                        $checkbox_id = 'color-' . $color->term_id;
                        $is_parent = ($color->parent == 0) ? 'true' : 'false';
                        
                        // Parse current filter from URL
                        $current_filters = isset($_GET['filter_colour']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['filter_colour'])))) : array();
                        
                        if (empty($current_filters) && is_tax('pa_colour')) {
                            $current_obj = get_queried_object();
                            if ($current_obj && isset($current_obj->slug)) {
                                $current_filters[] = $current_obj->slug;
                            }
                        }
                        
                        $is_checked = in_array($color->slug, $current_filters) ? 'checked="checked"' : '';
                        
                        $new_filters = $current_filters;
                        if (in_array($color->slug, $new_filters)) {
                            // Removing if already checked
                            $new_filters = array_filter($new_filters, function($v) use ($color) { return $v !== $color->slug; });
                        } else {
                            // Adding to filters array
                            $new_filters[] = $color->slug;
                        }
                        
                        $current_cat_slug_for_url = get_query_var( 'product_cat' );
                        if (!empty($current_cat_slug_for_url)) {
                            // If base is a category, we stay on it
                            $base_url = get_term_link($current_cat_slug_for_url, 'product_cat');
                        } elseif (is_product_category() && isset(get_queried_object()->taxonomy) && get_queried_object()->taxonomy === 'product_cat') {
                            $base_url = get_term_link(get_queried_object());
                        } elseif (is_tax('pa_colour') && count($new_filters) === 1 && isset(get_queried_object()->taxonomy) && get_queried_object()->taxonomy === 'pa_colour' && in_array(get_queried_object()->slug, $new_filters)) {
                            $base_url = get_term_link(get_queried_object());
                            } else {
                                // Otherwise fallback to current page
                                global $wp;
                                $base_url = home_url( add_query_arg( array(), $wp->request ) );
                                if (empty($wp->request)) $base_url = home_url('/');
                            }
                        
                        if (is_wp_error($base_url)) {
                            $base_url = home_url('/');
                        }
                        
                        $query_args = $_GET;
                        unset($query_args['paged']);
                        
                        if (!empty($new_filters)) {
                            if (is_tax('pa_colour') && count($new_filters) === 1 && current($new_filters) === get_queried_object()->slug) {
                                unset($query_args['filter_colour']);
                                unset($query_args['query_type_colour']);
                            } else {
                                $query_args['filter_colour'] = implode(',', $new_filters);
                                $query_args['query_type_colour'] = 'or';
                            }
                        } else {
                            unset($query_args['filter_colour']);
                            unset($query_args['query_type_colour']);
                        }
                        
                        $toggle_url = add_query_arg($query_args, $base_url);
                        ?>
                        <div class="cat-row">
                            <label class="cat-label" for="<?php echo $checkbox_id; ?>">
                                <input type="checkbox" class="cat-filter-input cat-redirect-input" 
                                       id="<?php echo $checkbox_id; ?>" 
                                       value="<?php echo $color->term_id; ?>" 
                                       data-url="<?php echo esc_url($toggle_url); ?>"
                                       data-is-parent="<?php echo $is_parent; ?>"
                                       <?php echo $is_checked; ?>>
                                <span class="custom-checkmark"></span>
                                <span class="cat-text">
                                    <?php echo $color->name; ?> 
                                    <span class="count">(<?php echo $color->count; ?>)</span>
                                </span>
                            </label>
                        </div>
                    <?php endforeach;
                else:
                    // Message for no child colors
                    echo '<div class="no-child-msg">No sub-colors found</div>';
                endif;
                ?>
            </div>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Colors';
        echo '<p><label>Title:</label><input class="widefat" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr($title).'"></p>';
    }
}