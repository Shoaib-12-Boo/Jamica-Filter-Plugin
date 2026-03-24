<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Custom_Woo_Category_Filter_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'custom_woo_cat_filter',
            'JADeals Category Filter Pro',
            array('description' => 'Professional Category filter with empty child message.')
        );
    }

    public function widget($args, $instance) {
        if ( is_front_page() || is_home() ) {
            return;
        }

        $title = apply_filters('widget_title', $instance['title']);
        $current_cat_id = 0;
        $parent_to_show = 0; 
        $current_cat_name = '';
        
        // Find current category from queried object OR query var
        $current_cat_slug_var = get_query_var( 'product_cat' );
        if ( !empty($current_cat_slug_var) ) {
            $term = get_term_by( 'slug', $current_cat_slug_var, 'product_cat' );
            if ( $term && !is_wp_error($term) ) {
                $current_cat_id = $term->term_id;
                $current_cat_name = $term->name;
            }
        } elseif ( is_product_category() ) {
            $current_obj = get_queried_object();
            if ( $current_obj && isset($current_obj->term_id) ) {
                $current_cat_id = $current_obj->term_id;
                $current_cat_name = $current_obj->name;
            }
        }

        $on_category_page = ($current_cat_id > 0);

        if ( $on_category_page ) {
            // Default to showing children of the current category
            $parent_to_show = $current_cat_id;
            
            // Check if current category has children
            $children = get_term_children( $current_cat_id, 'product_cat' );
        }

        // Determine if this panel should be open by default (Open on Desktop, Closed on Mobile unless filtered)
        $current_filters = isset($_GET['catgorie']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['catgorie'])))) : array();
        $is_panel_open = !wp_is_mobile();
        if (wp_is_mobile() && !empty($current_filters)) {
            $is_panel_open = true;
        }

        $categories = jad_get_terms_for_current_category('product_cat', $parent_to_show);

        // Agar categories empty hain, to seedha return kar dein, widget show he na karein
        if ( empty($categories) || is_wp_error($categories) ) {
            return;
        }

        echo $args['before_widget'];
        
        $current_filters = isset($_GET['catgorie']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['catgorie'])))) : array();
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
            unset($query_args['catgorie']);
            $clear_url = add_query_arg($query_args, $base_url);
        }
        ?>
        <div id="jad-cat-filter" class="custom-cat-filter-container jad-category-filter <?php echo $is_panel_open ? 'is-open' : ''; ?>">
            <div class="cat-filter-header" id="catFilterToggle">
                <div class="header-text-wrapper">
                    <?php
                    $display_title = !empty($title) ? $title : 'Categories';
                    if ($current_cat_id > 0 && !empty($current_cat_name)) {
                        $display_title = $current_cat_name;
                    }
                    ?>
                    <h3><?php echo esc_html($display_title); ?></h3>
                </div>
                <?php if ($has_active_filters && !empty($clear_url)): ?>
                    <a href="<?php echo esc_url($clear_url); ?>" class="jad-clear-filter">Clear</a>
                <?php endif; ?>
                <span class="cat-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#253b80" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>

            <div class="cat-filter-body scrollable-cat-list" style="<?php echo $is_panel_open ? 'display: block;' : ''; ?>">
                <?php
                foreach ($categories as $cat) :
                    if ( $cat->count == 0 && !in_array($cat->slug, $current_filters) ) {
                        continue;
                    }
                    $checkbox_id = 'cat-' . $cat->term_id;
                    $is_checked = ($current_cat_id == $cat->term_id) ? 'checked="checked"' : '';
                    $is_parent = ($cat->parent == 0) ? 'true' : 'false';
                    
                    // Generate PJAX toggle URL for multi-select
                    $input_class = 'cat-filter-input cat-redirect-input';
                    
                    $current_filters = isset($_GET['catgorie']) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['catgorie'])))) : array();
                    
                    $is_checked = in_array($cat->slug, $current_filters) ? 'checked="checked"' : '';
                    
                    $new_filters = $current_filters;
                    if (in_array($cat->slug, $new_filters)) {
                        $new_filters = array_filter($new_filters, function($v) use ($cat) { return $v !== $cat->slug; });
                    } else {
                        $new_filters[] = $cat->slug;
                    }
                    
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
                    
                    if (!empty($new_filters)) {
                        $query_args['catgorie'] = implode(',', $new_filters);
                    } else {
                        unset($query_args['catgorie']);
                    }
                    
                    $toggle_url = add_query_arg($query_args, $base_url);
                    ?>
                    <div class="cat-row">
                            <label class="cat-label" for="<?php echo esc_attr($checkbox_id); ?>">
                                <input type="checkbox" class="<?php echo esc_attr($input_class); ?>" 
                                       id="<?php echo esc_attr($checkbox_id); ?>" 
                                       value="<?php echo esc_attr($cat->term_id); ?>" 
                                       data-url="<?php echo esc_url($toggle_url); ?>"
                                       data-term-id="<?php echo esc_attr($cat->term_id); ?>"
                                       data-is-parent="<?php echo esc_attr($is_parent); ?>"
                                       <?php echo $is_checked; ?>>
                                <span class="custom-checkmark"></span>
                                <span class="cat-text">
                                    <?php echo $cat->name; ?> 
                                    <span class="count">(<?php echo $cat->count; ?>)</span>
                                </span>
                            </label>
                        </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Categories';
        echo '<p><label>Title:</label><input class="widefat" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr($title).'"></p>';
    }
}