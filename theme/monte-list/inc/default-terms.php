<?php
/**
 * 初期の分類（領域・発達段階・手の動き・入手方法）を登録する。
 * MONTE_TERMS_VERSION を上げると、未適用の分だけ追加で実行される。
 * 名前・色・順番はあとから管理画面で変更できる。
 */

defined( 'ABSPATH' ) || exit;

const MONTE_TERMS_VERSION = 2;

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

function monte_ensure_term( string $taxonomy, string $name, string $slug, int $parent = 0 ): int {
	$existing = get_term_by( 'slug', $slug, $taxonomy );
	if ( $existing ) {
		return (int) $existing->term_id;
	}
	$result = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug, 'parent' => $parent ) );
	return is_wp_error( $result ) ? 0 : (int) $result['term_id'];
}
