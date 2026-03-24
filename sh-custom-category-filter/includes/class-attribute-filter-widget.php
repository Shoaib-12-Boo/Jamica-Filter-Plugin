<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Custom_Woo_Attribute_Filter_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'custom_woo_attribute_filter',
            'JADeals Attribute Filter Pro',
            array('description' => 'Professional Attribute filter (works with any WooCommerce product attribute).')
        );
    }

    public function widget($args, $instance) {
        if ( is_front_page() || is_home() ) {
            return;
        }

        $title = apply_filters('widget_title', isset($instance['title']) ? $instance['title'] : '');
        $attributes_selected = !empty($instance['attributes']) ? $instance['attributes'] : array();
        
        // Backward compatibility
        if (empty($attributes_selected) && !empty($instance['attribute'])) {
            $attributes_selected = array($instance['attribute']);
        }
        
        if (empty($attributes_selected)) {
            return;
        }

        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        foreach ($attributes_selected as $attribute_tax) {
            $current_term_id = 0;
            $current_term_slug = '';
            $parent_to_show = 0; 

            // For pa_size, we use filter_size in URL
            $query_param = 'filter_' . str_replace('pa_', '', $attribute_tax);

            if ( isset($_GET['term']) && $_GET['taxonomy'] === $attribute_tax ) {
                $current_term_slug = sanitize_text_field($_GET['term']);
            } elseif ( isset($_GET[$query_param]) ) {
                $current_term_slug = sanitize_text_field($_GET[$query_param]);
            }
            
            $on_attribute_page = is_tax($attribute_tax);

            if ( $on_attribute_page ) {
                $current_obj = get_queried_object();
                $current_term_id = $current_obj->term_id;
                $current_term_slug = $current_obj->slug;
                
                // Default to showing children of the current attribute term
                $parent_to_show = $current_term_id;
                
                // Check if current term has children
                $children = get_term_children( $current_term_id, $attribute_tax );
                
                // If it has NO children, show its siblings (children of its parent) 
                if ( empty( $children ) || is_wp_error( $children ) ) {
                    $parent_to_show = $current_obj->parent;
                }
            }

            // Determine if this panel should be open by default (Open on Desktop, Closed on Mobile unless filtered)
            $current_filters = isset($_GET['filter_' . $taxonomy_slug]) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET['filter_' . $taxonomy_slug])))) : array();
            $is_panel_open = !wp_is_mobile();
            if (wp_is_mobile() && !empty($current_filters)) {
                $is_panel_open = true;
            }
            
            // Fetch terms early so we can hide the widget if empty
            // Now unconditionally using the helper so it handles category pages, featured products, etc.
            $terms = jad_get_terms_for_current_category( $attribute_tax, $parent_to_show );

            if ( empty($terms) || is_wp_error($terms) ) {
                // If there are no terms for this category, we just output an empty hidden container so JS/PJAX doesn't break
                // when navigating back to a category that DOES have terms.
                echo '<div id="jad-' . esc_attr($attribute_tax) . '-filter" class="custom-cat-filter-container jad-attribute-filter" style="display:none;"></div>';
                // Move to next attribute without breaking the widget
                continue; 
            }

            $current_filters = isset($_GET[$query_param]) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET[$query_param])))) : array();
            if (empty($current_filters) && is_tax($attribute_tax)) {
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
                unset($query_args[$query_param]);
                unset($query_args['query_type_' . str_replace('pa_', '', $attribute_tax)]);
                $clear_url = add_query_arg($query_args, $base_url);
            }
            ?>
            <div id="jad-<?php echo esc_attr($attribute_tax); ?>-filter" class="custom-cat-filter-container jad-attribute-filter <?php echo $is_panel_open ? 'is-open' : ''; ?>">
                <div class="cat-filter-header jad-attribute-filter-toggle">
                    <div class="header-text-wrapper">
                        <h3><?php echo wc_attribute_label($attribute_tax); ?></h3>
                    </div>
                    <?php if ($has_active_filters && !empty($clear_url)): ?>
                        <a href="<?php echo esc_url($clear_url); ?>" class="jad-clear-filter">Clear</a>
                    <?php endif; ?>
                    <span class="cat-arrow">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#253b80" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </span>
                </div>

                <div class="cat-filter-body scrollable-cat-list jad-attribute-filter-body" style="<?php echo $is_panel_open ? 'display: block;' : ''; ?>">
                    <?php
                    if ( !empty($terms) && !is_wp_error($terms) ) :
                        foreach ($terms as $term) :
                            if ( $term->count == 0 && !in_array($term->slug, $current_filters) ) {
                                continue;
                            }

                            $checkbox_id = 'attr-' . $attribute_tax . '-' . $term->term_id;
                            $is_parent = ($term->parent == 0) ? 'true' : 'false';
                            
                            // Parse current filter from URL
                            $current_filters = isset($_GET[$query_param]) ? array_filter(array_map('sanitize_text_field', explode(',', wp_unslash($_GET[$query_param])))) : array();
                            
                            if (empty($current_filters) && is_tax($attribute_tax)) {
                                $current_obj = get_queried_object();
                                if ($current_obj && isset($current_obj->slug)) {
                                    $current_filters[] = $current_obj->slug;
                                }
                            }
                            
                            $is_checked = in_array($term->slug, $current_filters) ? 'checked="checked"' : '';
                            
                            $new_filters = $current_filters;
                            if (in_array($term->slug, $new_filters)) {
                                // Removing if already checked
                                $new_filters = array_filter($new_filters, function($v) use ($term) { return $v !== $term->slug; });
                            } else {
                                // Adding to filters array
                                $new_filters[] = $term->slug;
                            }
                            
                            $current_cat_slug_for_url = get_query_var( 'product_cat' );
                            if (!empty($current_cat_slug_for_url)) {
                                // If base is a category, we stay on it
                                $base_url = get_term_link($current_cat_slug_for_url, 'product_cat');
                            } elseif (is_product_category() && isset(get_queried_object()->taxonomy) && get_queried_object()->taxonomy === 'product_cat') {
                                $base_url = get_term_link(get_queried_object());
                            } elseif (is_tax($attribute_tax) && count($new_filters) === 1 && isset(get_queried_object()->taxonomy) && get_queried_object()->taxonomy === $attribute_tax && in_array(get_queried_object()->slug, $new_filters)) {
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
                                if (is_tax($attribute_tax) && count($new_filters) === 1 && current($new_filters) === get_queried_object()->slug) {
                                    unset($query_args[$query_param]);
                                    unset($query_args['query_type_' . str_replace('pa_', '', $attribute_tax)]);
                                } else {
                                    $query_args[$query_param] = implode(',', $new_filters);
                                    $query_args['query_type_' . str_replace('pa_', '', $attribute_tax)] = 'or';
                                }
                            } else {
                                unset($query_args[$query_param]);
                                unset($query_args['query_type_' . str_replace('pa_', '', $attribute_tax)]);
                            }
                            
                            $toggle_url = add_query_arg($query_args, $base_url);
                            ?>
                            <div class="cat-row">
                                <label class="cat-label" for="<?php echo esc_attr($checkbox_id); ?>">
                                    <input type="checkbox" class="cat-filter-input cat-redirect-input" 
                                           id="<?php echo esc_attr($checkbox_id); ?>" 
                                           value="<?php echo esc_attr($term->term_id); ?>" 
                                           data-url="<?php echo esc_url($toggle_url); ?>"
                                           data-is-parent="<?php echo esc_attr($is_parent); ?>"
                                           <?php echo $is_checked; ?>>
                                    <span class="custom-checkmark"></span>
                                    <span class="cat-text">
                                        <?php echo esc_html($term->name); ?> 
                                        <span class="count">(<?php echo esc_html($term->count); ?>)</span>
                                    </span>
                                </label>
                            </div>
                        <?php endforeach;
                    else:
                        // Message for no children
                        echo '<div class="no-child-msg">No sub-options found</div>';
                    endif;
                    ?>
                </div>
            </div>
            <?php
        }

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        // Attributes selected is now properly stored as an array or converted from string if needed
        $attributes_selected = !empty($instance['attributes']) ? $instance['attributes'] : array();
        
        // Ensure it's an array if it somehow was saved as a string before
        if (is_string($attributes_selected)) {
            $attributes_selected = array_filter(array_map('trim', explode(',', $attributes_selected)));
        }

        // Backwards compatibility
        if (empty($attributes_selected) && !empty($instance['attribute'])) {
            $attributes_selected = array($instance['attribute']);
        }
        
        // Get all WooCommerce product attributes
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        
        echo '<p><label>Global Widget Title (Optional):</label><input class="widefat" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr($title).'"></p>';
        
        // We use a single hidden input field to store comma-separated values 
        // to bypass PHP max_input_vars limit when there are many attributes inside the widget form.
        $attributes_csv_id = $this->get_field_id('attributes_csv');
        $attributes_csv_name = $this->get_field_name('attributes_csv');
        $current_csv = implode(',', $attributes_selected);
        
        echo '<input type="hidden" id="'.esc_attr($attributes_csv_id).'" name="'.esc_attr($attributes_csv_name).'" value="'.esc_attr($current_csv).'" class="jad-attributes-csv-input" />';
        
        echo '<p><label>Select Attributes to Show:</label><br/>';
        echo '<div style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff; border-radius: 4px;">';
        if ( ! empty( $attribute_taxonomies ) ) {
            foreach ( $attribute_taxonomies as $tax ) {
                $taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
                $checked = in_array( $taxonomy_name, $attributes_selected ) ? 'checked="checked"' : '';
                echo '<label style="display: flex; align-items: center; margin-bottom: 8px; cursor: pointer;">';
                echo '<input type="checkbox" class="jad-attribute-checkbox" value="' . esc_attr( $taxonomy_name ) . '" ' . $checked . ' style="margin-right: 8px;" onchange="jadUpdateCsvInput(this)"> ';
                echo esc_html( $tax->attribute_label );
                echo '</label>';
            }
        } else {
            echo '<p>No attributes found in WooCommerce.</p>';
        }
        echo '</div>';
        echo '<small>Check all the attributes you want to display as separate filter panels.</small></p>';
        
        // JS script to keep the hidden input updated
        ?>
        <script>
        function jadUpdateCsvInput(checkbox) {
            var container = checkbox.closest('div');
            var checkboxes = container.querySelectorAll('.jad-attribute-checkbox:checked');
            var values = [];
            for (var i = 0; i < checkboxes.length; i++) {
                values.push(checkboxes[i].value);
            }
            var hiddenInput = container.previousElementSibling.previousElementSibling;
            if (hiddenInput && hiddenInput.classList.contains('jad-attributes-csv-input')) {
                 hiddenInput.value = values.join(',');
            }
        }
        </script>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        
        // Read from the CSV hidden field instead of array of inputs
        if ( isset( $new_instance['attributes_csv'] ) && !empty($new_instance['attributes_csv']) ) {
            $instance['attributes'] = array_filter(array_map( 'sanitize_text_field', explode(',', $new_instance['attributes_csv']) ));
        } else {
            $instance['attributes'] = array();
        }
        return $instance;
    }
}