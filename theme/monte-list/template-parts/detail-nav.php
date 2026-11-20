<?php
/**
 * 詳細ページ下部の Prev / 一覧に戻る / Next。
 * 一覧の並び（左上が最初）で前後の記事へ移動する。
 */

$top      = monte_post_area( get_the_ID() );
$list_url = $top ? get_term_link( $top ) : home_url( '/' );
$adjacent = monte_adjacent_posts( get_the_ID() );
$prev     = $adjacent['prev'];
$next     = $adjacent['next'];
?>
<nav class="detail-nav" aria-label="記事の移動">
	<span>
		<?php if ( $prev ) : ?>
			<a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" rel="prev"><span class="pager__arrow" aria-hidden="true">&larr;</span><span class="screen-reader-text">前の記事：</span><span class="detail-nav__title"><?php echo esc_html( get_the_title( $prev ) ); ?></span></a>
		<?php endif; ?>
	</span>
	<a class="detail-nav__back" href="<?php echo esc_url( $list_url ); ?>">一覧に戻る</a>
	<span>
		<?php if ( $next ) : ?>
			<a href="<?php echo esc_url( get_permalink( $next ) ); ?>" rel="next"><span class="screen-reader-text">次の記事：</span><span class="detail-nav__title"><?php echo esc_html( get_the_title( $next ) ); ?></span><span class="pager__arrow" aria-hidden="true">&rarr;</span></a>
		<?php endif; ?>
	</span>
</nav>
