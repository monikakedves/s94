<?php

function studio94_theme_setup() {
    add_theme_support('woocommerce');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    remove_theme_support('widgets-block-editor');
    register_nav_menus(['sidebar_menu' => 'Sidebar Navigation Menu']);
}
add_action('after_setup_theme', 'studio94_theme_setup');

function studio94_widgets_init() {
    register_sidebar(['name' => 'Footer Column 1 (Contact)', 'id' => 'footer-1', 'before_widget' => '<div>', 'after_widget' => '</div>', 'before_title' => '<h4 class="footer-heading">', 'after_title' => '</h4>']);
    register_sidebar(['name' => 'Footer Column 2 (Links)', 'id' => 'footer-2', 'before_widget' => '<div class="footer-links">', 'after_widget' => '</div>', 'before_title' => '<h4 class="footer-heading">', 'after_title' => '</h4>']);
    register_sidebar(['name' => 'Shop Filters (Top Bar)', 'id' => 'shop-filters', 'before_widget' => '<div class="shop-filter-widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="filter-heading">', 'after_title' => '</h4>']);
}
add_action('widgets_init', 'studio94_widgets_init');

function studio94_customize_register($wp_customize) {
    $wp_customize->add_section('studio94_hero_section', ['title' => 'Landing Page Hero', 'priority' => 30]);
    $wp_customize->add_setting('hero_badge_text', ['default' => 'Designed & Hand-Pressed in the UK']);
    $wp_customize->add_control('hero_badge_text', ['label' => 'Hero Badge Text', 'section' => 'studio94_hero_section']);
    $wp_customize->add_setting('hero_title', ['default' => 'Wearable Art.<br><span>Elderly Emo Approved.</span>']);
    $wp_customize->add_control('hero_title', ['label' => 'Hero Title (HTML allowed)', 'section' => 'studio94_hero_section', 'type' => 'textarea']);
    $wp_customize->add_setting('hero_subtitle', ['default' => 'Heavyweight tees, original linocut block prints, canvas totes, and temporary flash sheets for those who never grew out of it.']);
    $wp_customize->add_control('hero_subtitle', ['label' => 'Hero Subtitle', 'section' => 'studio94_hero_section', 'type' => 'textarea']);
    $wp_customize->add_setting('hero_btn_text', ['default' => 'Explore Collection']);
    $wp_customize->add_control('hero_btn_text', ['label' => 'Button Text', 'section' => 'studio94_hero_section']);
    $wp_customize->add_setting('hero_btn_link', ['default' => '/shop/']);
    $wp_customize->add_control('hero_btn_link', ['label' => 'Button URL', 'section' => 'studio94_hero_section']);
    $wp_customize->add_setting('brand_color', ['default' => '#df8ca3', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'brand_color', ['label' => 'Main Brand Color', 'section' => 'colors']));
}
add_action('customize_register', 'studio94_customize_register');

function studio94_customizer_css() {
    $brand_color = get_theme_mod('brand_color', '#df8ca3');
    echo "<style type=\"text/css\">:root{--brand-pink: " . esc_attr($brand_color) . "; --brand-pink-light: " . esc_attr($brand_color) . "1A;}</style>";
}
add_action('wp_head', 'studio94_customizer_css');

