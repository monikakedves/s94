<footer class="site-footer">

	<?php if (is_active_sidebar('footer-1')) : ?>
		<div class="footer-col-1">
			<?php dynamic_sidebar('footer-1'); ?>
		</div>
	<?php endif; ?>

	<?php if (is_active_sidebar('footer-2')) : ?>
		<div class="footer-col-2">
			<?php dynamic_sidebar('footer-2'); ?>
		</div>
	<?php endif; ?>

</footer>

</main> <!-- End Main Content -->
</div> <!-- End Site Wrapper -->

<?php wp_footer(); ?>
</body>

</html>