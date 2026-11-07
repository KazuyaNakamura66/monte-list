<?php
/**
 * 初期の分類（領域・発達段階・入手方法）を一度だけ登録する。
 * 名前や色はあとから管理画面で変更できる。
 */

defined( 'ABSPATH' ) || exit;

const MONTE_TERMS_VERSION = 1;

add_action( 'init', function () {
	if ( (int) get_option( 'monte_terms_version' ) >= MONTE_TERMS_VERSION ) {
		return;
	}
	if ( ! function_exists( 'update_field' ) ) {
		return; // SCF が有効になってから登録する
	}

	$areas = array(
		array( 'fine-motor', '微細運動', '#b89383', '指先の活動', array(
			array( 'grasp', '握る' ),
			array( 'fingers', '指先を使う' ),
			array( 'tools', '道具を使う' ),
		) ),
		array( 'gross-motor', '粗大運動', '#8a4a5a', 'からだの活動', array() ),
		array( 'sensorial', '感覚教育', '#c5c26a', '感覚の活動', array() ),
		array( 'language', '言語教育', '#c98a6e', 'ことばの活動', array() ),
		array( 'math', '数教育', '#6a76a8', 'かずの活動', array() ),
		array( 'culture', '文化教育', '#9b88d0', '文化の活動', array() ),
	);

	foreach ( $areas as $order => list( $slug, $name, $color, $heading, $children ) ) {
		$term_id = monte_ensure_term( 'area', $name, $slug );
		if ( ! $term_id ) {
			continue;
		}
		update_field( 'area_color', $color, "area_{$term_id}" );
		update_field( 'area_heading', $heading, "area_{$term_id}" );
		update_term_meta( $term_id, 'order', $order );
		foreach ( $children as $child_order => list( $child_slug, $child_name ) ) {
			$child_id = monte_ensure_term( 'area', $child_name, $child_slug, $term_id );
			if ( $child_id ) {
				update_term_meta( $child_id, 'order', $child_order );
			}
		}
	}

	$stage = monte_ensure_term( 'stage', 'にぎる・つまむ期', 'nigiru-tsumamu' );
	if ( $stage ) {
		monte_ensure_term( 'stage', '把握反射', 'grasp-reflex', $stage );
	}

	monte_ensure_term( 'acquire', '購入', 'buy' );
	monte_ensure_term( 'acquire', 'レンタル', 'rental' );
	monte_ensure_term( 'acquire', '手作り', 'handmade' );

	update_option( 'monte_terms_version', MONTE_TERMS_VERSION );
}, 99 );

function monte_ensure_term( string $taxonomy, string $name, string $slug, int $parent = 0 ): int {
	$existing = get_term_by( 'slug', $slug, $taxonomy );
	if ( $existing ) {
		return (int) $existing->term_id;
	}
	$result = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug, 'parent' => $parent ) );
	return is_wp_error( $result ) ? 0 : (int) $result['term_id'];
}
