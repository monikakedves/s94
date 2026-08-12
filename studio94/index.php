<?php get_header(); ?>

<!-- Memorable Clean Hero Section[cite: 5] -->
<header class="hero">
	<div class="hero-content">
		<div class="hero-badge">
			<span></span> Designed & Hand-Pressed in the UK
		</div>
		<h1>Wearable Art.<br><span>Elderly Emo Approved.</span></h1>
		<p>Heavyweight tees, original linocut block prints, canvas totes, and temporary flash sheets for those who never grew out of it.</p>
		<a href="<?php echo wc_get_page_permalink('shop'); ?>" class="btn">Explore Collection</a>
	</div>
	<div class="hero-image-wrap">
		<!-- Ensure this image is uploaded to your theme's /images folder or media library -->
		<img src="https://images.unsplash.com/photo-1615555195415-467f96142c26?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Studio Art Process">
	</div>
</header>

<!-- Loop through WooCommerce recent products here -->
<!-- Add your manifesto section[cite: 5] here -->

<?php get_footer(); ?>