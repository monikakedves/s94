<?php
defined( 'ABSPATH' ) || exit;
global $product;
if ( empty( $product ) || ! $product->is_visible() ) return;
?>
<li <?php wc_product_class( '', $product ); ?>>
	<div class="product-img-wrap">
		<?php 
        // CORRECTED: Pointing to /img/ instead of /images/
        if ( $product->is_on_sale() ) {
            echo '<img src="' . esc_url( get_template_directory_uri() . '/assets/images/sale.svg' ) . '" class="onsale-svg" alt="Sale!">';
        }
        
        // Best Seller Badge
        if ( has_term( 'best-seller', 'product_tag', $product->get_id() ) ) {
            echo '<span class="best-seller-badge">Best Seller</span>';
        }
        ?>
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
		</div>
		<div class="product-price-row">
			<span class="price"><?php echo $product->get_price_html(); ?></span>
			<?php 
			echo sprintf( '<a href="%s" data-quantity="1" class="%s" %s>%s</a>', esc_url( $product->add_to_cart_url() ), esc_attr( implode( ' ', array( 'button add_to_cart_button', 'product_type_' . $product->get_type(), $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '', $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '' ) ) ), wc_implode_html_attributes( array( 'data-product_id' => $product->get_id(), 'data-product_sku' => $product->get_sku(), 'aria-label' => $product->add_to_cart_description(), 'rel' => 'nofollow' ) ), esc_html__( 'Add to cart', 'woocommerce' ) );
			?>
		</div>
	</div>

    <!-- Hidden Quick View Data -->
	<div class="qv-data" style="display:none;" data-title="<?php echo esc_attr( $product->get_name() ); ?>" data-img="<?php echo esc_url( wp_get_attachment_image_url( $product->get_image_id(), 'large' ) ); ?>" data-url="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo wp_kses_post( apply_filters( 'woocommerce_short_description', $product->get_short_description() ) ); ?></div>
	<div class="qv-rating-data" style="display:none;"></div>
	<div class="qv-price-data" style="display:none;"><?php echo $product->get_price_html(); ?></div>
	<div class="qv-cart-data" style="display:none;"><?php ob_start(); woocommerce_template_single_add_to_cart(); echo htmlspecialchars( ob_get_clean() ); ?></div>
</li>