function studio94_enqueue_scripts() {
    wp_enqueue_style('studio94-fonts', 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap', false);
    $theme_version = filemtime(get_stylesheet_directory() . '/style.css');
    wp_enqueue_style('studio94-style', get_stylesheet_uri(), [], $theme_version);

    $card_css_path = get_stylesheet_directory() . '/assets/css/product-card.css';
    wp_enqueue_style('studio94-product-card', get_template_directory_uri() . '/assets/css/product-card.css', ['studio94-style'], file_exists($card_css_path) ? filemtime($card_css_path) : $theme_version);

    // Main theme script (logo flip, tabs, gallery/lightbox, single-product AJAX cart, variation dropdowns)
    $main_js_path = get_stylesheet_directory() . '/assets/js/main.js';
    wp_enqueue_script('studio94-main', get_template_directory_uri() . '/assets/js/main.js', ['jquery'], file_exists($main_js_path) ? filemtime($main_js_path) : $theme_version, true);

    // Quick view modal – depends on main.js (uses window.studio94InitQtyButtons)
    $modals_js_path = get_stylesheet_directory() . '/assets/js/product-modals.js';
    wp_enqueue_script('studio94-modals', get_template_directory_uri() . '/assets/js/product-modals.js', ['jquery', 'studio94-main'], file_exists($modals_js_path) ? filemtime($modals_js_path) : $theme_version, true);

    $card_js_path = get_stylesheet_directory() . '/assets/js/product-card.js';
    wp_enqueue_script('studio94-product-card', get_template_directory_uri() . '/assets/js/product-card.js', [], file_exists($card_js_path) ? filemtime($card_js_path) : $theme_version, true);

    if (class_exists('WooCommerce')) {
        // Attached to main.js (owns single-product add/update/remove). It's a global, so product-card.js can read it too.
        $cart_config = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'wcAjaxUrl' => WC_AJAX::get_endpoint('%%endpoint%%'),
            'removeNonce' => wp_create_nonce('studio94_remove_from_cart'),
        ];
        wp_localize_script('studio94-main', 'studio94Cart', $cart_config);
    }
}
add_action('wp_enqueue_scripts', 'studio94_enqueue_scripts');

function studio94_dequeue_wc_add_to_cart_script() {
    wp_dequeue_script('wc-add-to-cart');
    wp_deregister_script('wc-add-to-cart');
}
add_action('wp_enqueue_scripts', 'studio94_dequeue_wc_add_to_cart_script', 20);

function studio94_ajax_remove_from_cart() {
    check_ajax_referer('studio94_remove_from_cart', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(['message' => 'Cart unavailable']);
    }

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    if (!$product_id) {
        wp_send_json_error(['message' => 'Missing product ID']);
    }

    $removed = false;
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $match_id = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
        if ((int) $cart_item['product_id'] === $product_id || (int) $match_id === $product_id) {
            WC()->cart->remove_cart_item($cart_item_key);
            $removed = true;
            break;
        }
    }

    if (!$removed) {
        wp_send_json_error(['message' => 'Item not found in cart']);
    }

    WC()->cart->calculate_totals();

    wp_send_json_success([
        'fragments' => apply_filters('woocommerce_add_to_cart_fragments', []),
        'cart_hash' => WC()->cart->get_cart_hash(),
        'cart_count' => WC()->cart->get_cart_contents_count(),
    ]);
}
add_action('wp_ajax_studio94_remove_from_cart', 'studio94_ajax_remove_from_cart');
add_action('wp_ajax_nopriv_studio94_remove_from_cart', 'studio94_ajax_remove_from_cart');

function studio94_add_cart_count_fragment($fragments) {
    if (function_exists('WC') && WC()->cart) {
        $fragments['studio94_cart_count'] = WC()->cart->get_cart_contents_count();
    }
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'studio94_add_cart_count_fragment');
add_filter('woocommerce_update_order_review_fragments', 'studio94_add_cart_count_fragment');

function studio94_add_meta_boxes() {
    foreach (['post', 'page'] as $screen) {
        add_meta_box('studio94_header_meta_box', 'Header Settings', 'studio94_header_meta_box_html', $screen, 'normal', 'high');
    }
    add_meta_box('studio94_inner_sidebar_box', 'Inner Sidebar (Right Side)', 'studio94_inner_sidebar_box_html', 'page', 'normal', 'high');
}
add_action('add_meta_boxes', 'studio94_add_meta_boxes');

