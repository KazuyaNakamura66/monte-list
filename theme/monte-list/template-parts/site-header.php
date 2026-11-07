<?php
/**
 * 詳細ページなどの小さなヘッダー（一覧ページはヒーローを使う）。
 */
?>
<header class="site-header">
	<div class="container site-header__inner">
		<a class="site-header__title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
		<a class="saved-link saved-link--static" href="<?php echo esc_url( monte_saved_url() ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4h10v16l-5-4-5 4z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
			保存した活動<span class="saved-link__count" data-saved-count hidden></span>
		</a>
	</div>
</header>
