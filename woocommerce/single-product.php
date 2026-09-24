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

		<div class="page-body-container" style="margin-bottom:2rem;">
			<div class="single-product-top-split">
				<div class="product-gallery">
					<?php
					$main_image_id = $product->get_image_id();
					$attachment_ids = $product->get_gallery_image_ids();

					if ($main_image_id) {
						$main_img_url = wp_get_attachment_image_url($main_image_id, 'full');
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

					<?php if ($product->get_rating_count() > 0) : ?>
						<div class="product-rating-wrap" style="margin-bottom: 1rem;">
							<?php echo wc_get_rating_html($product->get_average_rating()); ?>
						</div>
					<?php endif; ?>

					<?php $custom_range = get_post_meta($product->get_id(), '_s94_custom_price_range', true); ?>
					<div class="price-wrap" data-custom-range="<?php echo esc_attr($custom_range); ?>">
						<?php
						if (!empty($custom_range)) {
							echo '<p class="price"><span class="woocommerce-Price-amount amount"><bdi>' . esc_html($custom_range) . '</bdi></span></p>';
						} else {
							woocommerce_template_single_price();
						}
						?>
					</div>
					<div class="short-desc"><?php woocommerce_template_single_excerpt(); ?></div>

					<div class="cart-actions-wrapper">
						<?php
						// Dynamically check cart state on page load
						$is_in_cart = false;
						if (function_exists('WC') && WC()->cart) {
							foreach (WC()->cart->get_cart() as $cart_item) {
								$match_id = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
								if ((int) $cart_item['product_id'] === $product->get_id() || (int) $match_id === $product->get_id()) {
									$is_in_cart = true;
									break;
								}
							}
						}

						// Force text change
						add_filter('woocommerce_product_single_add_to_cart_text', function () use ($is_in_cart) {
							return $is_in_cart ? __('Added to cart', 'woocommerce') : __('Add to cart', 'woocommerce');
						}, 99);

						// Inject the necessary class directly onto the button immediately after it renders
						if ($is_in_cart) {
							add_action('woocommerce_after_add_to_cart_button', function () {
								echo '<script>document.addEventListener("DOMContentLoaded", function() { var b = document.querySelector(".single_add_to_cart_button"); if(b) b.classList.add("added"); });</script>';
							});
						}

						woocommerce_template_single_add_to_cart();
						?>
					</div>

					<div class="product-meta"><?php woocommerce_template_single_meta(); ?></div>
				</div>
			</div>
		</div>

		<div class="page-body-container" style="margin-bottom:2rem;">
			<div class="product-specs-wrap">
				<div class="product-tabs">
					<h2 class="tab-btn active" data-target="tab-details">Product details</h2>
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
								<?php if ($product->has_dimensions()) :
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
	</div>

<?php
	get_template_part('template-parts/modal-quick-view');
	get_template_part('template-parts/modal-lightbox');
endwhile;
do_action('woocommerce_after_main_content');
get_footer();
?>