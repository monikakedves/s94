<?php
defined('ABSPATH') || exit;

global $product;

if (empty($product) || ! $product->is_visible()) {
    return;
}

$product_id = $product->get_id();
$permalink  = $product->get_permalink();
$title      = $product->get_name();

// Price Fetcher with Fallback for Fixed-Price Variable Products
$price_html = $product->get_price_html();
if (empty($price_html)) {
    $price_html = wc_price(wc_get_price_to_display($product));
}
$custom_range = get_post_meta($product_id, '_s94_custom_price_range', true);

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

// Fetch main image and gallery images for the slider
$main_image = $product->get_image('woocommerce_thumbnail', ['loading' => 'eager']);
$gallery_ids = $product->get_gallery_image_ids();
$images = array($main_image);
if (!empty($gallery_ids)) {
    foreach (array_slice($gallery_ids, 0, 4) as $id) { // Limit to 5 images total
        $images[] = wp_get_attachment_image($id, 'woocommerce_thumbnail', false, ['loading' => 'eager']);
    }
}
?>
<li <?php wc_product_class('', $product); ?>>

    <!-- Sale ribbon moved outside the image wrap to hug the card corner -->
    <?php if ($product->is_on_sale() && $product->is_in_stock()) : ?>
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/sale.svg'); ?>" class="onsale-svg" alt="Sale!">
    <?php endif; ?>

    <div class="product-img-wrap s94-slider-wrap">
        <?php if (! $product->is_in_stock()) : ?>
            <span class="out-of-stock-pill">OUT OF STOCK</span>
        <?php endif; ?>

        <?php if (has_term('best-seller', 'product_tag', $product_id)) : ?>
            <span class="best-seller-badge">Best Seller</span>
        <?php endif; ?>

        <a href="<?php echo esc_url($permalink); ?>" class="s94-slider-link">
            <div class="s94-slider-track">
                <?php foreach ($images as $img_html): ?>
                    <div class="s94-slide"><?php echo $img_html; ?></div>
                <?php endforeach; ?>
            </div>
        </a>

        <?php if (count($images) > 1): ?>
            <button class="s94-slider-arrow s94-prev" aria-label="Previous image">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </button>
            <button class="s94-slider-arrow s94-next" aria-label="Next image">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6" />
                </svg>
            </button>
        <?php endif; ?>
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
</li>