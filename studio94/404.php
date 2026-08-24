<?php
get_header(); ?>
<div class="page-layout-wrapper no-sidebar">
	<div class="page-body-container error-404-wrapper">
		<div class="error-404-content">
			
			<h1 class="error-title">404</h1>
			<h2 class="error-subtitle">This page got carved out.</h2>
			
			<p class="error-text">Much like a slip of the linocut blade, the page you're looking for is gone. It might be a dead link, a typo, or maybe it just grew out of its emo phase.</p>
			
			<div class="error-actions">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn">Return to Base</a>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-outline">Explore the Shop</a>
			</div>

		</div>
	</div>
</div>
<?php get_footer(); ?>