<?php
/**
 * 初期の分類（領域・発達段階・手の動き・入手方法）を登録する。
 * MONTE_TERMS_VERSION を上げると、未適用の分だけ追加で実行される。
 * 名前・色・順番はあとから管理画面で変更できる。
 */

defined( 'ABSPATH' ) || exit;

const MONTE_TERMS_VERSION = 7;

add_action( 'init', function () {
	$version = (int) get_option( 'monte_terms_version' );
	if ( $version >= MONTE_TERMS_VERSION || ! function_exists( 'update_field' ) ) {
		return; // 適用済み、または SCF が有効になる前
	}

	if ( $version < 1 ) {
		monte_terms_v1();
	}
	if ( $version < 2 ) {
		monte_terms_v2();
	}
	if ( $version < 3 ) {
		monte_terms_v3();
	}
	if ( $version < 4 ) {
		monte_terms_v4();
	}
	if ( $version < 5 ) {
		monte_terms_v5();
	}
	if ( $version < 6 ) {
		monte_terms_v6();
	}
	if ( $version < 7 ) {
		monte_terms_v7();
	}

	update_option( 'monte_terms_version', MONTE_TERMS_VERSION );
}, 99 );

/** v1: 領域・入手方法。 */
function monte_terms_v1(): void {
	$areas = array(
		array( 'fine-motor', '微細運動', '#b89383' ),
		array( 'gross-motor', '粗大運動', '#8a4a5a' ),
		array( 'sensorial', '感覚教育', '#c5c26a' ),
		array( 'language', '言語教育', '#c98a6e' ),
		array( 'math', '数教育', '#6a76a8' ),
		array( 'culture', '文化教育', '#9b88d0' ),
	);
	foreach ( $areas as $order => list( $slug, $name, $color ) ) {
		$term_id = monte_ensure_term( 'area', $name, $slug );
		if ( $term_id ) {
			update_field( 'area_color', $color, "area_{$term_id}" );
			update_term_meta( $term_id, 'order', $order );
		}
	}

	monte_ensure_term( 'acquire', '購入', 'buy' );
	monte_ensure_term( 'acquire', 'レンタル', 'rental' );
	monte_ensure_term( 'acquire', '手作り', 'handmade' );
}

/**
 * v2: 絞り込みデザインに合わせて整理。
 * - 「握る」などは領域の子ではなく「手の動き」へ
 * - 領域の下のタブは「発達段階」
 */
function monte_terms_v2(): void {
	// 旧: 領域の子（握る・指先を使う・道具を使う）を削除
	foreach ( array( 'grasp', 'fingers', 'tools' ) as $slug ) {
		$old = get_term_by( 'slug', $slug, 'area' );
		if ( $old ) {
			wp_delete_term( $old->term_id, 'area' );
		}
	}

	$motions = array(
		array( 'grasp', '握る' ),
		array( 'pinch', 'つまむ' ),
		array( 'pull', 'ひっぱる' ),
		array( 'push', '押す' ),
		array( 'turn', '回す' ),
		array( 'thread', '通す' ),
		array( 'fit', 'はめる' ),
		array( 'clip', 'はさむ' ),
		array( 'stick', '貼る' ),
		array( 'tear', 'ちぎる' ),
	);
	foreach ( $motions as $order => list( $slug, $name ) ) {
		$id = monte_ensure_term( 'hand_motion', $name, $slug );
		if ( $id ) {
			update_term_meta( $id, 'order', $order );
		}
	}

	$order = array( 'handmade' => 0, 'buy' => 1, 'substitute' => 2, 'rental' => 3 );
	monte_ensure_term( 'acquire', '身近なもので代用', 'substitute' );
	foreach ( $order as $slug => $n ) {
		$t = get_term_by( 'slug', $slug, 'acquire' );
		if ( $t ) {
			update_term_meta( $t->term_id, 'order', $n );
		}
	}

	$fine = get_term_by( 'slug', 'fine-motor', 'area' );
	$stages = array(
		array( 'nigiru-tsumamu', 'にぎる・つまむ期' ),
		array( 'tsumamu', 'つまむ期' ),
		array( 'yubisaki-senren', '指先洗練期' ),
	);
	foreach ( $stages as $n => list( $slug, $name ) ) {
		$id = monte_ensure_term( 'stage', $name, $slug );
		if ( $id ) {
			update_term_meta( $id, 'order', $n );
			if ( $fine ) {
				update_field( 'stage_area', $fine->term_id, "stage_{$id}" );
			}
		}
	}
	$parent = get_term_by( 'slug', 'nigiru-tsumamu', 'stage' );
	if ( $parent ) {
		monte_ensure_term( 'stage', '把握反射', 'grasp-reflex', $parent->term_id );
	}
}

