<?php
/**
 * 管理画面の調整。
 */

defined( 'ABSPATH' ) || exit;

// 標準の「投稿」は使わないので非表示にする。
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit.php' );
} );

// 教具一覧・活動一覧の先頭に写真を表示する。
foreach ( array( 'tool', 'activity' ) as $monte_type ) {
	add_filter( "manage_{$monte_type}_posts_columns", function ( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['monte_thumb'] = '写真';
			}
			$new[ $key ] = $label;
		}
		return $new;
	} );

	add_action( "manage_{$monte_type}_posts_custom_column", function ( string $column, int $post_id ) {
		if ( 'monte_thumb' !== $column ) {
			return;
		}
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, 'thumbnail', array( 'class' => 'monte-thumb', 'alt' => '' ) );
		} else {
			echo '<span class="monte-thumb monte-thumb--empty" aria-label="写真なし"></span>';
		}
	}, 10, 2 );
}

add_action( 'admin_head-edit.php', function () {
	echo '<style>
		.column-monte_thumb { width: 72px; }
		.monte-thumb { display: block; width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
		.monte-thumb--empty { background: #f0ebe6; }
	</style>';
} );
