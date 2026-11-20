<?php
/**
 * 一覧・お気に入り共通のヒーロー（サイト名・検索・カテゴリー）。
 *
 * $args['tabs']: [ slug, label（エスケープ済み HTML）, url, color, active ] の配列
 */

$tabs     = $args['tabs'] ?? array();
$is_saved = (bool) get_query_var( 'monte_saved' );

// お気に入りページは本文側の「お気に入り」を h1 にする。
$title_tag = $is_saved ? 'p' : 'h1';
?>
<header class="catalog-hero">
	<div class="container catalog-hero__inner">
		<?php get_template_part( 'template-parts/header-links' ); ?>
		<<?php echo $title_tag; // phpcs:ignore WordPress.Security.EscapeOutput ?> class="catalog-hero__title">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
		</<?php echo $title_tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<p class="catalog-hero__sub">ACTIVITY LIBRARY</p>

		<form class="search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="catalog-search">検索</label>
			<input id="catalog-search" class="search__input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="活動名・キーワードで検索">
		</form>

		<nav class="area-tabs" aria-label="カテゴリー">
			<?php foreach ( $tabs as $tab ) : ?>
				<a class="area-tab<?php echo $tab['active'] ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( $tab['url'] ); ?>"
					style="--area-color:<?php echo esc_attr( $tab['color'] ); ?>"
					<?php echo $tab['active'] ? 'aria-current="true"' : ''; ?>
					<?php echo $is_saved ? 'data-saved-tab="' . esc_attr( $tab['slug'] ) . '"' : ''; ?>><?php echo $tab['label']; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php endforeach; ?>
		</nav>
	</div>
</header>
