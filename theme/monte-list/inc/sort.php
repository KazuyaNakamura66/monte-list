<?php
/**
 * 並び順の指定。
 * 管理画面の「教具一覧」「活動一覧」で行をドラッグすると、サイトの一覧もその順になる。
 * 並べ替えていない新しい記事は先頭に入る（順序 0）。
 */

defined( 'ABSPATH' ) || exit;

const MONTE_SORT_ORDER = array(
	'menu_order' => 'ASC',
	'date'       => 'DESC',
	'ID'         => 'DESC', // 同じ日時のときも並びを固定する
);

// サイトの一覧: 指定した順 → 新しい順。検索は関連度順のまま。
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() || $q->is_search() ) {
		return;
	}
	if ( $q->is_home() || $q->is_tax() ) {
		$q->set( 'orderby', MONTE_SORT_ORDER );
	}
}, 20 );

// 管理画面の一覧: 並べ替えた順で表示する（列見出しで並べ替えたときは除く）。
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || ! monte_is_sortable_screen() ) {
		return;
	}
	if ( empty( $_GET['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$q->set( 'orderby', MONTE_SORT_ORDER );
	}
} );

function monte_is_sortable_screen(): bool {
	global $pagenow;
	$type = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	return 'edit.php' === $pagenow && in_array( $type, MONTE_POST_TYPES, true );
}

add_action( 'admin_enqueue_scripts', function () {
	if ( ! monte_is_sortable_screen() || ! empty( $_GET['orderby'] ) || ! empty( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$dir = get_template_directory();
	wp_enqueue_script( 'monte-admin-sort', get_template_directory_uri() . '/assets/js/admin-sort.js', array( 'jquery', 'jquery-ui-sortable' ), filemtime( "{$dir}/assets/js/admin-sort.js" ), true );
	wp_localize_script( 'monte-admin-sort', 'monteSort', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'monte_sort' ),
		'postType' => sanitize_key( $_GET['post_type'] ), // phpcs:ignore WordPress.Security.NonceVerification
		'paged'    => max( 1, (int) ( $_GET['paged'] ?? 1 ) ), // phpcs:ignore WordPress.Security.NonceVerification
	) );
	wp_add_inline_style( 'common', '
		.monte-sortable #the-list tr { cursor: grab; }
		.monte-sortable #the-list tr.ui-sortable-helper { cursor: grabbing; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,.15); }
		.monte-sortable #the-list .ui-sortable-placeholder { visibility: visible !important; background: #f0f6fc; }
		.monte-sort-note { margin: 8px 0; color: #646970; }
	' );
} );

/**
 * 並べ替えの保存。
 * 表示中のページの並びを、その種類の全記事の並びの中に差し込んで 1..N を振り直す。
 */
add_action( 'wp_ajax_monte_sort', function () {
	check_ajax_referer( 'monte_sort', 'nonce' );

	$type = sanitize_key( $_POST['postType'] ?? '' );
	if ( ! in_array( $type, MONTE_POST_TYPES, true ) || ! current_user_can( get_post_type_object( $type )->cap->edit_others_posts ) ) {
		wp_send_json_error( 'forbidden', 403 );
	}

	$page_ids = array_map( 'absint', (array) ( $_POST['ids'] ?? array() ) );

	$all = get_posts( array(
		'post_type'      => $type,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'posts_per_page' => -1,
		'orderby'        => MONTE_SORT_ORDER,
		'fields'         => 'ids',
	) );

	// 表示中ページの記事が元々あった位置に、新しい順で入れ直す
	$positions = array_keys( array_intersect( $all, $page_ids ) );
	if ( count( $positions ) !== count( $page_ids ) ) {
		wp_send_json_error( 'mismatch', 400 );
	}
	foreach ( $positions as $i => $pos ) {
		$all[ $pos ] = $page_ids[ $i ];
	}

	global $wpdb;
	foreach ( array_values( $all ) as $i => $id ) {
		$order = $i + 1;
		if ( (int) get_post_field( 'menu_order', $id ) !== $order ) {
			$wpdb->update( $wpdb->posts, array( 'menu_order' => $order ), array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			clean_post_cache( $id );
		}
	}
	wp_send_json_success();
} );