function studio94_header_meta_box_html($post) {
    $eyebrow = get_post_meta($post->ID, '_studio94_eyebrow', true) ?: get_post_meta($post->ID, '_studio94_subtitle', true);
    $icon = get_post_meta($post->ID, '_studio94_eyebrow_icon', true);
    $subtitle = get_post_meta($post->ID, '_studio94_true_subtitle', true);
    $align = get_post_meta($post->ID, '_studio94_header_align', true) ?: 'left';
    echo '<label style="display:block; margin-bottom:5px; font-weight:600;">Eyebrow (Above Title)</label><input type="text" name="studio94_eyebrow" value="' . esc_attr($eyebrow) . '" style="width:100%; padding: 8px; margin-bottom: 15px;" />';
    echo '<label style="display:block; margin-bottom:5px; font-weight:600;">Eyebrow Icon (SVG or Image URL)</label><textarea name="studio94_eyebrow_icon" style="width:100%; padding: 8px; margin-bottom: 15px;" rows="3">' . esc_textarea($icon) . '</textarea>';
    echo '<label style="display:block; margin-bottom:5px; font-weight:600;">Sub-title (Below Title)</label><textarea name="studio94_true_subtitle" style="width:100%; padding: 8px; margin-bottom: 15px;" rows="2">' . esc_textarea($subtitle) . '</textarea>';
    echo '<label style="display:block; margin-bottom:5px; font-weight:600;">Alignment</label><select name="studio94_header_align" style="width:100%; padding: 8px;"><option value="left" ' . selected($align, 'left', false) . '>Left (Default)</option><option value="center" ' . selected($align, 'center', false) . '>Center</option><option value="right" ' . selected($align, 'right', false) . '>Right</option></select>';
}

function studio94_inner_sidebar_box_html($post) {
    $enabled = get_post_meta($post->ID, '_studio94_inner_sidebar_enabled', true);
    $content = get_post_meta($post->ID, '_studio94_inner_sidebar_content', true);
    echo '<p><label><input type="checkbox" name="studio94_inner_sidebar_enabled" value="1" ' . checked($enabled, 1, false) . ' /> <strong>Enable Inner Sidebar on this page</strong></label></p><p>Add your links, terms, or secondary navigation below.</p>';
    wp_editor($content, 'studio94_inner_sidebar_content', ['textarea_rows' => 6]);
}

function studio94_save_meta_boxes($post_id) {
    if (array_key_exists('studio94_eyebrow', $_POST)) {
        update_post_meta($post_id, '_studio94_eyebrow', sanitize_text_field($_POST['studio94_eyebrow']));
        update_post_meta($post_id, '_studio94_subtitle', sanitize_text_field($_POST['studio94_eyebrow']));
    }
    if (array_key_exists('studio94_eyebrow_icon', $_POST)) {
        update_post_meta($post_id, '_studio94_eyebrow_icon', current_user_can('unfiltered_html') ? $_POST['studio94_eyebrow_icon'] : wp_kses_post($_POST['studio94_eyebrow_icon']));
    }
    if (array_key_exists('studio94_true_subtitle', $_POST)) {
        update_post_meta($post_id, '_studio94_true_subtitle', sanitize_text_field($_POST['studio94_true_subtitle']));
    }
    if (array_key_exists('studio94_header_align', $_POST)) {
        update_post_meta($post_id, '_studio94_header_align', sanitize_text_field($_POST['studio94_header_align']));
    }
    if (isset($_POST['post_type']) && 'page' === $_POST['post_type']) {
        update_post_meta($post_id, '_studio94_inner_sidebar_enabled', isset($_POST['studio94_inner_sidebar_enabled']) ? 1 : 0);
        if (array_key_exists('studio94_inner_sidebar_content', $_POST)) {
            update_post_meta($post_id, '_studio94_inner_sidebar_content', wp_kses_post($_POST['studio94_inner_sidebar_content']));
        }
    }
}
add_action('save_post', 'studio94_save_meta_boxes');

function studio94_custom_sorting_labels($options) {
    $options['menu_order'] = 'Default sorting';
    $options['popularity'] = 'Sort by popularity';
    $options['rating']     = 'Sort by average rating';
    $options['date']       = 'Sort by latest';
    $options['price']      = 'Sort by price: low to high';
    $options['price-desc'] = 'Sort by price: high to low';
    return $options;
}
add_filter('woocommerce_catalog_orderby', 'studio94_custom_sorting_labels');

add_filter('auto_plugin_update_send_email', '__return_false');
add_filter('auto_theme_update_send_email', '__return_false');
add_filter('auto_core_update_send_email', '__return_false');

