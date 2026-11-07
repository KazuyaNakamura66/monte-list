<?php
/**
 * 投稿タイプ（教具・活動）と分類（領域・発達段階・入手方法）。
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	$supports = array( 'title', 'thumbnail', 'revisions' );

	register_post_type( 'tool', array(
		'labels'        => monte_post_type_labels( '教具' ),
		'public'        => true,
		'has_archive'   => false,
		'rewrite'       => array( 'slug' => 'tools' ),
		'menu_icon'     => 'dashicons-screenoptions',
		'menu_position' => 5,
		'supports'      => $supports,
		'show_in_rest'  => true,
	) );

	register_post_type( 'activity', array(
		'labels'        => monte_post_type_labels( '活動' ),
		'public'        => true,
		'has_archive'   => false,
		'rewrite'       => array( 'slug' => 'activities' ),
		'menu_icon'     => 'dashicons-heart',
		'menu_position' => 6,
		'supports'      => $supports,
		'show_in_rest'  => true,
	) );

	$types = array( 'tool', 'activity' );

	register_taxonomy( 'area', $types, array(
		'labels'            => monte_taxonomy_labels( '領域' ),
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'area', 'hierarchical' => true ),
	) );

	register_taxonomy( 'stage', $types, array(
		'labels'            => monte_taxonomy_labels( '発達段階' ),
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'stage', 'hierarchical' => true ),
	) );

	register_taxonomy( 'acquire', $types, array(
		'labels'            => monte_taxonomy_labels( '入手方法' ),
		'hierarchical'      => true, // チェックボックスで選べるようにする
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'acquire' ),
	) );
} );

function monte_post_type_labels( string $name ): array {
	return array(
		'name'               => $name,
		'singular_name'      => $name,
		'add_new'            => '新規追加',
		'add_new_item'       => "{$name}を追加",
		'edit_item'          => "{$name}を編集",
		'new_item'           => "新しい{$name}",
		'view_item'          => "{$name}を表示",
		'search_items'       => "{$name}を検索",
		'not_found'          => "{$name}が見つかりません",
		'not_found_in_trash' => "ゴミ箱に{$name}はありません",
		'all_items'          => "{$name}一覧",
		'menu_name'          => $name,
	);
}

function monte_taxonomy_labels( string $name ): array {
	return array(
		'name'          => $name,
		'singular_name' => $name,
		'search_items'  => "{$name}を検索",
		'all_items'     => "すべての{$name}",
		'parent_item'   => "親の{$name}",
		'edit_item'     => "{$name}を編集",
		'update_item'   => "{$name}を更新",
		'add_new_item'  => "{$name}を追加",
		'new_item_name' => "新しい{$name}名",
		'menu_name'     => $name,
	);
}
