<?php get_header(); ?>

<?php while (have_posts()) : the_post();
	$eyebrow = get_post_meta(get_the_ID(), '_studio94_eyebrow', true) ?: get_post_meta(get_the_ID(), '_studio94_subtitle', true);
	$icon = get_post_meta(get_the_ID(), '_studio94_eyebrow_icon', true);
	$subtitle = get_post_meta(get_the_ID(), '_studio94_true_subtitle', true);
	$align = get_post_meta(get_the_ID(), '_studio94_header_align', true) ?: 'left';

	$sidebar_enabled = get_post_meta(get_the_ID(), '_studio94_inner_sidebar_enabled', true);
	$sidebar_content = get_post_meta(get_the_ID(), '_studio94_inner_sidebar_content', true);
	$has_sidebar = ($sidebar_enabled && !empty($sidebar_content)) ? 'has-inner-sidebar' : '';

	$no_sidebar_class = $has_sidebar ? '' : 'no-sidebar';
	$align_class = 'align-' . esc_attr($align);
?>

	<header class="page-header-container <?php echo $no_sidebar_class . ' ' . $align_class; ?>">
		<?php if ($eyebrow) : ?>
			<div class="page-eyebrow-wrapper">
				<?php if ($icon) : ?>
					<?php if (strpos(trim($icon), '<svg') === 0) :
						$icon = str_ireplace(['#000000', '#000', 'black'], 'currentColor', $icon);
					?>
						<span class="page-eyebrow-icon raw-svg"><?php echo $icon; ?></span>
					<?php else : ?>
						<span class="page-eyebrow-icon img-mask" style="-webkit-mask-image: url('<?php echo esc_url($icon); ?>'); mask-image: url('<?php echo esc_url($icon); ?>');"></span>
					<?php endif; ?>
				<?php endif; ?>
				<span class="page-eyebrow"><?php echo esc_html($eyebrow); ?></span>
			</div>
		<?php endif; ?>

		<h1 class="page-title"><?php the_title(); ?></h1>

		<?php if ($subtitle) : ?>
			<p class="page-sub-title"><?php echo esc_html($subtitle); ?></p>
		<?php endif; ?>
	</header>

	<!-- 2. Body & Sidebar Container -->
	<div class="page-layout-wrapper <?php echo esc_attr($has_sidebar); ?>">

		<!-- Main Content (Body) -->
		<div class="page-body-container">
			<div class="page-content">
				<?php the_content(); ?>
			</div>
		</div>

		<!-- Inner Sidebar (Only if turned on) -->
		<?php if ($has_sidebar) : ?>
			<aside class="inner-sidebar">
				<div class="inner-sidebar-sticky">
					<?php echo apply_filters('the_content', $sidebar_content); ?>
				</div>
			</aside>
		<?php endif; ?>

	</div>

<?php endwhile; ?>

<?php get_footer(); ?>