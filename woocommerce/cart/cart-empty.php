<?php
defined('ABSPATH') || exit;
?>
<div class="category-header align-center" style="margin-bottom: 2rem; text-align: center;">
    <h1>Your cart is currently empty.</h1>
    <p style="margin-bottom: 2rem;">Looks like you haven't added anything to your cart yet.</p>
    <a class="btn" href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
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
$new_products = new WP_Query($args);

if ($new_products->have_posts()) : ?>
    <div class="archive">
        <ul class="products custom-related columns-4" style="list-style: none; padding-left: 0; margin-left: 0;">
            <?php
            while ($new_products->have_posts()) : $new_products->the_post();
                wc_get_template_part('content', 'product');
            endwhile;
            ?>
        </ul>
    </div>
    <?php wp_reset_postdata(); ?>
<?php endif; ?>

<script>
    if (typeof jQuery !== 'undefined') {
        jQuery(function($) {
            if (typeof wc_cart_params !== "undefined") wc_cart_params.is_cart = false;

            $(document.body).off('added_to_cart.s94empty');
            $(document.body).on('added_to_cart.s94empty', function(e) {
                if ($('body').hasClass('woocommerce-cart')) {
                    $('.page-layout-wrapper').css('opacity', '0.5').css('pointer-events', 'none');

                    $.get(window.location.href, function(response) {
                        let $newLayout = $(response).find('.page-layout-wrapper');
                        if ($newLayout.length) {
                            $('.page-layout-wrapper').replaceWith($newLayout);
                            $(document.body).trigger('updated_wc_div');

                            if (typeof window.studio94InitCartButtons === 'function') {
                                window.studio94InitCartButtons(document.querySelector('.page-layout-wrapper'));
                            }
                        } else {
                            window.location.reload();
                        }
                    });
                }
            });
        });
    }
</script>