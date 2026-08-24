<?php

defined( 'ABSPATH' ) || exit;
?>
<div class="category-header align-center" style="margin-bottom: 2rem; text-align: center;">
    <h1>Your cart is currently empty.</h1>
    <p style="margin-bottom: 2rem;">Looks like you haven't added anything to your cart yet.</p>
    <a class="btn" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
        Return to Shop
    </a>
</div>

<div class="section-header" style="justify-content: center; text-align: center; margin-bottom: 1rem;">
    <h2 class="section-title">New in Store</h2>
</div>

<?php
$args = array(
    'post_type'      => 'product',
    'posts_per_page' => 4,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$new_products = new WP_Query( $args );

if ( $new_products->have_posts() ) : ?>
    
    <div class="archive">
        <ul class="products custom-related columns-4" style="list-style: none; padding-left: 0; margin-left: 0;">
            <?php
            while ( $new_products->have_posts() ) : $new_products->the_post();
                global $product;
                ?>
                <li <?php wc_product_class( 'product type-product', $product ); ?> style="list-style-type: none !important;">
                    
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
                                esc_html__( 'Add to Cart', 'woocommerce' )
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
            endwhile;
            ?>
        </ul>
    </div>
    
    <?php wp_reset_postdata(); ?>
<?php endif; ?>

<?php 
get_template_part('template-parts/modal-quick-view'); 
?>