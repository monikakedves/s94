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
        $GLOBALS['post'] = get_post( $rid );
        setup_postdata( $GLOBALS['post'] );
        wc_get_template_part( 'content', 'product' );
    endforeach;
    wp_reset_postdata();
    ?>
</ul>
    </div>
</div>
<?php endif; ?>