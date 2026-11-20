<?php
/**
 * 教具・活動の「複製」。一覧の行リンクから、同じ内容の下書きを作って編集画面を開く。
 */

foreach ( array( 'tool', 'activity' ) as $monte_type ) {
	add_filter( 'post_row_actions', function ( array $actions, WP_Post $post ) use ( $monte_type ): array {
		if ( $monte_type !== $post->post_type || ! current_user_can( 'edit_posts' ) || 'trash' === $post->post_status ) {
			return $actions;
		}
		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=monte_duplicate&post=' . $post->ID ),
			'monte_duplicate_' . $post->ID
		);
		$link = '<a href="' . esc_url( $url ) . '">複製</a>';

		// 「編集」のすぐ後ろに置く。
		$new = array();
		foreach ( $actions as $key => $html ) {
			$new[ $key ] = $html;
			if ( 'edit' === $key ) {
				$new['monte_duplicate'] = $link;
			}
		}
		if ( ! isset( $new['monte_duplicate'] ) ) {
			$new['monte_duplicate'] = $link;
		}
		return $new;
	}, 10, 2 );
}

add_action( 'admin_post_monte_duplicate', function () {
	$id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
	check_admin_referer( 'monte_duplicate_' . $id );

	$source = get_post( $id );
	if ( ! $source || ! in_array( $source->post_type, array( 'tool', 'activity' ), true ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_die( 'この記事は複製できません。' );
	}

	$new_id = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => $source->post_type,
				'post_status'  => 'draft',
				'post_title'   => $source->post_title . '（コピー）',
				'post_content' => $source->post_content,
				'post_excerpt' => $source->post_excerpt,
				'post_author'  => get_current_user_id(),
				'menu_order'   => $source->menu_order,
			)
		),
		true
	);
	if ( is_wp_error( $new_id ) ) {
		wp_die( esc_html( $new_id->get_error_message() ) );
	}

	// カテゴリー・サブカテゴリー・ラベル・入手方法。
	foreach ( get_object_taxonomies( $source->post_type ) as $taxonomy ) {
		$terms = wp_get_object_terms( $id, $taxonomy, array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $terms ) ) {
			wp_set_object_terms( $new_id, $terms, $taxonomy );
		}
	}

	// 写真・目的・用意するもの・作り方などの入力項目（アイキャッチ含む）。
	$skip = array( '_edit_lock', '_edit_last', '_wp_old_slug', '_wp_old_date' );
	foreach ( get_post_meta( $id ) as $key => $values ) {
		if ( in_array( $key, $skip, true ) ) {
			continue;
		}
		foreach ( $values as $value ) {
			add_post_meta( $new_id, $key, wp_slash( maybe_unserialize( $value ) ) );
		}
	}

	wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
	exit;
} );
