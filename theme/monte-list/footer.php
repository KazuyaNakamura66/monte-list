<footer class="site-footer">
	<div class="container">
		<ul class="site-footer__nav">
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">活動一覧</a></li>
			<li><a href="<?php echo esc_url( monte_saved_url() ); ?>">保存した活動</a></li>
		</ul>
		<p class="site-footer__copy">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
	</div>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
