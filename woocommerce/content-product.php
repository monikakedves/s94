<?php
defined('ABSPATH') || exit;

global $product;

if (empty($product) || ! $product->is_visible()) {
    return;
}

$product_id = $product->get_id();
$permalink  = $product->get_permalink();
$title      = $product->get_name();
$price_html = $product->get_price_html();
$thumb_url  = get_the_post_thumbnail_url($product_id, 'woocommerce_thumbnail');
$full_url   = get_the_post_thumbnail_url($product_id, 'large');
$short_desc = apply_filters('woocommerce_short_description', $product->get_short_description());
$custom_range = get_post_meta($product_id, '_s94_custom_price_range', true);
ob_start();
woocommerce_template_single_add_to_cart();
$cart_form = ob_get_clean();

$is_in_cart = false;
if (function_exists('WC') && WC()->cart) {
    foreach (WC()->cart->get_cart() as $cart_item) {
        $match_id = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
        if ((int) $cart_item['product_id'] === $product_id || (int) $match_id === $product_id) {
            $is_in_cart = true;
            break;
        }
    }
}
?>
<li <?php wc_product_class('', $product); ?>>
    <div class="product-img-wrap">
        <?php if ($product->is_on_sale() && $product->is_in_stock()) : ?>
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/sale.svg'); ?>" class="onsale-svg" alt="Sale!">
        <?php endif; ?>

        <?php if (! $product->is_in_stock()) : ?>
            <span class="out-of-stock-pill">OUT OF STOCK</span>
        <?php endif; ?>

        <?php if (has_term('best-seller', 'product_tag', $product_id)) : ?>
            <span class="best-seller-badge">Best Seller</span>
        <?php endif; ?>

        <a href="<?php echo esc_url($permalink); ?>" style="display:block; width:100%; height:100%;">
            <?php echo $product->get_image('woocommerce_thumbnail'); ?>
        </a>
        <div class="quick-view-overlay">
            <button class="quick-view-btn" data-id="<?php echo esc_attr($product_id); ?>">Quick View</button>
        </div>
    </div>

    <div class="product-info-wrap">
        <div class="product-title-row">
            <a href="<?php echo esc_url($permalink); ?>">
                <h3 class="woocommerce-loop-product__title"><?php echo esc_html($title); ?></h3>
            </a>
            <?php if ($product->get_rating_count() > 0) : ?>
                <div class="loop-product-rating" style="margin-top: 0.4rem;">
                    <?php echo wc_get_rating_html($product->get_average_rating()); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-price-row">
            <span class="price" data-custom-range="<?php echo esc_attr($custom_range); ?>">
                <?php echo !empty($custom_range) ? '<span class="woocommerce-Price-amount amount"><bdi>' . esc_html($custom_range) . '</bdi></span>' : $price_html; ?>
            </span>

            <?php if ($product->is_type('variable')) : ?>
                <a href="<?php echo esc_url($permalink); ?>" class="s94-cart-btn is-variable" aria-label="<?php esc_attr_e('Select options', 'woocommerce'); ?>">
                    <span class="cart-icon icon-edit" aria-hidden="true" style="-webkit-mask-image:url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/cart_edit.svg'); ?>'); mask-image:url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/cart_edit.svg'); ?>'); background-color: #fff; opacity: 1;"></span>
                    <span class="screen-reader-text">Select options</span>
                </a>
            <?php elseif ($product->is_in_stock()) : ?>
                <button type="button" class="s94-cart-btn<?php echo $is_in_cart ? ' in-cart' : ''; ?>" data-product_id="<?php echo esc_attr($product_id); ?>" data-quantity="1" aria-label="<?php echo $is_in_cart ? esc_attr__('Remove from cart', 'woocommerce') : esc_attr($product->add_to_cart_description()); ?>">
                    <span class="cart-icon icon-add" aria-hidden="true"></span>
                    <span class="cart-icon icon-added" aria-hidden="true"></span>
                    <span class="cart-icon icon-remove" aria-hidden="true"></span>
                    <span class="screen-reader-text">Add to cart</span>
                </button>
            <?php else : ?>
                <button type="button" class="s94-cart-btn is-out-of-stock" disabled aria-label="<?php esc_attr_e('Out of stock', 'woocommerce'); ?>">
                    <span class="cart-icon icon-add" aria-hidden="true"></span>
                    <span class="cart-icon icon-added" aria-hidden="true"></span>
                    <span class="cart-icon icon-remove" aria-hidden="true"></span>
                    <span class="cart-icon icon-delete" aria-hidden="true"></span>
                    <span class="screen-reader-text">Out of stock</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Hidden Quick View Data -->
    <div class="qv-data" style="display:none;" data-title="<?php echo esc_attr($title); ?>" data-img="<?php echo esc_url($full_url); ?>" data-url="<?php echo esc_url($permalink); ?>">
        <?php echo wp_kses_post($short_desc); ?>
    </div>
    <div class="qv-rating-data" style="display:none;">
        <?php echo $product->get_rating_count() > 0 ? wc_get_rating_html($product->get_average_rating()) : ''; ?>
    </div>
    <div class="qv-price-data" style="display:none;"><?php echo !empty($custom_range) ? '<span class="woocommerce-Price-amount amount"><bdi>' . esc_html($custom_range) . '</bdi></span>' : $price_html; ?></div>
    <div class="qv-custom-range-data" style="display:none;"><?php echo esc_attr($custom_range); ?></div>
    <div class="qv-cart-data" style="display:none;"><?php echo htmlspecialchars($cart_form); ?></div>
</li>