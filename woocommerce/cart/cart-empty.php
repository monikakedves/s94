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
                wc_get_template_part( 'content', 'product' );
            endwhile;
            ?>
        </ul>
    </div>
    <?php wp_reset_postdata(); ?>
<?php endif; ?>

<?php get_template_part('template-parts/modal-quick-view'); ?>