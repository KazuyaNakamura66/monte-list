<?php
/**
 * ヘッダー右上のリンク（活動一覧・お気に入り）。
 */

$is_saved = (bool) get_query_var( 'monte_saved' );
?>
<nav class="header-links" aria-label="サイト内リンク">
	<a class="saved-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
		活動一覧
	</a>
	<a class="saved-link" href="<?php echo esc_url( monte_saved_url() ); ?>"<?php echo $is_saved ? ' aria-current="page"' : ''; ?>>
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4h10v16l-5-4-5 4z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
		お気に入り<span class="saved-link__count" data-saved-count hidden></span>
	</a>
</nav>
