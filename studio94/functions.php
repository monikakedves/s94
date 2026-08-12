<?php
function studio94_theme_setup() {
	add_theme_support('woocommerce');
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');

	// Disables the buggy Block Widget editor and restores Classic Widgets
	remove_theme_support('widgets-block-editor');

	// Register the Sidebar Navigation Menu
	register_nav_menus(array(
		'sidebar_menu' => 'Sidebar Navigation Menu'
	));
}
add_action('after_setup_theme', 'studio94_theme_setup');

// Register Footer Widgets
function studio94_widgets_init() {
	register_sidebar(array(
		'name'          => 'Footer Column 1 (Contact)',
		'id'            => 'footer-1',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="footer-heading">',
		'after_title'   => '</h4>',
	));

	register_sidebar(array(
		'name'          => 'Footer Column 2 (Links)',
		'id'            => 'footer-2',
		'before_widget' => '<div class="footer-links">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="footer-heading">',
		'after_title'   => '</h4>',
	));
}
add_action('widgets_init', 'studio94_widgets_init');

// Add Landing Page & Color fields to the WordPress Customizer
function studio94_customize_register($wp_customize) {
	// Landing Page Section
	$wp_customize->add_section('studio94_hero_section', array(
		'title'      => 'Landing Page Hero',
		'priority'   => 30,
	));

	$wp_customize->add_setting('hero_badge_text', array('default' => 'Designed & Hand-Pressed in the UK'));
	$wp_customize->add_control('hero_badge_text', array('label' => 'Hero Badge Text', 'section' => 'studio94_hero_section'));

	$wp_customize->add_setting('hero_title', array('default' => 'Wearable Art.<br><span>Elderly Emo Approved.</span>'));
	$wp_customize->add_control('hero_title', array('label' => 'Hero Title (HTML allowed)', 'section' => 'studio94_hero_section', 'type' => 'textarea'));

	$wp_customize->add_setting('hero_subtitle', array('default' => 'Heavyweight tees, original linocut block prints, canvas totes, and temporary flash sheets for those who never grew out of it.'));
	$wp_customize->add_control('hero_subtitle', array('label' => 'Hero Subtitle', 'section' => 'studio94_hero_section', 'type' => 'textarea'));

	$wp_customize->add_setting('hero_btn_text', array('default' => 'Explore Collection'));
	$wp_customize->add_control('hero_btn_text', array('label' => 'Button Text', 'section' => 'studio94_hero_section'));

	$wp_customize->add_setting('hero_btn_link', array('default' => '/shop/'));
	$wp_customize->add_control('hero_btn_link', array('label' => 'Button URL', 'section' => 'studio94_hero_section'));

	// Main Brand Color (Added to standard 'Colors' section)
	$wp_customize->add_setting('brand_color', array(
		'default'           => '#df8ca3',
		'sanitize_callback' => 'sanitize_hex_color',
	));
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'brand_color', array(
		'label'    => 'Main Brand Color',
		'section'  => 'colors',
	)));
}
add_action('customize_register', 'studio94_customize_register');

// Inject the custom color into the head of the site
function studio94_customizer_css() {
	$brand_color = get_theme_mod('brand_color', '#df8ca3');
?>
	<style type="text/css">
		:root {
			--brand-pink: <?php echo esc_attr($brand_color); ?>;
			/* Appending '1A' to a hex code creates a 10% opacity version of that color for backgrounds */
			--brand-pink-light: <?php echo esc_attr($brand_color); ?>1A;
		}
	</style>
<?php
}
add_action('wp_head', 'studio94_customizer_css');

