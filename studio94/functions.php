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
	register_sidebar(['name'=>'Footer Column 1 (Contact)','id'=>'footer-1','before_widget'=>'<div>','after_widget'=>'</div>','before_title'=>'<h4 class="footer-heading">','after_title'=>'</h4>']);
	register_sidebar(['name'=>'Footer Column 2 (Links)','id'=>'footer-2','before_widget'=>'<div class="footer-links">','after_widget'=>'</div>','before_title'=>'<h4 class="footer-heading">','after_title'=>'</h4>']);
	register_sidebar(['name'=>'Shop Filters (Top Bar)','id'=>'shop-filters','before_widget'=>'<div class="shop-filter-widget %2$s">','after_widget'=>'</div>','before_title'=>'<h4 class="filter-heading">','after_title'=>'</h4>']);
}
add_action('widgets_init', 'studio94_widgets_init');

function studio94_customize_register($wp_customize) {
	$wp_customize->add_section('studio94_hero_section', ['title'=>'Landing Page Hero','priority'=>30]);
	$wp_customize->add_setting('hero_badge_text', ['default'=>'Designed & Hand-Pressed in the UK']);
	$wp_customize->add_control('hero_badge_text', ['label'=>'Hero Badge Text','section'=>'studio94_hero_section']);
	$wp_customize->add_setting('hero_title', ['default'=>'Wearable Art.<br><span>Elderly Emo Approved.</span>']);
	$wp_customize->add_control('hero_title', ['label'=>'Hero Title (HTML allowed)','section'=>'studio94_hero_section','type'=>'textarea']);
	$wp_customize->add_setting('hero_subtitle', ['default'=>'Heavyweight tees, original linocut block prints, canvas totes, and temporary flash sheets for those who never grew out of it.']);
	$wp_customize->add_control('hero_subtitle', ['label'=>'Hero Subtitle','section'=>'studio94_hero_section','type'=>'textarea']);
	$wp_customize->add_setting('hero_btn_text', ['default'=>'Explore Collection']);
	$wp_customize->add_control('hero_btn_text', ['label'=>'Button Text','section'=>'studio94_hero_section']);
	$wp_customize->add_setting('hero_btn_link', ['default'=>'/shop/']);
	$wp_customize->add_control('hero_btn_link', ['label'=>'Button URL','section'=>'studio94_hero_section']);
	$wp_customize->add_setting('brand_color', ['default'=>'#df8ca3','sanitize_callback'=>'sanitize_hex_color']);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'brand_color', ['label'=>'Main Brand Color','section'=>'colors']));
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
	wp_enqueue_script('studio94-modals', get_template_directory_uri() . '/assets/js/product-modals.js', [], $theme_version, true);
}
add_action('wp_enqueue_scripts', 'studio94_enqueue_scripts');

function studio94_add_meta_boxes() {
	foreach (['post', 'page'] as $screen) {
		add_meta_box('studio94_header_meta_box', 'Header Settings (Eyebrow, Sub-title, Alignment)', 'studio94_header_meta_box_html', $screen, 'normal', 'high');
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
	echo '<p><label><input type="checkbox" name="studio94_inner_sidebar_enabled" value="1" ' . checked($enabled, 1, false) . ' /> <strong>Enable Inner Sidebar on this page</strong></label></p><p>Add your links, terms, or secondary navigation below. This will stick to the right side on desktop and stack above the content on mobile.</p>';
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