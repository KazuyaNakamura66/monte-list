<?php
/**
 * カタログ一覧（トップページのデザイン）。
 */

list( $current_top, $current_sub ) = monte_current_area();

$top_areas = monte_top_areas();
$children  = $current_top ? monte_child_areas( $current_top->term_id ) : array();

if ( is_search() ) {
	$heading = sprintf( '「%s」の検索結果', get_search_query() );
} elseif ( $current_top ) {
	$heading = get_field( 'area_heading', $current_top ) ?: $current_top->name;
} else {
	$heading = 'すべての活動';
}

$timeline_url = monte_option( 'timeline_url' ) ?: '#';
$record_url   = monte_option( 'record_sheet_url' ) ?: '#';
?>
<header class="catalog-hero">
	<div class="container catalog-hero__inner">
		<a class="saved-link" href="<?php echo esc_url( monte_saved_url() ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4h10v16l-5-4-5 4z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
			保存した活動<span class="saved-link__count" data-saved-count hidden></span>
		</a>
		<h1 class="catalog-hero__title">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
		</h1>
		<p class="catalog-hero__sub">ACTIVITY LIBRARY</p>

		<form class="search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="catalog-search">検索</label>
			<input id="catalog-search" class="search__input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="活動名・キーワードで検索">
		</form>

		<nav class="area-tabs" aria-label="領域">
			<a class="area-tab<?php echo $current_top || is_search() ? '' : ' is-active'; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="--area-color:#3b3530">すべて</a>
			<?php foreach ( $top_areas as $area ) : ?>
				<a class="area-tab<?php echo $current_top && $current_top->term_id === $area->term_id ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( get_term_link( $area ) ); ?>"
					style="--area-color:<?php echo esc_attr( monte_area_color( $area ) ); ?>"><?php echo esc_html( $area->name ); ?></a>
			<?php endforeach; ?>
		</nav>
	</div>
</header>

<main class="catalog">
	<div class="container">
		<h2 class="catalog__heading">
			<svg class="catalog__icon" viewBox="0 0 32 24" aria-hidden="true"><path d="M9 15c3-6 9-10 15-10 3 0 5 2 4 4-1 3-6 4-9 4M9 15c2 3 6 5 10 4 3-1 5-3 5-5M9 15H4m2-4H2m4 8H3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			<?php echo esc_html( $heading ); ?>
		</h2>

		<div class="notice">
			<p>活動カテゴリーは、「<a href="<?php echo esc_url( $timeline_url ); ?>" target="_blank" rel="noopener">発達タイムライン</a>」と「<a href="<?php echo esc_url( $record_url ); ?>" target="_blank" rel="noopener">発達記録シート</a>」に対応しています。<br>
			お子さんの今の発達段階や、どの活動を取り入れるか迷ったときは、あわせて確認してみてください。</p>
		</div>

		<div class="subbar">
			<?php if ( $children ) : ?>
				<nav class="sub-tabs" aria-label="小分類">
					<?php foreach ( $children as $child ) : ?>
						<a class="sub-tab<?php echo $current_sub && $current_sub->term_id === $child->term_id ? ' is-active' : ''; ?>"
							href="<?php echo esc_url( get_term_link( $child ) ); ?>"><?php echo esc_html( $child->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php else : ?>
				<div></div>
			<?php endif; ?>
			<button class="filter-button" type="button" aria-haspopup="dialog" data-filter-open>
				<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M2 4h3m4 0h5M2 8h7m4 0h1M2 12h2m4 0h6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/><circle cx="7" cy="4" r="1.6" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="11" cy="8" r="1.6" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="6" cy="12" r="1.6" fill="none" stroke="currentColor" stroke-width="1.3"/></svg>
				絞り込み
			</button>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="card-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card' );
				endwhile;
				?>
			</div>
		<?php else : ?>
			<p class="catalog__empty">該当する活動はまだありません。</p>
		<?php endif; ?>

		<nav class="pager" aria-label="ページ送り">
			<span class="pager__prev"><?php previous_posts_link( '<span class="pager__arrow" aria-hidden="true">&larr;</span>Prev' ); ?></span>
			<span class="pager__next"><?php next_posts_link( 'Next<span class="pager__arrow" aria-hidden="true">&rarr;</span>' ); ?></span>
		</nav>
	</div>
</main>

<dialog class="filter-dialog" data-filter-dialog>
	<p>絞り込み条件は準備中です。</p>
	<form method="dialog"><button class="filter-dialog__close">閉じる</button></form>
</dialog>