/**
 * v3: 一覧デザイン（発達ステップ・関連資料）に合わせて整理。
 * - 「日常生活の練習」を追加し、タブの並びをデザインどおりに
 * - 見出し: 未入力のものだけ初期値を入れる
 * - 粗大運動の発達段階（ねんね期・移動期・歩行期）を追加
 */
function monte_terms_v3(): void {
	// slug => [名前, 見出し]
	$areas = array(
		'fine-motor'     => array( '微細運動', '指先の活動' ),
		'gross-motor'    => array( '粗大運動', '全身を使う活動' ),
		'practical-life' => array( '日常生活の練習', '' ),
		'sensorial'      => array( '感覚教育', '' ),
		'language'       => array( '言語教育', '' ),
		'math'           => array( '数教育', '' ),
		'culture'        => array( '文化教育', '' ),
	);
	$order = 0;
	foreach ( $areas as $slug => list( $name, $heading ) ) {
		$term_id = monte_ensure_term( 'area', $name, $slug );
		if ( ! $term_id ) {
			continue;
		}
		update_term_meta( $term_id, 'order', $order++ );
		if ( $heading && '' === (string) get_field( 'list_heading', "area_{$term_id}" ) ) {
			update_field( 'list_heading', $heading, "area_{$term_id}" );
		}
	}

	$gross = get_term_by( 'slug', 'gross-motor', 'area' );
	$stages = array(
		array( 'nenne', 'ねんね期' ),
		array( 'idou', '移動期' ),
		array( 'hokou', '歩行期' ),
	);
	foreach ( $stages as $n => list( $slug, $name ) ) {
		$exists = get_term_by( 'slug', $slug, 'stage' );
		$id     = monte_ensure_term( 'stage', $name, $slug );
		if ( $id && ! $exists ) {
			update_term_meta( $id, 'order', 10 + $n );
			if ( $gross ) {
				update_field( 'stage_area', $gross->term_id, "stage_{$id}" );
			}
		}
	}
}

/** v4: 領域の色を指定のカラーパレット（メイン・薄い色①・薄い色②）にする。 */
function monte_terms_v4(): void {
	$colors = array(
		'fine-motor'     => array( '#a99eae', '#e9e4eb', '#f4f1f5' ),
		'gross-motor'    => array( '#c77f70', '#f0dfda', '#f8f0ed' ),
		'practical-life' => array( '#b9a184', '#ede4d9', '#f6f2ec' ),
		'sensorial'      => array( '#c9a45f', '#f1e7d1', '#f9f5eb' ),
		'language'       => array( '#d39a9d', '#f3e1e2', '#faf2f2' ),
		'math'           => array( '#849bb7', '#e1e8f0', '#f1f4f8' ),
		'culture'        => array( '#899d8d', '#e2eae3', '#f1f5f1' ),
	);
	foreach ( $colors as $slug => list( $main, $light1, $light2 ) ) {
		$term = get_term_by( 'slug', $slug, 'area' );
		if ( ! $term ) {
			continue;
		}
		update_field( 'area_color', $main, "area_{$term->term_id}" );
		update_field( 'area_color_light1', $light1, "area_{$term->term_id}" );
		update_field( 'area_color_light2', $light2, "area_{$term->term_id}" );
	}
}

/**
 * v5: 残りの領域のデザイン（見出し・ステップ・関連資料）。
 * 見出し・ステップの見出し・関連資料は未入力のときだけ入れる。
 */
