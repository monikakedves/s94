<?php
if (! defined('ABSPATH')) {
	exit;
}

get_header(); ?>

<?php
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

while (have_posts()) : the_post();
	global $product;
?>

	<div id="product-<?php the_ID(); ?>" <?php wc_product_class('single-product-page-wrapper', $product); ?>>
		<div class="custom-breadcrumbs">
			<?php woocommerce_breadcrumb([
				'delimiter'   => ' <span class="divider">/</span> ',
				'wrap_before' => '<nav class="woocommerce-breadcrumb">',
				'wrap_after'  => '</nav>'
			]); ?>
		</div>

		<!-- Top Section: Gallery & Summary -->
		<div class="page-body-container" style="margin-bottom:2rem;">
			<div class="single-product-top-split">
				<div class="product-gallery">
					<?php
					$main_image_id = $product->get_image_id();
					$attachment_ids = $product->get_gallery_image_ids();
					if ($main_image_id) {
						$main_img_url = wp_get_attachment_image_url($main_image_id, 'full');
                        // UPDATED: Replaced woocommerce-product-gallery classes with custom-product-gallery
						echo '<div class="custom-product-gallery"><div class="custom-product-gallery__wrapper"><div class="custom-product-gallery__image"><img src="' . esc_url($main_img_url) . '" data-full="' . esc_url($main_img_url) . '" class="wp-post-image" alt="" /></div></div>';
						if ($attachment_ids) {
							echo '<ul class="flex-control-nav flex-control-thumbs"><li><img src="' . esc_url(wp_get_attachment_image_url($main_image_id, 'woocommerce_thumbnail')) . '" data-full="' . esc_url($main_img_url) . '" class="active-thumb" /></li>';
							foreach ($attachment_ids as $attachment_id) {
								echo '<li><img src="' . esc_url(wp_get_attachment_image_url($attachment_id, 'woocommerce_thumbnail')) . '" data-full="' . esc_url(wp_get_attachment_image_url($attachment_id, 'full')) . '" /></li>';
							}
							echo '</ul>';
						}
						echo '</div>';
					}
					?>
				</div>

				<div class="product-summary">
					<h1 class="product-title"><?php the_title(); ?></h1>
					<div class="price-wrap"><?php woocommerce_template_single_price(); ?></div>
					<div class="short-desc"><?php woocommerce_template_single_excerpt(); ?></div>
					<div class="cart-actions-wrapper"><?php woocommerce_template_single_add_to_cart(); ?></div>
					<div class="product-meta"><?php woocommerce_template_single_meta(); ?></div>
				</div>
			</div>
		</div>

		<!-- Middle Section: Tabs (Details & Reviews) -->
		<div class="page-body-container" style="margin-bottom:2rem;">
			<div class="product-specs-wrap">
				<div class="product-tabs">
					<h2 class="tab-btn active" data-target="tab-details">Product Details</h2>
					<h2 class="tab-btn" data-target="tab-reviews">Reviews (<?php echo get_comments_number(); ?>)</h2>
				</div>
			</div>

			<div class="tab-content active" id="tab-details">
				<div class="product-details-grid">
					<div class="long-description-wrap">
						<div class="long-desc-content"><?php the_content(); ?></div>
					</div>

					<div class="product-specs-wrap" style="border-left: 1px solid #eaeaea; padding-left: 4rem;">
						<?php if ($product->has_weight() || $product->has_dimensions()) : ?>
							<h2>Specifications</h2>
							<ul class="specs-list">
								<?php if ($product->has_weight()) : ?>
									<li><strong>Weight:</strong> <?php echo esc_html(wc_format_weight($product->get_weight())); ?></li>
								<?php endif; ?>
								<?php
								if ($product->has_dimensions()) :
									$d = $product->get_dimensions(false);
									$u = get_option('woocommerce_dimension_unit');
									$o = [];
									if (! empty($d['length'])) $o[] = 'L' . $d['length'];
									if (! empty($d['width'])) $o[] = 'W' . $d['width'];
									if (! empty($d['height'])) $o[] = 'H' . $d['height'];
								?>
									<li><strong>Dimensions:</strong> <?php echo esc_html(implode(' x ', $o) . ' ' . $u); ?></li>
								<?php endif; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="tab-content" id="tab-reviews">
				<div class="reviews-section" id="reviews">
					<!-- Hides default WC title to prevent duplicates -->
					<style>
						.woocommerce-Reviews-title {
							display: none !important;
						}
					</style>
					<?php if (comments_open() || get_comments_number()) : comments_template();
					endif; ?>
				</div>
			</div>
		</div>

		<?php get_template_part('template-parts/related-products'); ?>
	</div> <!-- End of main product wrap -->

	<?php get_template_part('template-parts/modal-quick-view'); ?>
	<?php get_template_part('template-parts/modal-lightbox'); ?>

<?php endwhile;
do_action('woocommerce_after_main_content'); ?>

<?php get_footer(); ?>