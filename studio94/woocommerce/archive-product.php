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
				<label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; color:var(--text-main); margin:0;">
					<input type="checkbox" name="instock_post" value="1" style="display:none;" <?php checked(isset($_GET['instock_post']), true); ?> />
					<div class="s94-toggle" style="order:-1; margin:0;"></div>
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
	<?php
	if (wc_get_loop_prop('total')) {
		while (have_posts()) {
			the_post();
			global $product;
			?>
			<li <?php wc_product_class( '', $product ); ?>>
				<div class="product-img-wrap">
					<?php if ( $product->is_on_sale() ) echo '<span class="onsale">SALE!</span>'; ?>
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" style="display:block; width:100%; height:100%;">
						<?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
					</a>
					<div class="quick-view-overlay">
						<button class="quick-view-btn" data-id="<?php echo esc_attr( $product->get_id() ); ?>">Quick View</button>
					</div>
				</div>

				<div class="product-info-wrap">
					<div class="product-title-row">
						<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
							<h3 class="woocommerce-loop-product__title"><?php echo esc_html( $product->get_name() ); ?></h3>
						</a>
						<?php if ( $product->get_rating_count() > 0 ) : ?>
							<div class="loop-product-rating" style="margin-top: 0.4rem;">
								<?php echo wc_get_rating_html( $product->get_average_rating() ); ?>
							</div>
						<?php endif; ?>
					</div>
					
					<div class="product-price-row">
						<span class="price"><?php echo $product->get_price_html(); ?></span>
						<?php 
						echo sprintf(
							'<a href="%s" data-quantity="1" class="%s" %s>%s</a>',
							esc_url( $product->add_to_cart_url() ),
							esc_attr( implode( ' ', array(
								'button add_to_cart_button',
								'product_type_' . $product->get_type(),
								$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
								$product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
							) ) ),
							wc_implode_html_attributes( array(
								'data-product_id'  => $product->get_id(),
								'data-product_sku' => $product->get_sku(),
								'aria-label'       => $product->add_to_cart_description(),
								'rel'              => 'nofollow',
							) ),
							esc_html__( 'Add to cart', 'woocommerce' )
						);
						?>
					</div>
				</div>

		        <div class="qv-data" style="display:none;" 
		             data-title="<?php echo esc_attr( $product->get_name() ); ?>" 
		             data-img="<?php echo esc_url( wp_get_attachment_image_url( $product->get_image_id(), 'large' ) ); ?>" 
		             data-url="<?php echo esc_url( $product->get_permalink() ); ?>">
		             <?php echo wp_kses_post( apply_filters( 'woocommerce_short_description', $product->get_short_description() ) ); ?>
		        </div>
		        <div class="qv-rating-data" style="display:none;">
		            <?php echo $product->get_rating_count() > 0 ? wc_get_rating_html( $product->get_average_rating() ) : ''; ?>
		        </div>
		        <div class="qv-price-data" style="display:none;"><?php echo $product->get_price_html(); ?></div>
				<div class="qv-cart-data" style="display:none;">
					<?php 
					ob_start();
					woocommerce_template_single_add_to_cart(); 
					echo htmlspecialchars( ob_get_clean() );
					?>
				</div>
			</li>
			<?php
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