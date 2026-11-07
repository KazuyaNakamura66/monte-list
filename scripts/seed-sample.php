<?php
/**
 * ローカル確認用のサンプル記事を作る（デザインカンプの内容）。
 * 実行: npx wp-env run cli wp eval-file scripts/seed-sample.php
 * 何度実行しても同じ記事が重複しないよう、スラッグで判定する。
 */

function seed_post( string $type, string $slug, string $title, array $terms, array $fields ): int {
	$existing = get_page_by_path( $slug, OBJECT, $type );
	$id       = $existing ? $existing->ID : wp_insert_post( array(
		'post_type'   => $type,
		'post_name'   => $slug,
		'post_title'  => $title,
		'post_status' => 'publish',
	) );
	foreach ( $terms as $taxonomy => $slugs ) {
		wp_set_object_terms( $id, $slugs, $taxonomy );
	}
	foreach ( $fields as $name => $value ) {
		update_field( $name, $value, $id );
	}
	WP_CLI::log( "{$title}: " . get_permalink( $id ) );
	return $id;
}

$items = fn( array $texts ) => array_map( fn( $t ) => array( 'text' => $t ), $texts );

seed_post( 'tool', 'pink-tower', 'ピンクタワー',
	array( 'area' => array( 'sensorial' ), 'acquire' => array( 'buy', 'rental' ) ),
	array(
		'catch'        => '「大きさ」だけに集中できる感覚教具',
		'lead'         => 'ピンク色に塗られた10個の木製の立体。縦、横、高さの３方向が変化する３次元の教具。10個の立方体の１辺は、１cmから10cmまで１cmずつ長くなる。',
		'guideline'    => '3歳頃〜6歳頃',
		'recommend'    => $items( array( '積み木が好き', '並べることが好き', '大きさの違いに興味がある', '集中して活動したい' ) ),
		'aim_direct'   => '視覚による３次元の変化を捉える',
		'aim_indirect' => '算数教育への準備',
		'overview'     => "ピンクタワーでは、「大きさ（次元）」と「重さ」の違いを、視覚と触覚を使いながら感じていきます。\n子どもは、運ぶ。並べる。積む。崩す。また積む。\nそんな活動を繰り返しながら、大きさの違いを感覚で理解していきます。",
		'abilities'    => $items( array( '集中力と手指のコントロール', '順序性と論理的思考力', '算数教育の土台づくり', '間違いを自分で直す力' ) ),
		'steps'        => array(
			array( 'heading' => 'ステップ1：運ぶ・置く', 'images' => array(), 'body' => "まずは、ピンクタワーを運ぶことから始まります。\n最初は完成させることよりも、「やってみたい」という気持ちを大切にしましょう。" ),
			array( 'heading' => 'ステップ2：垂直に積む', 'images' => array(), 'body' => '視覚と触覚を使いながら、大きさを識別して積み上げます。' ),
		),
	)
);

$ball = seed_post( 'activity', 'ball-drop', 'ボール落とし',
	array( 'area' => array( 'fine-motor' ), 'hand_motion' => array( 'grasp' ), 'stage' => array( 'grasp-reflex' ), 'acquire' => array( 'handmade' ) ),
	array(
		'catch'        => 'つかんで、狙って、落とす',
		'lead'         => "つかんだボールを容器の穴に入れ、落とす活動です。\n目と手の協応や、指先のコントロールを育みます。",
		'guideline'    => 'つかんだものを意図的に離せるようになったら',
		'recommend'    => $items( array( '落とすのが好き', '手全体で物を掴める' ) ),
		'overview'     => "つかんだものを手放せるようになったら、ぜひ取り入れてみてください。\n100均の材料で簡単に手作りできます。",
		'materials'    => array(
			array( 'name' => '味噌タッパー', 'url' => 'https://example.com', 'note' => '' ),
			array( 'name' => 'リメイクシート', 'url' => '', 'note' => '' ),
			array( 'name' => 'ボール', 'url' => '', 'note' => '' ),
			array( 'name' => '排水口カバー', 'url' => 'https://example.com', 'note' => '' ),
			array( 'name' => '接着剤', 'url' => '', 'note' => '排水口カバーと味噌タッパーを固定するために使用します' ),
		),
		'presentation' => "大きめの穴をひとつ開けておき、ボールを落として遊びます。\n穴が大きいため、初めての落とす遊びにも取り組みやすい活動です。",
		'levelup'      => $items( array( '対象物を小さくする', '穴を小さくする', '落とすものの数を増やす' ) ),
	)
);

// 一覧表示確認用に活動をもう少し増やす
for ( $i = 1; $i <= 8; $i++ ) {
	seed_post( 'activity', "sample-{$i}", 'にぎる・ひらく',
		array(
			'area'        => array( 'fine-motor' ),
			'hand_motion' => array( $i <= 5 ? 'grasp' : 'pinch' ),
			'stage'       => array( $i <= 5 ? 'grasp-reflex' : 'tsumamu' ),
			'acquire'     => array( 0 === $i % 2 ? 'buy' : 'substitute' ),
		),
		array( 'catch' => 'サンプル活動', 'video_url' => 0 === $i % 3 ? 'https://youtu.be/sample' : '' )
	);
}