// Enqueue Styles with Cache Busting
// Enqueue Styles & Scripts with Cache Busting
function studio94_enqueue_scripts() {
	wp_enqueue_style('studio94-fonts', 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap', false);
	$theme_version = filemtime(get_stylesheet_directory() . '/style.css');
	wp_enqueue_style('studio94-style', get_stylesheet_uri(), array(), $theme_version);
    
	// Enqueue the new Product Modals JS file
	wp_enqueue_script('studio94-modals', get_template_directory_uri() . '/assets/js/product-modals.js', array(), $theme_version, true);
}
add_action('wp_enqueue_scripts', 'studio94_enqueue_scripts');

// --- Custom Meta Boxes for Eyebrow & Inner Sidebar ---

function studio94_add_meta_boxes() {
	$screens = ['post', 'page'];
	foreach ($screens as $screen) {
		add_meta_box(
			'studio94_header_meta_box',
			'Header Settings (Eyebrow, Sub-title, Alignment)',
			'studio94_header_meta_box_html',
			$screen,
			'normal',
			'high'
		);
	}
	add_meta_box(
		'studio94_inner_sidebar_box',
		'Inner Sidebar (Right Side)',
		'studio94_inner_sidebar_box_html',
		'page',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'studio94_add_meta_boxes');

function studio94_header_meta_box_html($post) {
	// Falls back to old _studio94_subtitle key so you don't lose existing data
	$eyebrow = get_post_meta($post->ID, '_studio94_eyebrow', true) ?: get_post_meta($post->ID, '_studio94_subtitle', true);
	$icon = get_post_meta($post->ID, '_studio94_eyebrow_icon', true);
	$subtitle = get_post_meta($post->ID, '_studio94_true_subtitle', true);
	$align = get_post_meta($post->ID, '_studio94_header_align', true) ?: 'left';

	echo '<label style="display:block; margin-bottom:5px; font-weight:600;">Eyebrow (Above Title)</label>';
	echo '<input type="text" name="studio94_eyebrow" value="' . esc_attr($eyebrow) . '" style="width:100%; padding: 8px; margin-bottom: 15px;" />';

	echo '<label style="display:block; margin-bottom:5px; font-weight:600;">Eyebrow Icon (SVG or Image URL)</label>';
	echo '<textarea name="studio94_eyebrow_icon" style="width:100%; padding: 8px; margin-bottom: 15px;" rows="3">' . esc_textarea($icon) . '</textarea>';

	echo '<label style="display:block; margin-bottom:5px; font-weight:600;">Sub-title (Below Title)</label>';
	echo '<textarea name="studio94_true_subtitle" style="width:100%; padding: 8px; margin-bottom: 15px;" rows="2">' . esc_textarea($subtitle) . '</textarea>';

	echo '<label style="display:block; margin-bottom:5px; font-weight:600;">Alignment</label>';
	echo '<select name="studio94_header_align" style="width:100%; padding: 8px;">';
	echo '<option value="left" ' . selected($align, 'left', false) . '>Left (Default)</option>';
	echo '<option value="center" ' . selected($align, 'center', false) . '>Center</option>';
	echo '<option value="right" ' . selected($align, 'right', false) . '>Right</option>';
	echo '</select>';
}

function studio94_inner_sidebar_box_html($post) {
	$enabled = get_post_meta($post->ID, '_studio94_inner_sidebar_enabled', true);
	$content = get_post_meta($post->ID, '_studio94_inner_sidebar_content', true);

	echo '<p><label><input type="checkbox" name="studio94_inner_sidebar_enabled" value="1" ' . checked($enabled, 1, false) . ' /> <strong>Enable Inner Sidebar on this page</strong></label></p>';
	echo '<p>Add your links, terms, or secondary navigation below. This will stick to the right side on desktop and stack above the content on mobile.</p>';
	wp_editor($content, 'studio94_inner_sidebar_content', array('textarea_rows' => 6));
}

function studio94_save_meta_boxes($post_id) {
	if (array_key_exists('studio94_eyebrow', $_POST)) {
		update_post_meta($post_id, '_studio94_eyebrow', sanitize_text_field($_POST['studio94_eyebrow']));
		// Overwrite the legacy subtitle key so old data officially migrates to "eyebrow"
		update_post_meta($post_id, '_studio94_subtitle', sanitize_text_field($_POST['studio94_eyebrow']));
	}
	if (array_key_exists('studio94_eyebrow_icon', $_POST)) {
		$icon = current_user_can('unfiltered_html') ? $_POST['studio94_eyebrow_icon'] : wp_kses_post($_POST['studio94_eyebrow_icon']);
		update_post_meta($post_id, '_studio94_eyebrow_icon', $icon);
	}
	if (array_key_exists('studio94_true_subtitle', $_POST)) {
		update_post_meta($post_id, '_studio94_true_subtitle', sanitize_text_field($_POST['studio94_true_subtitle']));
	}
	if (array_key_exists('studio94_header_align', $_POST)) {
		update_post_meta($post_id, '_studio94_header_align', sanitize_text_field($_POST['studio94_header_align']));
	}

	if (isset($_POST['post_type']) && 'page' === $_POST['post_type']) {
		$enabled = isset($_POST['studio94_inner_sidebar_enabled']) ? 1 : 0;
		update_post_meta($post_id, '_studio94_inner_sidebar_enabled', $enabled);

		if (array_key_exists('studio94_inner_sidebar_content', $_POST)) {
			update_post_meta($post_id, '_studio94_inner_sidebar_content', wp_kses_post($_POST['studio94_inner_sidebar_content']));
		}
	}
}
add_action('save_post', 'studio94_save_meta_boxes');
