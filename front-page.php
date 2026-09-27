<?php get_header(); ?>

<header class="hero">
    <div class="hero-content">
        <div class="hero-badge">
            <span></span>
            <?php echo esc_html(get_theme_mod('hero_badge_text', 'Designed & Hand-Pressed in the UK')); ?>
        </div>
        <h1><?php echo wp_kses_post(get_theme_mod('hero_title', 'Smart Pet Tags.<br><span>Safe & Secure.</span>')); ?></h1>
        <p><?php echo esc_html(get_theme_mod('hero_subtitle', 'Premium stainless steel pet ID tags with traditional engraving or modern Smart QR code profiles.')); ?></p>
        <a href="<?php echo esc_url(get_theme_mod('hero_btn_link', '/product/pet-tag/')); ?>" class="btn">
            <?php echo esc_html(get_theme_mod('hero_btn_text', 'Shop Pet Tags')); ?>
        </a>
    </div>
    <div class="hero-image-wrap">
        <img src="https://images.unsplash.com/photo-1583337130417-3346a1be7dee?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Studio Pet Tags">
    </div>
</header>

<section class="temp-ink-section">
    <div class="temp-ink-overlay"></div>
    <div class="temp-ink-content-wrapper">
        <div class="temp-ink-col-empty"></div>
        <div class="temp-ink-col-text">
            <h2>Commitment Issues?</h2>
            <p>Try our temporary flash sheets. Hand-drawn traditional tattoo designs that carry that authentic studio grit, lasting just long enough for the weekend.</p>
            <a href="https://studio94.uk/product-category/temp-ink/" class="btn btn-white">Shop Temporary Ink</a>
        </div>
    </div>
</section>

<div class="manifesto">
    <h3>No Mass Production. Just Raw Art.</h3>
    <p>Every keepsake is hand-crafted, every pet tag is built to last, and every design carries that authentic studio grit.</p>
</div>

<section id="shop">
    <style>
        #shop .loop-product-rating {
            display: none !important;
        }
    </style>
    <div class="section-header" style="margin-bottom:1rem;">
        <h2 class="section-title">New Arrivals</h2>
    </div>
    <ul class="products custom-related columns-4" style="margin-bottom:2.5rem;">
        <?php
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => 4,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => 'outofstock',
                    'operator' => 'NOT IN',
                ),
            ),
        );
        $loop = new WP_Query($args);
        while ($loop->have_posts()) : $loop->the_post();
            wc_get_template_part('content', 'product');
        endwhile;
        wp_reset_postdata();
        ?>
    </ul>
</section>

<section class="split-categories-section">
    <div class="split-category keepsakes-category">
        <div class="split-overlay"></div>
        <div class="split-content">
            <h2>Personalised Keepsakes</h2>
            <p>Celebrate your unique bond with bespoke wire art sculptures and custom designs.</p>
            <a href="https://studio94.uk/product-category/personalised-keepsakes/" class="btn btn-white">Shop Keepsakes</a>
        </div>
    </div>
    <div class="split-category wax-melts-category">
        <div class="split-overlay"></div>
        <div class="split-content">
            <h2>Wax Melts</h2>
            <p>Hand-poured, studio-exclusive scents to set the perfect mood.</p>
            <a href="https://studio94.uk/product-category/home-space/wax-melts/" class="btn btn-white">Shop Wax Melts</a>
        </div>
    </div>
</section>

<?php get_footer(); ?>