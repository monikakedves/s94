<?php
global $product;
if ( ! is_a( $product, 'WC_Product' ) ) return;

get_template_part( 'template-parts/product-card' );

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
                studio94_render_product_card( $rp );
            endforeach;
            ?>
        </ul>
    </div>
</div>
<?php endif; ?>