function studio94_add_image_to_thankyou($item_name, $item, $is_visible) {
    if (is_wc_endpoint_url('order-received') || is_wc_endpoint_url('view-order')) {
        if ($product = $item->get_product()) {
            $thumbnail = $product->get_image([100, 100], ['style' => 'border-radius: 8px; object-fit: cover;', 'class' => 's94-order-item-img']);
            $item_name = $thumbnail . '<div class="s94-order-item-title">' . $item_name . '</div>';
        }
    }
    return $item_name;
}
add_filter('woocommerce_order_item_name', 'studio94_add_image_to_thankyou', 10, 3);

function studio94_custom_instock_filter($q) {
    if (!is_admin() && $q->is_main_query() && isset($_GET['instock_post']) && $_GET['instock_post'] == '1') {
        $meta_query = $q->get('meta_query') ?: [];
        $meta_query[] = ['key' => '_stock_status', 'value' => 'instock', 'compare' => '='];
        $q->set('meta_query', $meta_query);
    }
}
add_action('woocommerce_product_query', 'studio94_custom_instock_filter');

function studio94_register_pet_profiles() {
    register_post_type('pet_profile', [
        'labels'              => ['name' => 'Pet Profiles', 'singular_name' => 'Pet Profile'],
        'public'              => true,
        'has_archive'         => false,
        'rewrite'             => ['slug' => 'pet'],
        'supports'            => ['title'],
        'exclude_from_search' => true
    ]);
}
add_action('init', 'studio94_register_pet_profiles');

add_action('wp_footer', 'studio94_enforce_pin_limit');
function studio94_enforce_pin_limit() {
    if (!function_exists('is_product') || !is_product()) return;
?>
    <script>
        jQuery(document).ready(function($) {
            $('label').filter(function() {
                return $(this).text().indexOf('6-Digit Edit PIN') > -1;
            }).each(function() {
                var $input = $(this).closest('div, .wapf-field-row, .yith-wapo-block').find('input[type="text"]');
                if ($input.length) {
                    $input.attr({
                        'maxlength': '6',
                        'pattern': '\\d*',
                        'title': 'Please enter exactly 6 numbers'
                    });
                    $input.on('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    });
                }
            });
        });
    </script>
<?php
}

add_filter('wp_insert_post_data', 'studio94_random_pet_slug', 10, 2);
function studio94_random_pet_slug($data, $postarr) {
    if ($data['post_type'] === 'pet_profile' && $data['post_status'] !== 'trash') {
        if (empty($postarr['ID']) || !preg_match('/^[0-9]{6}$/', $data['post_name'])) {
            $is_unique = false;
            while (!$is_unique) {
                $random_slug = sprintf('%06d', mt_rand(100000, 999999));
                $exists = get_posts([
                    'name'        => $random_slug,
                    'post_type'   => 'pet_profile',
                    'post_status' => 'any',
                    'numberposts' => 1
                ]);
                if (empty($exists)) {
                    $data['post_name'] = $random_slug;
                    $is_unique = true;
                }
            }
        }
    }
    return $data;
}

add_action('save_post_pet_profile', 'studio94_auto_generate_public_pin', 10, 3);
function studio94_auto_generate_public_pin($post_id, $post, $update) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
    $existing_pin = get_post_meta($post_id, 'public_pin', true);
    if (empty($existing_pin)) {
        $random_pin = sprintf('%06d', mt_rand(100000, 999999));
        update_post_meta($post_id, 'public_pin', $random_pin);
    }
}

add_action('add_meta_boxes', 'studio94_add_pet_qr_meta_box');
function studio94_add_pet_qr_meta_box() {
    add_meta_box('pet_qr_box', 'Tag QR Code & PIN', 'studio94_pet_qr_html', 'pet_profile', 'side', 'high');
}

