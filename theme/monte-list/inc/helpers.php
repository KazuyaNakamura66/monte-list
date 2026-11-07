<?php
/**
 * テンプレート用の関数と一覧クエリの調整。
 */

defined( 'ABSPATH' ) || exit;

const MONTE_POST_TYPES = array( 'activity', 'tool' );
const MONTE_PER_PAGE   = 9;

add_action( 'after_setup_theme', function () {
	add_image_size( 'monte-card', 720, 450, true );
	add_image_size( 'monte-main', 1100, 860, false );
} );

// トップ・領域別一覧・検索では教具と活動を9件ずつ表示する。
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_home() || $q->is_tax( array( 'area', 'stage', 'acquire' ) ) || $q->is_search() ) {
		$q->set( 'post_type', MONTE_POST_TYPES );
		$q->set( 'posts_per_page', MONTE_PER_PAGE );
	}
} );

/** 大分類（微細運動など）を表示順で返す。 */
function monte_top_areas(): array {
	$terms = get_terms( array(
		'taxonomy'   => 'area',
		'parent'     => 0,
		'hide_empty' => false,
		'meta_key'   => 'order',
		'orderby'    => 'meta_value_num',
		'order'      => 'ASC',
	) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/** 小分類を表示順で返す。 */
function monte_child_areas( int $parent_id ): array {
	$terms = get_terms( array(
		'taxonomy'   => 'area',
		'parent'     => $parent_id,
		'hide_empty' => false,
		'meta_key'   => 'order',
		'orderby'    => 'meta_value_num',
		'order'      => 'ASC',
	) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * 現在表示中の領域を返す。
 * @return array{0: ?WP_Term, 1: ?WP_Term} [大分類, 小分類]
 */
function monte_current_area(): array {
	if ( ! is_tax( 'area' ) ) {
		return array( null, null );
	}
	$term = get_queried_object();
	if ( $term->parent ) {
		return array( get_term( $term->parent, 'area' ), $term );
	}
	return array( $term, null );
}

function monte_area_color( WP_Term $term ): string {
	$top   = $term->parent ? get_term( $term->parent, 'area' ) : $term;
	$color = function_exists( 'get_field' ) ? get_field( 'area_color', $top ) : '';
	return $color ?: '#b89383';
}

/** 記事に付いた分類のうち、指定した階層のタームを1つ返す（child: 子を優先）。 */
function monte_post_term( int $post_id, string $taxonomy, bool $prefer_child = true ): ?WP_Term {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	if ( $prefer_child ) {
		foreach ( $terms as $t ) {
			if ( $t->parent ) {
				return $t;
			}
		}
	}
	return $terms[0];
}

/** カード左上のタグ（小分類 → なければ大分類）。 */
function monte_card_tag( int $post_id ): string {
	$term = monte_post_term( $post_id, 'area' );
	return $term ? $term->name : '';
}

/** カードのサブタイトル（発達段階 → なければキャッチコピー）。 */
function monte_card_subtitle( int $post_id ): string {
	$term = monte_post_term( $post_id, 'stage' );
	if ( $term ) {
		return $term->name;
	}
	return function_exists( 'get_field' ) ? (string) get_field( 'catch', $post_id ) : '';
}

function monte_has_video( int $post_id ): bool {
	return function_exists( 'get_field' ) && (bool) get_field( 'video_url', $post_id );
}

function monte_option( string $name ): string {
	return function_exists( 'get_field' ) ? (string) get_field( $name, 'option' ) : '';
}

/** リピーター（text 1列）の値を文字列の配列にする。 */
function monte_list_items( $rows ): array {
	if ( ! is_array( $rows ) ) {
		return array();
	}
	return array_values( array_filter( array_map( fn( $r ) => trim( (string) ( $r['text'] ?? '' ) ), $rows ) ) );
}

/** YouTube などの動画を埋め込む。 */
function monte_video( $url ): void {
	if ( ! $url || ! is_string( $url ) ) {
		return;
	}
	$embed = wp_oembed_get( $url );
	if ( $embed ) {
		echo '<div class="video">' . $embed . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	} else {
		printf( '<p><a href="%s" target="_blank" rel="noopener">動画を見る</a></p>', esc_url( $url ) );
	}
}
