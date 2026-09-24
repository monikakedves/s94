<?php
defined('ABSPATH') || exit;

get_header('shop');

remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

do_action('woocommerce_before_main_content');
?>

<header class="category-header">
	<?php if (apply_filters('woocommerce_show_page_title', true)) : ?>
		<h1><?php woocommerce_page_title(); ?></h1>
	<?php endif; ?>
	<?php do_action('woocommerce_archive_description'); ?>
</header>

<?php if (woocommerce_product_loop()) : ?>

	<div class="s94-shop-top-row">
		<form class="s94-custom-filters" action="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" method="get">
			<?php
			if ( isset( $_GET['s'] ) ) {
				echo '<input type="hidden" name="s" value="' . esc_attr( $_GET['s'] ) . '" />';
			}
			?>
			
			<?php if ( ! is_product_category() ) : ?>
				<div class="s94-filter-category">
					<?php
					$current_cat = isset($_GET['product_cat']) ? sanitize_text_field($_GET['product_cat']) : '';
					wp_dropdown_categories( array(
						'taxonomy'          => 'product_cat',
						'show_option_none'  => 'All categories',
						'option_none_value' => '',
						'value_field'       => 'slug',
						'selected'          => $current_cat,
						'hierarchical'      => 1,
						'name'              => 'product_cat',
						'class'             => 's94-select',
						'depth'             => 3,
						'hide_empty'        => 1,
					) );
					?>
				</div>
			<?php endif; ?>

			<div class="s94-filter-price">
				<input type="number" name="min_price" placeholder="Min £" value="<?php echo esc_attr( isset($_GET['min_price']) ? $_GET['min_price'] : '' ); ?>" />
				<span class="s94-separator">-</span>
				<input type="number" name="max_price" placeholder="Max £" value="<?php echo esc_attr( isset($_GET['max_price']) ? $_GET['max_price'] : '' ); ?>" />
			</div>

			<div class="s94-filter-instock">
				<label>
					<input type="checkbox" name="instock_post" value="1" <?php checked( isset( $_GET['instock_post'] ) && $_GET['instock_post'] === '1' ); ?> />
					<span class="s94-filter-instock-toggle"></span>
					In stock only
				</label>
			</div>

			<div class="s94-filter-actions">
				<button type="button" class="btn btn-outline s94-clear-btn">Clear filters</button>
			</div>
		</form>

		<div class="s94-sorting-wrap">
			<?php woocommerce_catalog_ordering(); ?>
		</div>
	</div>

	<div class="s94-shop-bottom-row">
		<?php woocommerce_result_count(); ?>
	</div>

	<div class="shop-controls" style="display:none;">
		<?php do_action('woocommerce_before_shop_loop'); ?>
	</div>

	<ul class="products custom-related columns-<?php echo esc_attr( wc_get_loop_prop( 'columns' ) ); ?>">
	<?php if (wc_get_loop_prop('total')) {
    while (have_posts()) {
        the_post();
        wc_get_template_part( 'content', 'product' );
    }
}
?>
	</ul>

	<div class="pagination">
		<?php do_action('woocommerce_after_shop_loop'); ?>
	</div>

<?php else : ?>
	
	<div class="s94-shop-top-row">
		<form class="s94-custom-filters" action="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" method="get">
			<?php if ( ! is_product_category() ) : ?>
				<div class="s94-filter-category">
					<?php
					wp_dropdown_categories( array(
						'taxonomy'          => 'product_cat',
						'show_option_none'  => 'All categories',
						'option_none_value' => '',
						'value_field'       => 'slug',
						'hierarchical'      => 1,
						'name'              => 'product_cat',
						'class'             => 's94-select',
						'hide_empty'        => 1,
					) );
					?>
				</div>
			<?php endif; ?>
			<div class="s94-filter-price">
				<input type="number" name="min_price" placeholder="Min £" />
				<span class="s94-separator">-</span>
				<input type="number" name="max_price" placeholder="Max £" />
			</div>
			<div class="s94-filter-actions">
				<button type="button" class="btn btn-outline s94-clear-btn">Clear filters</button>
			</div>
		</form>
	</div>

	<ul class="products custom-related columns-<?php echo esc_attr( wc_get_loop_prop( 'columns' ) ); ?>">
		<li class="s94-no-products">No products found matching your criteria.</li>
	</ul>

<?php endif; ?>

<?php 
get_template_part('template-parts/modal-quick-view'); 
do_action('woocommerce_after_main_content'); 
get_footer('shop'); 
?>