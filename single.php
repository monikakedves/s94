<?php get_header(); ?>

<?php while (have_posts()) : the_post();
	$eyebrow = get_post_meta(get_the_ID(), '_studio94_eyebrow', true) ?: get_post_meta(get_the_ID(), '_studio94_subtitle', true);
	$icon = get_post_meta(get_the_ID(), '_studio94_eyebrow_icon', true);
	$subtitle = get_post_meta(get_the_ID(), '_studio94_true_subtitle', true);
	$align = get_post_meta(get_the_ID(), '_studio94_header_align', true) ?: 'left';
	$align_class = 'align-' . esc_attr($align);
?>

	<header class="page-header-container no-sidebar <?php echo $align_class; ?>">
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

	<div class="page-layout-wrapper">
		<div class="page-body-container no-sidebar">
			<div class="page-content">
				<?php the_content(); ?>
			</div>
		</div>
	</div>

<?php endwhile; ?>

<?php get_footer(); ?>