<?php
/**
 * お気に入りの一覧。カードは assets/js/main.js が読み込み、タブでカテゴリーごとに切り替える。
 */

$top_areas = monte_top_areas();
$current   = isset( $_GET['tab'] ) ? sanitize_title( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
if ( ! in_array( $current, wp_list_pluck( $top_areas, 'slug' ), true ) ) {
	$current = ''; // すべて
}

$tabs = array(
	array(
		'slug'   => '',
		'label'  => 'すべて',
		'url'    => monte_saved_url(),
		'color'  => 'var(--c-text)',
		'active' => '' === $current,
	),
);
foreach ( $top_areas as $area ) {
	$tabs[] = array(
		'slug'   => $area->slug,
		'label'  => monte_area_tab_label( $area->name ),
		'url'    => add_query_arg( 'tab', $area->slug, monte_saved_url() ),
		'color'  => monte_area_color( $area ),
		'active' => $current === $area->slug,
	);
}

get_header();
get_template_part( 'template-parts/catalog-hero', null, array( 'tabs' => $tabs ) );
?>

<main class="catalog catalog--saved">
	<div class="container">
		<h1 class="screen-reader-text">お気に入り</h1>

		<div class="card-grid" data-saved-list data-endpoint="<?php echo esc_url( rest_url( 'monte/v1/cards' ) ); ?>"></div>
		<p class="catalog__empty" data-saved-empty hidden>
			まだお気に入りはありません。<br>
			気になる活動のしおりボタンを押すと、ここに表示されます。<br>
			お気に入りは、この端末にのみ記録されます。
		</p>
		<p class="catalog__empty" data-saved-area-empty hidden>このカテゴリーのお気に入りはまだありません。</p>
	</div>
</main>
<?php
get_footer();
