<?php
defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>
</div>
</div>

<div class="studio94-cart-layout">

    <div class="studio94-cart-main">

        <style>
            #s94-empty-cart-btn {
                border: 2px solid #d9534f !important;
                color: #d9534f !important;
                background-color: transparent !important;
                padding: 0.5rem 25px !important;
                font-size: 0.9rem !important;
                margin: 0 0 1.5rem auto !important;
                width: fit-content !important;
                display: inline-flex !important;
                transition: all 0.3s ease !important;
            }

            #s94-empty-cart-btn:hover {
                background-color: #d9534f !important;
                color: #fff !important;
            }
        </style>

        <div style="display: flex; justify-content: flex-end; width: 100%;">
            <button type="button" id="s94-empty-cart-btn" class="btn btn-outline">Empty Cart</button>
        </div>

        <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
            <?php do_action('woocommerce_before_cart_table'); ?>

            <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
                <thead>
                    <tr>
                        <th class="product-remove"><span class="screen-reader-text"><?php esc_html_e('Remove item', 'woocommerce'); ?></span></th>
                        <th class="product-thumbnail"><span class="screen-reader-text"><?php esc_html_e('Thumbnail image', 'woocommerce'); ?></span></th>
                        <th class="product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                        <th class="product-price"><?php esc_html_e('Price', 'woocommerce'); ?></th>
                        <th class="product-quantity"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
                        <th class="product-subtotal"><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php do_action('woocommerce_before_cart_contents'); ?>

                    <?php
                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                        $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                            $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                    ?>
                            <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">

                                <td class="product-remove">
                                    <?php
                                    echo apply_filters(
                                        'woocommerce_cart_item_remove_link',
                                        sprintf(
                                            '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                                            esc_url(wc_get_cart_remove_url($cart_item_key)),
                                            esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($_product->get_name()))),
                                            esc_attr($product_id),
                                            esc_attr($_product->get_sku())
                                        ),
                                        $cart_item_key
                                    );
                                    ?>
                                </td>

                                <td class="product-thumbnail">
                                    <?php
                                    $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);

                                    if (! $product_permalink) {
                                        echo $thumbnail;
                                    } else {
                                        printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail);
                                    }
                                    ?>
                                </td>

                                <td class="product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>">
                                    <?php
                                    if (! $product_permalink) {
                                        echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;');
                                    } else {
                                        echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
                                    }

                                    do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);
                                    echo wc_get_formatted_cart_item_data($cart_item);

                                    if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                                        echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
                                    }
                                    ?>
                                </td>

                                <td class="product-price" data-title="<?php esc_attr_e('Price', 'woocommerce'); ?>">
                                    <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // PHPCS: XSS ok. 
                                    ?>
                                </td>

                                <td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
                                    <?php
                                    if ($_product->is_sold_individually()) {
                                        $min_quantity = 1;
                                        $max_quantity = 1;
                                    } else {
                                        $min_quantity = 0;
                                        $max_quantity = $_product->get_max_purchase_quantity();
                                    }

                                    $product_quantity = woocommerce_quantity_input(
                                        array(
                                            'input_name'   => "cart[{$cart_item_key}][qty]",
                                            'input_value'  => $cart_item['quantity'],
                                            'max_value'    => $max_quantity,
                                            'min_value'    => $min_quantity,
                                            'product_name' => $_product->get_name(),
                                        ),
                                        $_product,
                                        false
                                    );

                                    echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item);
                                    ?>
                                </td>

                                <td class="product-subtotal" data-title="<?php esc_attr_e('Subtotal', 'woocommerce'); ?>">
                                    <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // PHPCS: XSS ok. 
                                    ?>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>

                    <?php do_action('woocommerce_cart_contents'); ?>

                    <tr>
                        <td colspan="6" class="actions">

                            <?php if (wc_coupons_enabled()) { ?>
                                <div class="coupon">
                                    <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="Coupon code" />
                                    <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>">Apply&nbsp;coupon</button>
                                    <?php do_action('woocommerce_cart_coupon'); ?>
                                </div>
                            <?php } ?>

                            <button type="submit" class="button hide-update-btn" name="update_cart" value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>"><?php esc_html_e('Update cart', 'woocommerce'); ?></button>

                            <?php do_action('woocommerce_cart_actions'); ?>

                            <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
                        </td>
                    </tr>

                    <?php do_action('woocommerce_after_cart_contents'); ?>
                </tbody>
            </table>
            <?php do_action('woocommerce_after_cart_table'); ?>
        </form>
    </div>
    <div class="studio94-cart-sidebar">
        <div class="cart-collaterals">
            <?php do_action('woocommerce_cart_collaterals'); ?>
        </div>
    </div>
</div>

