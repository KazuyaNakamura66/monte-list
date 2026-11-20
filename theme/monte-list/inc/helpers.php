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

// 一覧（トップ・カテゴリー別・検索）: 教具と活動を9件ずつ、タブと絞り込みの条件で表示する。
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
	$period    = $q->is_search() ? $f['period'] : monte_current_period();
	if ( $period ) {
		$tax_query[] = array( 'taxonomy' => 'stage', 'field' => 'slug', 'terms' => $period );
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

// 「すべて」タブは廃止。トップ（/）は最初のカテゴリーへ移動する（検索結果はそのまま）。
add_action( 'template_redirect', function () {
	if ( ! is_home() || is_search() || get_query_var( 'monte_saved' ) ) {
		return;
	}
	$first = monte_top_areas()[0] ?? null;
	if ( ! $first ) {
		return;
	}
	$link = get_term_link( $first );
	if ( is_wp_error( $link ) ) {
		return;
	}
	$args = array_map( 'rawurlencode_deep', wp_unslash( $_GET ) ); // phpcs:ignore WordPress.Security.NonceVerification
	wp_safe_redirect( add_query_arg( $args, $link ), 302 );
	exit;
} );

/**
 * URL から一覧の条件を読む。
 * period=サブカテゴリー, hand[]=ラベル, source[]=入手方法, video=yes|no
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

/** カテゴリーのタブに出すサブカテゴリー（カテゴリーの段階のうち、表示するカテゴリーが一致するもの）。 */
function monte_area_stages( int $area_id ): array {
	$stages = monte_sorted_terms( array( 'taxonomy' => 'stage', 'parent' => 0 ) );
	return array_values( array_filter( $stages, fn( $t ) => (int) get_field( 'stage_area', $t ) === $area_id ) );
}

/** カテゴリーのラベル。null なら、いずれかのカテゴリーに表示するものすべて。 */
function monte_area_motions( ?int $area_id = null ): array {
	$motions = monte_sorted_terms( array( 'taxonomy' => 'hand_motion', 'parent' => 0 ) );
	return array_values( array_filter( $motions, function ( WP_Term $term ) use ( $area_id ) {
		$areas = array_map( 'intval', (array) get_field( 'motion_area', $term ) );
		return null === $area_id ? (bool) $areas : in_array( $area_id, $areas, true );
	} ) );
}

/** 表示中のカテゴリー（大分類）。 */
function monte_current_area(): ?WP_Term {
	// 絞り込み条件があると get_queried_object() は別の分類を返すことがあるため、URL のカテゴリーから取る。
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

/**
 * タブの名前を出力する。「の」を含む名前は「の」の後ろで2行にする（例: 日常生活の / 練習）。
 */
function monte_area_tab_label( string $name ): string {
	$pos = mb_strrpos( $name, 'の' );
	if ( false === $pos || $pos === mb_strlen( $name ) - 1 ) {
		return esc_html( $name );
	}
	return sprintf(
		'<span class="area-tab__part">%s</span><span class="area-tab__part">%s</span>',
		esc_html( mb_substr( $name, 0, $pos + 1 ) ),
		esc_html( mb_substr( $name, $pos + 1 ) )
	);
}

/**
 * 表示中のサブカテゴリー（slug）。「すべて」タブはないため、
 * 未指定・このカテゴリーにない段階なら最初の段階にする。段階のないカテゴリーは空。
 */
function monte_current_period(): string {
	$area = monte_current_area();
	if ( ! $area ) {
		return '';
	}
	$slugs = wp_list_pluck( monte_area_stages( $area->term_id ), 'slug' );
	if ( ! $slugs ) {
		return '';
	}
	$period = monte_filters()['period'];
	return in_array( $period, $slugs, true ) ? $period : $slugs[0];
}

function monte_area_color( WP_Term $term ): string {
	$top   = $term->parent ? get_term( $term->parent, 'area' ) : $term;
	$color = function_exists( 'get_field' ) ? get_field( 'area_color', $top ) : '';
	return $color ?: '#b89383';
}

/** 一覧のパネル背景用の薄い色（1 or 2）。未設定なら空文字（CSS 側でタブの色から作る）。 */
function monte_area_light( WP_Term $area, int $n ): string {
	return function_exists( 'get_field' ) ? (string) get_field( "area_color_light{$n}", $area ) : '';
}

/** 一覧の見出し（未入力ならカテゴリー名）。 */
function monte_area_heading( WP_Term $area ): string {
	$heading = function_exists( 'get_field' ) ? trim( (string) get_field( 'list_heading', $area ) ) : '';
	return $heading ?: $area->name;
}

/** 一覧のステップ欄の見出し（未入力なら「発達ステップ」）。 */
function monte_area_step_title( WP_Term $area ): string {
	$title = function_exists( 'get_field' ) ? trim( (string) get_field( 'step_title', $area ) ) : '';
	return $title ?: '発達ステップ';
}

/** 一覧の「関連資料」リンク。[ [label, url], ... ]（未入力なら空） */
function monte_area_links( ?WP_Term $area ): array {
	$rows  = $area && function_exists( 'get_field' ) ? get_field( 'area_links', $area ) : array();
	$links = array();
	foreach ( is_array( $rows ) ? $rows : array() as $row ) {
		$label = trim( (string) ( $row['label'] ?? '' ) );
		if ( '' !== $label ) {
			$links[] = array( $label, (string) ( $row['url'] ?? '' ) );
		}
	}
	return $links;
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

/** 記事のカテゴリー（大分類）。 */
function monte_post_area( int $post_id ): ?WP_Term {
	$term = monte_post_term( $post_id, 'area' );
	if ( $term && $term->parent ) {
		return get_term( $term->parent, 'area' );
	}
	return $term;
}

/** 記事が属する大分類の slug（お気に入りのタブ切り替え用）。 */
function monte_post_area_slugs( int $post_id ): array {
	$terms = get_the_terms( $post_id, 'area' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}
	$slugs = array_map(
		fn( WP_Term $t ) => $t->parent ? get_term( $t->parent, 'area' )->slug : $t->slug,
		$terms
	);
	return array_values( array_unique( $slugs ) );
}

/**
 * 一覧で前後に並ぶ記事の ID。同じカテゴリー・同じサブカテゴリーのタブの中を、一覧と同じ順で移動する。
 *
 * @return array{prev: int, next: int} ない場合は 0
 */
function monte_adjacent_posts( int $post_id ): array {
	$tax_query = array();
	$area      = monte_post_area( $post_id );
	if ( $area ) {
		$tax_query[] = array( 'taxonomy' => 'area', 'field' => 'term_id', 'terms' => $area->term_id );

		$stage_ids = wp_get_post_terms( $post_id, 'stage', array( 'fields' => 'ids' ) );
		$stage_ids = is_wp_error( $stage_ids ) ? array() : array_map( 'intval', $stage_ids );
		foreach ( monte_area_stages( $area->term_id ) as $tab ) {
			$tab_ids = array_merge( array( $tab->term_id ), get_term_children( $tab->term_id, 'stage' ) );
			if ( array_intersect( $tab_ids, $stage_ids ) ) {
				$tax_query[] = array( 'taxonomy' => 'stage', 'field' => 'term_id', 'terms' => $tab->term_id );
				break;
			}
		}
	}

	$ids = array_map( 'intval', get_posts( array(
		'post_type'      => MONTE_POST_TYPES,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => MONTE_SORT_ORDER,
		'fields'         => 'ids',
		'tax_query'      => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery
	) ) );

	$index = array_search( $post_id, $ids, true );
	if ( false === $index ) {
		return array( 'prev' => 0, 'next' => 0 );
	}
	return array(
		'prev' => $ids[ $index - 1 ] ?? 0,
		'next' => $ids[ $index + 1 ] ?? 0,
	);
}

/** カード左上のタグ（ラベル → なければカテゴリー）。 */
function monte_card_tag( int $post_id ): string {
	$term = monte_post_term( $post_id, 'hand_motion', false ) ?: monte_post_area( $post_id );
	return $term ? $term->name : '';
}

/** カードのサブタイトル（サブカテゴリー → なければキャッチコピー）。 */
function monte_card_subtitle( int $post_id ): string {
	$term = monte_post_term( $post_id, 'stage' );
	if ( $term ) {
		return $term->name;
	}
	return function_exists( 'get_field' ) ? (string) get_field( 'catch', $post_id ) : '';
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
