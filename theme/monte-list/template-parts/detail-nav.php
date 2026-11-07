<?php
/**
 * 詳細ページ下部の Prev / 一覧に戻る / Next。
 * 同じ種類（教具 or 活動）の前後の記事へ移動する。
 */

$top      = monte_post_area( get_the_ID() );
$list_url = $top ? get_term_link( $top ) : home_url( '/' );
$prev     = get_previous_post();
$next     = get_next_post();
?>
<nav class="detail-nav" aria-label="記事の移動">
	<span>
		<?php if ( $prev ) : ?>
			<a href="<?php echo esc_url( get_permalink( $prev ) ); ?>"><span class="pager__arrow" aria-hidden="true">&larr;</span>Prev</a>
		<?php endif; ?>
	</span>
	<a class="detail-nav__back" href="<?php echo esc_url( $list_url ); ?>">一覧に戻る</a>
	<span>
		<?php if ( $next ) : ?>
			<a href="<?php echo esc_url( get_permalink( $next ) ); ?>">Next<span class="pager__arrow" aria-hidden="true">&rarr;</span></a>
		<?php endif; ?>
	</span>
</nav>
