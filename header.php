<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preload" as="image" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo_v1.svg'); ?>">
    <link rel="preload" as="image" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo_v2.svg'); ?>">
    <link rel="preload" as="image" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo_v3.svg'); ?>">
    <link rel="preload" as="image" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo_v4.svg'); ?>">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <div id="s94-preloader">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo_v1.svg'); ?>" alt="Loading Studio 94">
    </div>
    <script>
        // window.addEventListener('load') guarantees all images, iframes, and fonts are fully downloaded
        window.addEventListener('load', function() {
            const preloader = document.getElementById('s94-preloader');
            if (preloader) {
                preloader.classList.add('s94-loaded');
                // Completely remove the element from the DOM after the fade transition finishes
                setTimeout(() => preloader.remove(), 400);
            }
        });
    </script>
    <div class="site-wrapper">
        <aside class="sidebar">
            <div class="sidebar-top">
                <div class="logo-container">
                    <a href="<?php echo esc_url(home_url('/')); ?>" id="logo-link">
                        <?php
                        $saved_index = isset($_COOKIE['studio94_active_logo']) ? intval($_COOKIE['studio94_active_logo']) : 0;
                        if ($saved_index < 0 || $saved_index > 3) {
                            $saved_index = 0;
                        }
                        $logos = ['logo_v1.svg', 'logo_v2.svg', 'logo_v3.svg', 'logo_v4.svg'];
                        $active_logo = $logos[$saved_index];
                        ?>
                        <img id="dynamic-logo" class="logo-desktop" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/' . $active_logo); ?>" alt="<?php bloginfo('name'); ?>">
                        <script>
                            (function() {
                                var match = document.cookie.match(/(^| )studio94_active_logo=([^;]+)/);
                                var idx = match ? parseInt(match[2]) : 0;
                                if (isNaN(idx) || idx < 0 || idx > 3) idx = 0;
                                var logos = ["logo_v1.svg", "logo_v2.svg", "logo_v3.svg", "logo_v4.svg"];
                                var basePath = "https://studio94.uk/uploads/themes/studio94/assets/images/";
                                document.getElementById("dynamic-logo").src = basePath + logos[idx];
                            })();
                        </script>
                        <span class="logo-mobile-wrap">
                            <img class="logo-mobile" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/wordmark_v1.svg'); ?>" alt="<?php bloginfo('name'); ?>"> </span>
                    </a>
                </div>
                <button id="mobile-menu-btn" class="mobile-menu-toggle">☰</button>
            </div>
            <nav id="mobile-nav-container" class="nav-container">
                <?php wp_nav_menu(['theme_location' => 'sidebar_menu', 'container' => false, 'menu_class' => 'main-nav', 'fallback_cb' => false]); ?>
                <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="cart-widget">Cart (<span class="cart-count"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>)</a>
            </nav>
        </aside>
        <main class="main-content">
            <script>
                document.getElementById('mobile-menu-btn').addEventListener('click', function() {
                    document.getElementById('mobile-nav-container').classList.toggle('active');
                });

                let lastScrollTop = 0;
                const mobileSidebar = document.querySelector('.sidebar');
                window.addEventListener('scroll', function() {
                    if (window.innerWidth <= 768) {
                        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                        if (scrollTop > lastScrollTop && scrollTop > 50) {
                            mobileSidebar.classList.add('nav-hidden');
                            document.getElementById('mobile-nav-container').classList.remove('active');
                        } else {
                            mobileSidebar.classList.remove('nav-hidden');
                        }
                        lastScrollTop = scrollTop;
                    }
                });

                document.addEventListener('DOMContentLoaded', function() {
                    const anchorLinks = document.querySelectorAll('a[href^="#"]');
                    anchorLinks.forEach(link => {
                        link.addEventListener('click', function(e) {
                            const targetId = this.getAttribute('href');
                            if (targetId === '#') return;
                            const targetElement = document.querySelector(targetId);
                            if (targetElement) {
                                e.preventDefault();
                                targetElement.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                                if (history.pushState) {
                                    history.pushState(null, null, targetId);
                                } else {
                                    location.hash = targetId;
                                }
                            }
                        });
                    });
                });

                document.addEventListener('DOMContentLoaded', function() {

                    if (typeof jQuery !== 'undefined') {
                        function studio94PulseCartWidget() {
                            var cartWidget = document.querySelector('.cart-widget');
                            if (!cartWidget) return;
                            cartWidget.style.transform = 'scale(1.05)';
                            cartWidget.style.backgroundColor = 'var(--brand-rose-light)';
                            cartWidget.style.color = 'var(--brand-rose)';
                            setTimeout(() => {
                                cartWidget.style.transform = '';
                                cartWidget.style.backgroundColor = '';
                                cartWidget.style.color = '';
                            }, 400);
                        }

                        function studio94UpdateCartCount(fragments) {
                            var cartCountEl = document.querySelector('.cart-widget .cart-count');
                            if (cartCountEl && fragments && typeof fragments.studio94_cart_count !== 'undefined') {
                                cartCountEl.innerText = fragments.studio94_cart_count;
                            }
                            studio94PulseCartWidget();
                        }

                        jQuery(document.body).on('added_to_cart', function(e, fragments) {
                            studio94UpdateCartCount(fragments);
                        });

                        jQuery(document.body).on('removed_from_cart', function(e, fragments) {
                            studio94UpdateCartCount(fragments);
                        });
                    }
                });
            </script>