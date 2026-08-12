<?php get_header(); ?>

<header class="hero">
	<div class="hero-content">
		<div class="hero-badge">
			<!-- Now pulls from Appearance > Customize > Landing Page Hero -->
			<span></span> <?php echo esc_html(get_theme_mod('hero_badge_text', 'Designed & Hand-Pressed in the UK')); ?>
		</div>

		<!-- Pulled from Appearance > Customize > Landing Page Hero -->
		<h1><?php echo wp_kses_post(get_theme_mod('hero_title', 'Wearable Art.<br><span>Elderly Emo Approved.</span>')); ?></h1>
		<p><?php echo esc_html(get_theme_mod('hero_subtitle', 'Heavyweight tees, original linocut block prints, canvas totes, and temporary flash sheets for those who never grew out of it.')); ?></p>
		<a href="<?php echo esc_url(get_theme_mod('hero_btn_link', '/shop/')); ?>" class="btn">
			<?php echo esc_html(get_theme_mod('hero_btn_text', 'Explore Collection')); ?>
		</a>

	</div>
	<div class="hero-image-wrap">
		<img src="https://images.unsplash.com/photo-1615555195415-467f96142c26?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Studio Art Process">
	</div>
</header>

<!-- Recent Products Grid -->
<section id="shop">
	<div class="section-header">
		<h2 class="section-title">New Arrivals</h2>
	</div>
	<div class="grid-container">
		<?php
		// Pull 4 recent WooCommerce products
		$args = array('post_type' => 'product', 'posts_per_page' => 4);
		$loop = new WP_Query($args);
		while ($loop->have_posts()) : $loop->the_post();
			wc_get_template_part('content', 'product');
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>

<div class="manifesto">
	<h3>No Mass Production. Just Raw Art.</h3>
	<p>Every lino cut is hand-carved, every tee is heavyweight cotton built to last through the years, and every design carries that authentic studio grit.</p>
</div>

<?php get_footer(); ?>