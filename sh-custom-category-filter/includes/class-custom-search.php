<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Custom_Woo_Search_Bar {

    public function __construct() {
        // Register shortcode
        add_shortcode( 'custom_search_bar', array( $this, 'render_search_bar' ) );

        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // AJAX handlers
        add_action( 'wp_ajax_jad_custom_search', array( $this, 'ajax_search' ) );
        add_action( 'wp_ajax_nopriv_jad_custom_search', array( $this, 'ajax_search' ) );
    }

    public function enqueue_assets() {
        // We will inline the CSS to keep it lightweight, similar to the other widgets
        // we'll output the JS via wp_add_inline_script or just a script tag in the footer
        wp_enqueue_script( 'custom-woo-search-js', plugin_dir_url( dirname(__FILE__) ) . 'assets/js/custom-search.js', array('jquery'), filemtime( plugin_dir_path( dirname(__FILE__) ) . 'assets/js/custom-search.js' ), true );
        
        // Localize script for ajax url
        wp_localize_script( 'custom-woo-search-js', 'jadSearchAjax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ) );
    }

    public function render_search_bar() {
        ob_start();
        ?>
        <div class="jad-custom-search-container">
            <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="jad-search-form">
                <div class="jad-search-input-wrapper">
                    <svg class="jad-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="s" id="jad-custom-search-input" class="jad-custom-search-input" placeholder="Searches" autocomplete="off" />
                    <input type="hidden" name="post_type" value="product" />
                    <div class="jad-search-loader" style="display: none;"></div>
                </div>
            </form>
            <div id="jad-search-results" class="jad-search-results" style="display: none;"></div>
        </div>

        <style>
           @media screen and (min-width: 786px) {
    .custom-search-form {
        justify-content: space-between;
        margin-top: 14px;
    }
}
@media screen and (min-width: 768px) {
    .custom-search-form {
        display: flex;
        gap: 50px;
    }
}


            .jad-custom-search-container {
                position: relative;
                width: 100%;
               margin-left: 20px;
                font-family: inherit;
            }
            input#jad-custom-search-input {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
}
            .jad-search-input-wrapper {
                position: relative;
                display: flex;
                align-items: center;
                background: #fff;
                border: 2px solid #000;
                border-radius: 30px;
                padding: 3px 16px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            .jad-search-icon {
                width: 18px;
                height: 18px;
                color: #555;
                margin-right: 10px;
            }
            .jad-custom-search-input {
                flex-grow: 1;
                border: none;
                background: transparent;
                padding: 5px 0;
                font-size: 16px;
                color: #333;
                outline: none;
                border-radius: 0;
                box-shadow: none;
            }
            .jad-custom-search-input:focus {
                border: none;
                outline: none;
                box-shadow: none;
                background: transparent;
            }
            .jad-search-loader {
                width: 18px;
                height: 18px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #b52b27;
                border-radius: 50%;
                animation: jad-search-spin 1s linear infinite;
                margin-left: 10px;
            }
            @keyframes jad-search-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            
            .jad-search-results {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                margin-top: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                z-index: 99999;
                max-height: 400px;
                overflow-y: auto;
            }
            .jad-search-result-group {
                padding: 10px 0;
                border-bottom: 1px solid #eee;
            }
            .jad-search-result-group:last-child {
                border-bottom: none;
            }
            .jad-search-result-title {
                font-size: 12px;
                text-transform: uppercase;
                color: #999;
                font-weight: 700;
                padding: 4px 16px;
                letter-spacing: 0.5px;
            }
            .jad-search-item {
                display: flex;
                align-items: center;
                padding: 8px 16px;
                color: #333;
                text-decoration: none;
                transition: background 0.2s;
            }
            .jad-search-item:hover {
                background: #f9f9f9;
                color: #b52b27;
            }
            .jad-search-item-image {
                width: 40px;
                height: 40px;
                object-fit: cover;
                border-radius: 4px;
                margin-right: 12px;
                background: #f5f5f5;
            }
            .jad-search-item-info {
                display: flex;
                flex-direction: column;
            }
            .jad-search-item-name {
                font-size: 14px;
                font-weight: 500;
            }
            .jad-search-item-price {
                font-size: 13px;
                color: #b52b27;
                font-weight: 600;
                margin-top: 2px;
            }
            .jad-search-no-results {
                padding: 16px;
                text-align: center;
                color: #777;
                font-style: italic;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    public function ajax_search() {
        $query = isset( $_POST['query'] ) ? sanitize_text_field( $_POST['query'] ) : '';
        
        if ( empty( $query ) ) {
            wp_send_json_error( 'Empty query' );
        }

        $results = array(
            'categories' => array(),
            'brands'     => array(),
            'colors'     => array(),
            'sizes'      => array(),
            'products'   => array()
        );

        // 1. Search Categories
        $categories = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'search'     => $query,
            'number'     => 5
        ) );

        if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
            foreach ( $categories as $cat ) {
                $results['categories'][] = array(
                    'name' => $cat->name,
                    'url'  => get_term_link( $cat ),
                );
            }
        }

        // 1.5 Search Brands
        $brands = get_terms( array(
            'taxonomy'   => 'product_brand',
            'hide_empty' => true,
            'search'     => $query,
            'number'     => 5
        ) );

        if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
            foreach ( $brands as $brand ) {
                $results['brands'][] = array(
                    'name' => $brand->name,
                    'url'  => get_term_link( $brand ),
                );
            }
        }

        // 1.6 Search Colors
        $colors = get_terms( array(
            'taxonomy'   => 'pa_colour',
            'hide_empty' => true,
            'search'     => $query,
            'number'     => 5
        ) );

        if ( ! is_wp_error( $colors ) && ! empty( $colors ) ) {
            foreach ( $colors as $color ) {
                $results['colors'][] = array(
                    'name' => $color->name,
                    'url'  => add_query_arg( array( 's' => '', 'post_type' => 'product', 'filter_colour' => $color->slug ), home_url( '/' ) ),
                );
            }
        }

        // 1.7 Search Sizes
        $sizes = get_terms( array(
            'taxonomy'   => 'pa_size',
            'hide_empty' => true,
            'search'     => $query,
            'number'     => 5
        ) );

        if ( ! is_wp_error( $sizes ) && ! empty( $sizes ) ) {
            foreach ( $sizes as $size ) {
                $results['sizes'][] = array(
                    'name' => $size->name,
                    'url'  => add_query_arg( array( 's' => '', 'post_type' => 'product', 'filter_size' => $size->slug ), home_url( '/' ) ),
                );
            }
        }

        // 2. Search Products
        $product_args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            's'              => $query
        );

        $product_query = new WP_Query( $product_args );

        if ( $product_query->have_posts() ) {
            while ( $product_query->have_posts() ) {
                $product_query->the_post();
                global $product;
                
                $image_url = '';
                if ( has_post_thumbnail() ) {
                    $image_url = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
                } else if ( function_exists( 'wc_placeholder_img_src' ) ) {
                    $image_url = wc_placeholder_img_src( 'thumbnail' );
                }

                $results['products'][] = array(
                    'name'  => get_the_title(),
                    'url'   => get_permalink(),
                    'price' => $product->get_price_html(),
                    'image' => $image_url
                );
            }
            wp_reset_postdata();
        }

        // Generate HTML response
        ob_start();
        
        if ( empty( $results['categories'] ) && empty( $results['brands'] ) && empty( $results['colors'] ) && empty( $results['sizes'] ) && empty( $results['products'] ) ) {
            echo '<div class="jad-search-no-results">No results found for "' . esc_html( $query ) . '"</div>';
        } else {
            // Display Categories
            if ( ! empty( $results['categories'] ) ) {
                echo '<div class="jad-search-result-group">';
                echo '<div class="jad-search-result-title">Categories</div>';
                foreach ( $results['categories'] as $cat ) {
                    echo '<a href="' . esc_url( $cat['url'] ) . '" class="jad-search-item">';
                    echo '<div class="jad-search-item-info">';
                    echo '<span class="jad-search-item-name">' . esc_html( $cat['name'] ) . '</span>';
                    echo '</div></a>';
                }
                echo '</div>';
            }

            // Display Brands
            if ( ! empty( $results['brands'] ) ) {
                echo '<div class="jad-search-result-group">';
                echo '<div class="jad-search-result-title">Brands</div>';
                foreach ( $results['brands'] as $brand ) {
                    echo '<a href="' . esc_url( $brand['url'] ) . '" class="jad-search-item">';
                    echo '<div class="jad-search-item-info">';
                    echo '<span class="jad-search-item-name">' . esc_html( $brand['name'] ) . '</span>';
                    echo '</div></a>';
                }
                echo '</div>';
            }

            // Display Colors
            if ( ! empty( $results['colors'] ) ) {
                echo '<div class="jad-search-result-group">';
                echo '<div class="jad-search-result-title">Colors</div>';
                foreach ( $results['colors'] as $color ) {
                    echo '<a href="' . esc_url( $color['url'] ) . '" class="jad-search-item">';
                    echo '<div class="jad-search-item-info">';
                    echo '<span class="jad-search-item-name">' . esc_html( $color['name'] ) . '</span>';
                    echo '</div></a>';
                }
                echo '</div>';
            }

            // Display Sizes
            if ( ! empty( $results['sizes'] ) ) {
                echo '<div class="jad-search-result-group">';
                echo '<div class="jad-search-result-title">Sizes</div>';
                foreach ( $results['sizes'] as $size ) {
                    echo '<a href="' . esc_url( $size['url'] ) . '" class="jad-search-item">';
                    echo '<div class="jad-search-item-info">';
                    echo '<span class="jad-search-item-name">' . esc_html( $size['name'] ) . '</span>';
                    echo '</div></a>';
                }
                echo '</div>';
            }

            // Display Products
            if ( ! empty( $results['products'] ) ) {
                echo '<div class="jad-search-result-group">';
                echo '<div class="jad-search-result-title">Products</div>';
                foreach ( $results['products'] as $prod ) {
                    echo '<a href="' . esc_url( $prod['url'] ) . '" class="jad-search-item">';
                    if ( ! empty( $prod['image'] ) ) {
                        echo '<img src="' . esc_url( $prod['image'] ) . '" class="jad-search-item-image" alt="' . esc_attr( wp_strip_all_tags( $prod['name'] ) ) . '">';
                    }
                    echo '<div class="jad-search-item-info">';
                    echo '<span class="jad-search-item-name">' . esc_html( wp_strip_all_tags($prod['name']) ) . '</span>';
                    if ( ! empty( $prod['price'] ) ) {
                        echo '<span class="jad-search-item-price">' . wp_kses_post( $prod['price'] ) . '</span>';
                    }
                    echo '</div></a>';
                }
                echo '</div>';
            }
        }

        $html = ob_get_clean();

        wp_send_json_success( array( 'html' => $html ) );
    }
}

new Custom_Woo_Search_Bar();