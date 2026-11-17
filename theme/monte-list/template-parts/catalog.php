<?php
/**
 * カタログ一覧（トップページのデザイン）。
 */

$current_top = monte_current_area();
$filters     = monte_filters();

$top_areas = monte_top_areas();
$stages    = $current_top ? monte_area_stages( $current_top->term_id ) : array();
$links     = $current_top ? monte_area_links( $current_top ) : array(); // 関連資料
$period    = monte_current_period(); // 選択中の発達段階タブ（未指定なら最初の段階）

// タブ・絞り込みの基準URL（ページ番号なし）
$base_url = $current_top ? get_term_link( $current_top ) : home_url( '/' );
$keep     = array_filter( array(
	's'      => get_search_query(),
	'hand'   => $filters['hand'],
	'source' => $filters['source'],
	'video'  => $filters['video'],
) );

if ( is_search() ) {
	$heading = sprintf( '「%s」の検索結果', get_search_query() );
} elseif ( $current_top ) {
	$heading = monte_area_heading( $current_top ); // 例: 指先の活動
} else {
	$heading = 'すべて';
}

// 一覧のパネル・小タブの色（薄い色が未設定なら CSS でタブの色から作る）
$area_style = '--area-color:' . ( $current_top ? monte_area_color( $current_top ) : '#b5694a' ) . ';';
if ( $current_top ) {
	foreach ( array( 1, 2 ) as $n ) {
		$light = monte_area_light( $current_top, $n );
		if ( $light ) {
			$area_style .= "--area-light{$n}:{$light};";
		}
	}
}
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
			<?php foreach ( $top_areas as $area ) : ?>
				<a class="area-tab<?php echo $current_top && $current_top->term_id === $area->term_id ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( get_term_link( $area ) ); ?>"
					style="--area-color:<?php echo esc_attr( monte_area_color( $area ) ); ?>"><?php echo monte_area_tab_label( $area->name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php endforeach; ?>
		</nav>
	</div>
</header>

<main class="catalog" style="<?php echo esc_attr( $area_style ); ?>">
	<div class="container">
		<h2 class="catalog__heading"><?php echo esc_html( $heading ); ?></h2>

		<?php if ( $stages || $links ) : ?>
			<div class="guide<?php echo $stages && $links ? '' : ' guide--single'; ?>">
				<?php if ( $stages ) : ?>
					<div class="guide__steps">
						<p class="guide__title"><?php echo esc_html( monte_area_step_title( $current_top ) ); ?></p>
						<ol class="steps">
							<?php foreach ( $stages as $i => $stage ) : ?>
								<li class="steps__item<?php echo $period === $stage->slug ? ' is-active' : ''; ?>">
									<span class="steps__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
									<span class="steps__label"><?php echo esc_html( $stage->name ); ?></span>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				<?php endif; ?>
				<?php if ( $links ) : ?>
					<div class="guide__links">
						<p class="guide__title">関連資料</p>
						<ul class="doc-links">
							<?php foreach ( $links as list( $label, $url ) ) : ?>
								<li><a class="doc-link" href="<?php echo esc_url( $url ?: '#' ); ?>"<?php echo $url ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $label ); ?><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M6 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="subbar">
			<?php if ( $stages ) : ?>
				<nav class="sub-tabs" aria-label="発達段階">
					<?php foreach ( $stages as $stage ) : ?>
						<a class="sub-tab<?php echo $period === $stage->slug ? ' is-active' : ''; ?>"
							href="<?php echo esc_url( add_query_arg( array_merge( $keep, array( 'period' => $stage->slug ) ), $base_url ) ); ?>"><?php echo esc_html( $stage->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php else : ?>
				<div></div>
			<?php endif; ?>
			<button class="filter-button<?php echo monte_filter_count() ? ' is-active' : ''; ?>" type="button" aria-haspopup="dialog" data-filter-open>
				<?php monte_icon( 'filter' ); ?>
				絞り込み
				<?php if ( monte_filter_count() ) : ?>
					<span class="filter-button__count"><?php echo esc_html( monte_filter_count() ); ?></span>
				<?php endif; ?>
				<svg class="filter-button__chevron" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 6l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
			<p class="catalog__empty">
				<?php if ( monte_filter_count() || $period || is_search() ) : ?>
					条件に合う活動が見つかりませんでした。<br>条件を変えてお試しください。
				<?php else : ?>
					該当する活動はまだありません。
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<nav class="pager" aria-label="ページ送り">
			<span class="pager__prev"><?php previous_posts_link( '<span class="pager__arrow" aria-hidden="true">&larr;</span>Prev' ); ?></span>
			<span class="pager__next"><?php next_posts_link( 'Next<span class="pager__arrow" aria-hidden="true">&rarr;</span>' ); ?></span>
		</nav>
	</div>
</main>

<?php get_template_part( 'template-parts/filter', null, array( 'base_url' => $base_url ) ); ?>
