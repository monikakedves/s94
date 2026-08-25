<?php
/**
 * Product Card
 *
 * Renders a single <li class="product"> card for the shop grid / related
 * products list, including the hidden Quick View data blocks. Used by
 * woocommerce/archive-product.php and template-parts/related-products.php
 * so the card markup only has to be maintained in one place.
 *
 * Usage: studio94_render_product_card( $product );
 *
 * @param WC_Product $product
 */
if ( ! function_exists( 'studio94_render_product_card' ) ) :

function studio94_render_product_card( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$product_id = $product->get_id();
	$permalink  = $product->get_permalink();
	$title      = $product->get_name();
	$price_html = $product->get_price_html();
	$thumb_url  = get_the_post_thumbnail_url( $product_id, 'woocommerce_thumbnail' );
	$full_url   = get_the_post_thumbnail_url( $product_id, 'large' );
	$short_desc = apply_filters( 'woocommerce_short_description', $product->get_short_description() );

	// Render the native WooCommerce add-to-cart form for this product for
	// use in the Quick View modal (same markup as the single product page).
	ob_start();
	$original_post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
	$GLOBALS['post'] = get_post( $product_id );
	setup_postdata( $GLOBALS['post'] );
	woocommerce_template_single_add_to_cart();
	$cart_form = ob_get_clean();
	if ( $original_post ) {
		$GLOBALS['post'] = $original_post;
	}
	wp_reset_postdata();

	$is_in_cart = false;
	if ( function_exists( 'WC' ) && WC()->cart ) {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$match_id = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
			if ( (int) $cart_item['product_id'] === $product_id || (int) $match_id === $product_id ) {
				$is_in_cart = true;
				break;
			}
		}
	}
	?>
	<li <?php wc_product_class( '', $product ); ?>>
		<div class="product-img-wrap">
			<?php if ( $product->is_on_sale() ) : ?>
				<span class="onsale">SALE!</span>
			<?php endif; ?>
			<a href="<?php echo esc_url( $permalink ); ?>" style="display:block; width:100%; height:100%;">
				<?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
			</a>
			<div class="quick-view-overlay">
				<button class="quick-view-btn" data-id="<?php echo esc_attr( $product_id ); ?>">Quick View</button>
			</div>
		</div>

		<div class="product-info-wrap">
			<div class="product-title-row">
				<a href="<?php echo esc_url( $permalink ); ?>">
					<h3 class="woocommerce-loop-product__title"><?php echo esc_html( $title ); ?></h3>
				</a>
				<?php if ( $product->get_rating_count() > 0 ) : ?>
					<div class="loop-product-rating" style="margin-top: 0.4rem;">
						<?php echo wc_get_rating_html( $product->get_average_rating() ); ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="product-price-row">
				<span class="price"><?php echo $price_html; ?></span>
				<button type="button"
					class="s94-cart-btn<?php echo $is_in_cart ? ' in-cart' : ''; ?>"
					data-product_id="<?php echo esc_attr( $product_id ); ?>"
					data-quantity="1"
					aria-label="<?php echo $is_in_cart ? esc_attr__( 'Remove from cart', 'woocommerce' ) : esc_attr( $product->add_to_cart_description() ); ?>">
					<span class="cart-icon icon-add" aria-hidden="true"></span>
					<span class="cart-icon icon-added" aria-hidden="true"></span>
					<span class="cart-icon icon-remove" aria-hidden="true"></span>
					<span class="screen-reader-text">Add to cart</span>
				</button>
			</div>
		</div>

		<!-- Hidden Quick View Data -->
		<div class="qv-data" style="display:none;"
			data-title="<?php echo esc_attr( $title ); ?>"
			data-img="<?php echo esc_url( $full_url ); ?>"
			data-url="<?php echo esc_url( $permalink ); ?>">
			<?php echo wp_kses_post( $short_desc ); ?>
		</div>
		<div class="qv-rating-data" style="display:none;">
			<?php echo $product->get_rating_count() > 0 ? wc_get_rating_html( $product->get_average_rating() ) : ''; ?>
		</div>
		<div class="qv-price-data" style="display:none;"><?php echo $price_html; ?></div>
		<div class="qv-cart-data" style="display:none;"><?php echo htmlspecialchars( $cart_form ); ?></div>
	</li>
	<?php
}

endif;
