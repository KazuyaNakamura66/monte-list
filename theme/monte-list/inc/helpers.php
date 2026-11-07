<?php
/**
 * テンプレート用の関数と一覧クエリの調整。
 */

defined( 'ABSPATH' ) || exit;

const MONTE_POST_TYPES = array( 'activity', 'tool' );
const MONTE_PER_PAGE   = 9;
// 絞り込みで「その他」に入れずに最初から見せる手の動きの数
const MONTE_MOTION_VISIBLE = 10;

add_action( 'after_setup_theme', function () {
	add_image_size( 'monte-card', 720, 450, true );
	add_image_size( 'monte-main', 1100, 860, false );
} );

// 一覧（トップ・領域別・検索）: 教具と活動を9件ずつ、タブと絞り込みの条件で表示する。
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( ! ( $q->is_home() || $q->is_tax() || $q->is_search() ) ) {
		return;
	}

	$q->set( 'post_type', MONTE_POST_TYPES );
	$q->set( 'posts_per_page', MONTE_PER_PAGE );

	$f         = monte_filters();
	$tax_query = array();
	if ( $f['period'] ) {
		$tax_query[] = array( 'taxonomy' => 'stage', 'field' => 'slug', 'terms' => $f['period'] );
	}
	if ( $f['hand'] ) {
		$tax_query[] = array( 'taxonomy' => 'hand_motion', 'field' => 'slug', 'terms' => $f['hand'] );
	}
	if ( $f['source'] ) {
		$tax_query[] = array( 'taxonomy' => 'acquire', 'field' => 'slug', 'terms' => $f['source'] );
	}
	if ( $tax_query ) {
		$existing = $q->get( 'tax_query' ) ?: array();
		$q->set( 'tax_query', array_merge( $existing, $tax_query ) );
	}

	if ( 'yes' === $f['video'] ) {
		$q->set( 'meta_query', array( array( 'key' => 'video_url', 'value' => '', 'compare' => '!=' ) ) );
	} elseif ( 'no' === $f['video'] ) {
		$q->set( 'meta_query', array(
			'relation' => 'OR',
			array( 'key' => 'video_url', 'compare' => 'NOT EXISTS' ),
			array( 'key' => 'video_url', 'value' => '' ),
		) );
	}
} );

/**
 * URL から一覧の条件を読む。
 * period=発達段階タブ, hand[]=手の動き, source[]=入手方法, video=yes|no
 * （taxonomy の query var と名前が被らないようにしている）
 */
function monte_filters(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$slugs = function ( string $key ): array {
		$raw = isset( $_GET[ $key ] ) ? (array) wp_unslash( $_GET[ $key ] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
		return array_values( array_filter( array_map( 'sanitize_title', $raw ) ) );
	};
	$video = isset( $_GET['video'] ) ? sanitize_key( wp_unslash( $_GET['video'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

	$cache = array(
		'period' => isset( $_GET['period'] ) ? sanitize_title( wp_unslash( $_GET['period'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
		'hand'   => $slugs( 'hand' ),
		'source' => $slugs( 'source' ),
		'video'  => in_array( $video, array( 'yes', 'no' ), true ) ? $video : '',
	);
	return $cache;
}

/** 絞り込み（ダイアログ側）で選ばれている条件の数。 */
function monte_filter_count(): int {
	$f = monte_filters();
	return count( $f['hand'] ) + count( $f['source'] ) + ( $f['video'] ? 1 : 0 );
}

/** term meta「order」の順に並べたタームを返す（未設定は後ろ）。 */
function monte_sorted_terms( array $args ): array {
	$terms = get_terms( array_merge( array( 'hide_empty' => false ), $args ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$key = fn( WP_Term $t ) => array( (int) ( get_term_meta( $t->term_id, 'order', true ) === '' ? 9999 : get_term_meta( $t->term_id, 'order', true ) ), $t->term_id );
	usort( $terms, fn( $a, $b ) => $key( $a ) <=> $key( $b ) );
	return $terms;
}

/** 大分類（微細運動など）。 */
function monte_top_areas(): array {
	return monte_sorted_terms( array( 'taxonomy' => 'area', 'parent' => 0 ) );
}

/** 領域のタブに出す発達段階（親の段階のうち、表示する領域が一致するもの）。 */
function monte_area_stages( int $area_id ): array {
	$stages = monte_sorted_terms( array( 'taxonomy' => 'stage', 'parent' => 0 ) );
	return array_values( array_filter( $stages, fn( $t ) => (int) get_field( 'stage_area', $t ) === $area_id ) );
}

/** 表示中の領域（大分類）。 */
function monte_current_area(): ?WP_Term {
	// 絞り込み条件があると get_queried_object() は別の分類を返すことがあるため、URL の領域から取る。
	$slug = get_query_var( 'area' );
	if ( ! $slug ) {
		return null;
	}
	$term = get_term_by( 'slug', basename( $slug ), 'area' );
	if ( ! $term ) {
		return null;
	}
	return $term->parent ? get_term( $term->parent, 'area' ) : $term;
}

function monte_area_color( WP_Term $term ): string {
	$top   = $term->parent ? get_term( $term->parent, 'area' ) : $term;
	$color = function_exists( 'get_field' ) ? get_field( 'area_color', $top ) : '';
	return $color ?: '#b89383';
}

/** 記事に付いた分類のうち1つを返す（prefer_child: 子を優先）。 */
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

/** 記事の領域（大分類）。 */
function monte_post_area( int $post_id ): ?WP_Term {
	$term = monte_post_term( $post_id, 'area' );
	if ( $term && $term->parent ) {
		return get_term( $term->parent, 'area' );
	}
	return $term;
}

/** カード左上のタグ（手の動き → なければ領域）。 */
function monte_card_tag( int $post_id ): string {
	$term = monte_post_term( $post_id, 'hand_motion', false ) ?: monte_post_area( $post_id );
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

/** タームのアイコン画像（未設定なら空）。 */
function monte_term_icon( WP_Term $term ): string {
	$id = function_exists( 'get_field' ) ? get_field( 'term_icon', $term ) : 0;
	return $id ? wp_get_attachment_image( $id, 'thumbnail', false, array( 'class' => 'chip__icon', 'alt' => '' ) ) : '';
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
