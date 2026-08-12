<?php
global $product;
if ( ! is_a( $product, 'WC_Product' ) ) return;

$related = wc_get_related_products( $product->get_id(), 4 );
if ( $related ) :
?>
<div class="page-body-container" style="margin-bottom:2rem;">
    <div class="related-products-wrap">
        <h2>Related products</h2>
        <ul class="custom-related">
            <?php 
            foreach ( $related as $rid ) :
                $rp = wc_get_product( $rid );
                if ( ! $rp ) continue;

                $p_url = $rp->get_permalink();
                $p_title = $rp->get_name();
                $p_price = $rp->get_price_html();
                $i_url = get_the_post_thumbnail_url( $rid, 'woocommerce_thumbnail' );
                $fi_url = get_the_post_thumbnail_url( $rid, 'large' );
                $exc = apply_filters( 'woocommerce_short_description', $rp->get_short_description() );
                
                // Pre-render the cart form for the modal
                ob_start();
                $original_post = $GLOBALS['post'];
                $GLOBALS['post'] = get_post( $rid );
                setup_postdata( $GLOBALS['post'] );
                woocommerce_template_single_add_to_cart();
                $cart_form = ob_get_clean();
                $GLOBALS['post'] = $original_post;
                wp_reset_postdata();
            ?>
                <li class="product type-product">
                    <div class="product-img-wrap">
                        <?php if ( $rp->is_on_sale() ) echo '<span class="onsale">Sale!</span>'; ?>
                        <img src="<?php echo esc_url( $i_url ); ?>" alt="">
                        <div class="quick-view-overlay">
                            <button class="quick-view-btn" data-id="<?php echo esc_attr( $rid ); ?>">Quick View</button>
                        </div>
                    </div>
                    <div class="product-info-wrap">
                        <div class="product-title-row">
                            <a href="<?php echo esc_url( $p_url ); ?>">
                                <h3 class="woocommerce-loop-product__title"><?php echo esc_html( $p_title ); ?></h3>
                            </a>
                            
                            <?php 
                            // Native WooCommerce AJAX Add to Cart Button
                            echo sprintf(
                                '<a href="%s" data-quantity="1" class="%s" %s>%s</a>',
                                esc_url( $rp->add_to_cart_url() ),
                                esc_attr( implode( ' ', array(
                                    'button add_to_cart_button',
                                    'product_type_' . $rp->get_type(),
                                    $rp->is_purchasable() && $rp->is_in_stock() ? 'add_to_cart_button' : '',
                                    $rp->supports( 'ajax_add_to_cart' ) && $rp->is_purchasable() && $rp->is_in_stock() ? 'ajax_add_to_cart' : '',
                                ) ) ),
                                wc_implode_html_attributes( array(
                                    'data-product_id'  => $rp->get_id(),
                                    'data-product_sku' => $rp->get_sku(),
                                    'aria-label'       => $rp->add_to_cart_description(),
                                    'rel'              => 'nofollow',
                                ) ),
                                esc_html( $rp->add_to_cart_text() )
                            );
                            ?>
                        </div>
                        <span class="price"><?php echo $p_price; ?></span>
                    </div>

                    <!-- Hidden Data for JS Modal Injection -->
                    <div class="qv-data" style="display:none;" data-title="<?php echo esc_attr( $p_title ); ?>" data-img="<?php echo esc_url( $fi_url ); ?>" data-url="<?php echo esc_url( $p_url ); ?>"><?php echo wp_kses_post( $exc ); ?></div>
                    <div class="qv-price-data" style="display:none;"><?php echo $p_price; ?></div>
                    <div class="qv-cart-data" style="display:none;"><?php echo htmlspecialchars( $cart_form ); ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>