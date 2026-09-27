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

					<?php
					$custom_range = get_post_meta($product->get_id(), '_s94_custom_price_range', true);

					// NUCLEAR PRICE FETCHING
					$display_price_html = '';
					$data_custom_range_attr = $custom_range;

					if (!empty($custom_range)) {
						// 1. Pet Tags (Custom Range) - Untouched logic
						$display_price_html = '<span class="woocommerce-Price-amount amount"><bdi>' . esc_html($custom_range) . '</bdi></span>';
					} else {
						// 2. Intertwined Hands & Standard Products - Raw database extraction bypassing WAPF filters
						if ($product->is_type('variable')) {
							$prices = $product->get_variation_prices(true);
							$min_price = current($prices['price']);
							$max_price = end($prices['price']);

							if ($min_price === $max_price) {
								$display_price_html = wc_price($min_price);
							} else {
								$display_price_html = wc_format_price_range($min_price, $max_price);
							}
						} else {
							$display_price_html = wc_price(wc_get_price_to_display($product));
						}

						// Create pure text string for main.js to consume safely without double-wrapping HTML
						$data_custom_range_attr = html_entity_decode(wp_strip_all_tags($display_price_html));
					}
					?>

					<div class="price-wrap s94-force-price" data-custom-range="<?php echo esc_attr($data_custom_range_attr); ?>" style="display:block !important; opacity:1 !important; visibility:visible !important;">
						<p class="price" style="display:block !important; opacity:1 !important; visibility:visible !important;"><?php echo $display_price_html; ?></p>
					</div>

					<!-- Nuclear JS Fallback: actively guards the price HTML and restores it if WAPF erases it -->
					<script>
						document.addEventListener("DOMContentLoaded", function() {
							var priceWrap = document.querySelector('.s94-force-price .price');
							var backupHTML = <?php echo wp_json_encode($display_price_html); ?>;

							if (priceWrap) {
								if (priceWrap.innerHTML.trim() === '') {
									priceWrap.innerHTML = backupHTML;
								}
								var observer = new MutationObserver(function() {
									if (priceWrap.innerHTML.trim() === '') {
										priceWrap.innerHTML = backupHTML;
									}
								});
								observer.observe(priceWrap, {
									childList: true,
									characterData: true,
									subtree: true
								});
							}
						});
					</script>

					<div class="short-desc"><?php woocommerce_template_single_excerpt(); ?></div>

					<div class="cart-actions-wrapper">
						<?php
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

						add_filter('woocommerce_product_single_add_to_cart_text', function () use ($is_in_cart) {
							return $is_in_cart ? __('Added to cart', 'woocommerce') : __('Add to cart', 'woocommerce');
						}, 99);

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
	get_template_part('template-parts/modal-lightbox');
endwhile;
do_action('woocommerce_after_main_content');
get_footer();
?>