function studio94_pet_qr_html($post) {
    $public_pin = get_post_meta($post->ID, 'public_pin', true);
    $short_url = 's94.uk/' . $post->post_name;

    if (empty($public_pin) || $post->post_status !== 'publish') {
        echo '<p style="color:#d9534f; font-weight:600;">Publish this profile first to generate the unique 6-digit slug and PIN.</p>';
        return;
    }
?>
    <style>
        #s94-qr-wrapper svg {
            width: 100% !important;
            height: auto !important;
            max-width: 296px;
            display: block;
            margin: 0 auto;
        }
    </style>
    <div id="s94-qr-wrapper" style="text-align:center; padding: 10px 0;">
        <div id="qr-container" style="margin-bottom: 15px; border: 1px solid #eaeaea; padding: 10px; background: #fff; display: flex; justify-content: center;"></div>
        <button id="download-qr" class="button button-primary" style="width: 100%;">Download Vector (SVG)</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode-svg@1.1.0/lib/qrcode.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var qrcode = new QRCode({
                content: "<?php echo esc_js($short_url); ?>",
                padding: 0,
                width: 256,
                height: 256,
                color: "#000000",
                background: "#ffffff",
                ecl: "L"
            });

            var svgData = qrcode.svg();
            var parser = new DOMParser();
            var doc = parser.parseFromString(svgData, "image/svg+xml");
            var qrContent = doc.documentElement.innerHTML;

            var combinedSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="296" height="366" viewBox="0 0 296 366" style="background:#ffffff;">' +
                '<g transform="translate(20, 20)">' + qrContent + '</g>' +
                '<text x="148" y="335" font-family="Arial, sans-serif" font-size="46" font-weight="bold" letter-spacing="4" text-anchor="middle" fill="#000000"><?php echo esc_js($public_pin); ?></text>' +
                '</svg>';

            document.getElementById('qr-container').innerHTML = combinedSvg;

            document.getElementById('download-qr').addEventListener('click', function(e) {
                e.preventDefault();
                var blob = new Blob([combinedSvg], {
                    type: "image/svg+xml;charset=utf-8"
                });
                var url = URL.createObjectURL(blob);
                var a = document.createElement("a");
                a.href = url;
                a.download = "<?php echo esc_js($post->post_name); ?>-tag-qr.svg";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });
        });
    </script>
<?php
}

add_filter('manage_pet_profile_posts_columns', 'studio94_pet_table_columns');
function studio94_pet_table_columns($columns) {
    $columns['public_pin'] = 'Public PIN';
    $columns['owner_pin']  = 'Private PIN';
    return $columns;
}

add_action('manage_pet_profile_posts_custom_column', 'studio94_pet_table_column_data', 10, 2);
function studio94_pet_table_column_data($column, $post_id) {
    if ($column === 'public_pin' || $column === 'owner_pin') {
        $pin = get_post_meta($post_id, $column, true);
        if ($pin) {
            echo '<span style="font-family:monospace; font-size:14px; font-weight:bold;">' . esc_html($pin) . '</span> ';
            echo '<button type="button" class="button button-small" onclick="navigator.clipboard.writeText(\'' . esc_js($pin) . '\'); this.innerText=\'Copied!\'; setTimeout(() => this.innerText=\'Copy\', 2000);">Copy</button>';
        } else {
            echo '<span style="color:#999;">—</span>';
        }
    }
}

// Automatically redirect short domain (s94.uk/123456) to full pet profile
add_action('init', 'studio94_short_domain_redirect');
function studio94_short_domain_redirect() {
    // Check if the visitor is using the short domain
    if (strpos($_SERVER['HTTP_HOST'], 's94.uk') !== false) {
        // Grab the exact path they requested (e.g., /328789)
        $path = $_SERVER['REQUEST_URI'];

        // Redirect them permanently to the proper pet profile URL
        wp_redirect('https://studio94.uk/pet' . $path, 301);
        exit;
    }
}

// Hide SKU completely if empty or N/A
add_filter('wc_product_sku_enabled', 'studio94_hide_empty_sku');
function studio94_hide_empty_sku($enabled) {
    if (! is_admin() && is_product()) {
        global $product;
        if (! $product || ! $product->get_sku() || $product->get_sku() === 'N/A') {
            return false;
        }
    }
    return $enabled;
}
// Remove WooCommerce notices from the Shop, Archive, and Cart pages
add_action('wp', 'studio94_remove_annoying_notices');
function studio94_remove_annoying_notices() {
    if (is_shop() || is_product_category() || is_product_tag()) {
        remove_action('woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10);
    }
    if (is_cart()) {
        remove_action('woocommerce_before_cart', 'woocommerce_output_all_notices', 10);
    }
}