function monte_terms_v5(): void {
	$timeline = monte_option( 'timeline_url' );
	$record   = monte_option( 'record_sheet_url' );

	// slug => [見出し, ステップの見出し, 発達段階 [slug => 名前], 関連資料 [[文字, URL]]]
	$areas = array(
		'fine-motor'     => array( '', '', array(), array( array( '微細運動の発達タイムライン', $timeline ), array( '発達記録シート', $record ) ) ),
		'gross-motor'    => array( '', '', array(), array( array( '粗大運動の発達タイムライン', $timeline ), array( '発達記録シート', $record ) ) ),
		'practical-life' => array( '', '活動カテゴリー', array(
			'care-self' => '自分に配慮',
			'care-others' => '他人に配慮',
			'care-env' => '環境に配慮',
		), array() ),
		'sensorial'      => array( '感覚教育の活動', '', array(
			'senses' => '五感で感じる',
			'matching' => '同じものを見つける',
			'sorting' => '仲間分けする',
			'grading' => '順番に並べる',
		), array() ),
		'language'       => array( '言語教育の活動', '', array(
			'listen' => '聞く',
			'speak' => '話す',
			'write' => '書く',
			'read' => '読む',
		), array( array( '言葉の発達タイムライン', '' ), array( '言語教育一覧', '' ) ) ),
		'math'           => array( '数教育の活動', '', array(
			'number-seeds' => '数の種まき',
			'numerals' => '数字を知る',
			'number-quantity' => '数と量を結びつける',
			'number-concept' => '数の概念を広げる',
		), array( array( '数教育の発達タイムライン', $timeline ), array( '発達記録シート', $record ) ) ),
		'culture'        => array( '文化教育の活動', '', array(), array() ),
	);

	$stage_order = 20;
	foreach ( $areas as $slug => list( $heading, $step_title, $stages, $links ) ) {
		$area = get_term_by( 'slug', $slug, 'area' );
		if ( ! $area ) {
			continue;
		}
		$key = "area_{$area->term_id}";
		if ( $heading && '' === (string) get_field( 'list_heading', $key ) ) {
			update_field( 'list_heading', $heading, $key );
		}
		if ( $step_title && '' === (string) get_field( 'step_title', $key ) ) {
			update_field( 'step_title', $step_title, $key );
		}
		if ( $links && ! get_field( 'area_links', $key ) ) {
			update_field( 'area_links', array_map( fn( $l ) => array( 'label' => $l[0], 'url' => $l[1] ), $links ), $key );
		}
		foreach ( $stages as $stage_slug => $name ) {
			$exists = get_term_by( 'slug', $stage_slug, 'stage' );
			$id     = monte_ensure_term( 'stage', $name, $stage_slug );
			if ( $id && ! $exists ) {
				update_term_meta( $id, 'order', $stage_order++ );
				update_field( 'stage_area', $area->term_id, "stage_{$id}" );
			}
		}
	}
}

/** v6: 管理画面に「順番」欄を出したので、番号を 1, 2, 3… に振り直す（並びは変えない）。 */
function monte_terms_v6(): void {
	foreach ( monte_top_areas() as $i => $area ) {
		update_term_meta( $area->term_id, 'order', $i + 1 );
		foreach ( monte_area_stages( $area->term_id ) as $j => $stage ) {
			update_term_meta( $stage->term_id, 'order', $j + 1 );
		}
	}
}

/**
 * v7: 微細運動の発達段階の名前をデザインに合わせる（01 握る / 02 指先を使う / 03 道具を使う）。
 * URL（slug）は変えない。管理画面ですでに名前を変えたものはそのまま。
 */
function monte_terms_v7(): void {
	$names = array(
		'nigiru-tsumamu'  => array( 'にぎる・つまむ期', '握る' ),
		'tsumamu'         => array( 'つまむ期', '指先を使う' ),
		'yubisaki-senren' => array( '指先洗練期', '道具を使う' ),
	);
	foreach ( $names as $slug => list( $old, $new ) ) {
		$term = get_term_by( 'slug', $slug, 'stage' );
		if ( $term && $term->name === $old ) {
			wp_update_term( $term->term_id, 'stage', array( 'name' => $new ) );
		}
	}
}

function monte_ensure_term( string $taxonomy, string $name, string $slug, int $parent = 0 ): int {
	$existing = get_term_by( 'slug', $slug, $taxonomy );
	if ( $existing ) {
		return (int) $existing->term_id;
	}
	$result = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug, 'parent' => $parent ) );
	return is_wp_error( $result ) ? 0 : (int) $result['term_id'];
}