<div class="page-body-container" style="display: none;">
    <div class="page-content">
        <?php do_action('woocommerce_after_cart'); ?>

        <script>
            if (typeof jQuery !== 'undefined') {
                jQuery.scroll_to_notices = function() {
                    return false;
                };

                jQuery(function($) {
                    $.scroll_to_notices = function() {
                        return false;
                    };

                    if (typeof wc_cart_params !== "undefined") wc_cart_params.is_cart = false;

                    function styliseCartText() {
                        $('.cart_totals h2').text('Cart totals');
                        $('.wc-proceed-to-checkout .checkout-button').text('Proceed to checkout');

                        $('.cart-discount th').each(function() {
                            let text = $(this).text();
                            if (text.includes('Coupon')) {
                                let code = text.replace(/Coupon:?/gi, '').trim();
                                $(this).html('Coupon <span class="coupon-pill">' + code + '</span>');
                                $(this).css({
                                    'display': 'flex',
                                    'align-items': 'center'
                                });
                            }
                        });

                        $('.woocommerce-remove-coupon').text('✕');
                        $('.restore-item').each(function() {
                            $(this).html('&nbsp;Undo?');
                        });

                        $('.woocommerce-cart-form').css('opacity', '1').css('pointer-events', 'all');

                        let $shippingDest = $('.woocommerce-shipping-destination');
                        if ($shippingDest.length) {
                            let destText = $shippingDest.text().trim();
                            destText = destText.replace(/\.$/, '');
                            destText = destText.replace(/([A-Z0-9]{2,4})\s([A-Z0-9]{3})$/i, '$1&nbsp;$2');
                            $shippingDest.html(destText);
                            $shippingDest.css({
                                'font-size': '1rem',
                                'font-weight': '400'
                            });
                        }
                        $('ul#shipping_method li').css({
                            'display': 'flex',
                            'justify-content': 'flex-end'
                        });
                    }

                    function restoreQuantityButtons() {
                        document.querySelectorAll(".woocommerce-cart-form .quantity input.qty").forEach(input => {
                            if (input.parentNode.querySelector(".qty-btn")) return;

                            const minusBtn = document.createElement("button");
                            minusBtn.type = "button";
                            minusBtn.className = "qty-btn minus";
                            minusBtn.innerText = "−";

                            const plusBtn = document.createElement("button");
                            plusBtn.type = "button";
                            plusBtn.className = "qty-btn plus";
                            plusBtn.innerText = "+";

                            input.parentNode.insertBefore(minusBtn, input);
                            input.parentNode.insertBefore(plusBtn, input.nextSibling);

                            minusBtn.addEventListener("click", () => {
                                let val = parseFloat(input.value) || 1,
                                    min = parseFloat(input.min) || 0;
                                if (val > min) {
                                    input.value = val - 1;
                                    input.dispatchEvent(new Event("change", {
                                        bubbles: true
                                    }));
                                }
                            });

                            plusBtn.addEventListener("click", () => {
                                let val = parseFloat(input.value) || 1,
                                    max = parseFloat(input.max) || 9999;
                                if (val < max) {
                                    input.value = val + 1;
                                    input.dispatchEvent(new Event("change", {
                                        bubbles: true
                                    }));
                                }
                            });
                        });
                    }

                    function refreshCartPageAjax() {
                        $('.page-layout-wrapper').css('opacity', '0.5').css('pointer-events', 'none');
                        $.get(window.location.href, function(response) {
                            let $newLayout = $(response).find('.page-layout-wrapper');
                            if ($newLayout.length) {
                                $('.page-layout-wrapper').replaceWith($newLayout);
                                $(document.body).trigger('updated_wc_div');
                                if (typeof window.studio94InitCartButtons === 'function') {
                                    window.studio94InitCartButtons(document.querySelector('.page-layout-wrapper'));
                                }
                                $(document.body).trigger('wc_fragment_refresh');
                            } else {
                                window.location.reload();
                            }
                        });
                    }

                    $(document).off('change.s94cart', 'input.qty');
                    $(document).off('click.s94cart', '.woocommerce-cart-form .product-remove a.remove');
                    $(document).off('click.s94cart', '#s94-empty-cart-btn');
                    $(document.body).off('updated_cart_totals.s94cart updated_wc_div.s94cart');

                    let updateTimer;
                    $(document).on('change.s94cart', 'input.qty', function() {
                        if (updateTimer) clearTimeout(updateTimer);
                        updateTimer = setTimeout(function() {
                            $('[name="update_cart"]').prop('disabled', false).trigger('click');
                        }, 500);
                    });

                    $(document).on('click.s94cart', '.woocommerce-cart-form .product-remove a.remove', function(e) {
                        e.preventDefault();
                        let href = $(this).attr('href');
                        $('.page-layout-wrapper').css('opacity', '0.5').css('pointer-events', 'none');
                        $.get(href, function() {
                            refreshCartPageAjax();
                        });
                    });

                    $(document).on('click.s94cart', '#s94-empty-cart-btn', function(e) {
                        e.preventDefault();
                        let $btn = $(this);
                        $btn.css('opacity', '0.6').text('Emptying...');
                        $('.page-layout-wrapper').css('opacity', '0.5').css('pointer-events', 'none');

                        $.ajax({
                            type: 'POST',
                            url: (typeof studio94Cart !== 'undefined' ? studio94Cart.ajaxUrl : '/wp-admin/admin-ajax.php'),
                            data: {
                                action: 'studio94_empty_cart',
                                nonce: (typeof studio94Cart !== 'undefined' ? studio94Cart.emptyNonce : '')
                            },
                            success: function() {
                                refreshCartPageAjax();
                            }
                        });
                    });

                    $(document.body).on('updated_cart_totals.s94cart updated_wc_div.s94cart', function() {
                        if ($('.woocommerce-cart-form__cart-item').length === 0 && !$('.category-header:contains("empty")').length) {
                            refreshCartPageAjax();
                            return;
                        }
                        $('html, body').stop(true, true);
                        styliseCartText();
                        restoreQuantityButtons();
                    });

                    styliseCartText();
                    restoreQuantityButtons();
                });
            }
        </script>