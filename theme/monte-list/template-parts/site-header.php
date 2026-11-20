<?php
/**
 * 詳細ページなどの小さなヘッダー（一覧ページはヒーローを使う）。
 */
?>
<header class="site-header">
	<div class="container site-header__inner">
		<a class="site-header__title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
		<?php get_template_part( 'template-parts/header-links' ); ?>
	</div>
</header>
