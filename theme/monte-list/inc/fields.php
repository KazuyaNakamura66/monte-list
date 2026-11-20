<?php
/**
 * 入力項目（SCF / ACF 互換）をコードで定義する。
 * コード管理にしておくと、本番にもテーマごと同じ項目が入る。
 */

defined( 'ABSPATH' ) || exit;

// サイト設定ページ（外部リンクなど）。
add_action( 'acf/init', function () {
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page( array(
			'page_title' => 'サイト設定',
			'menu_title' => 'サイト設定',
			'menu_slug'  => 'monte-settings',
			'capability' => 'edit_others_posts', // 編集者も変更できる
			'icon_url'   => 'dashicons-admin-generic',
			'position'   => 30,
		) );
	}
} );

add_action( 'acf/include_fields', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$list = fn( string $key, string $label, string $button ) => array(
		'key'          => "field_{$key}",
		'label'        => $label,
		'name'         => $key,
		'type'         => 'repeater',
		'layout'       => 'table',
		'button_label' => $button,
		'sub_fields'   => array(
			array(
				'key'   => "field_{$key}_text",
				'label' => '項目',
				'name'  => 'text',
				'type'  => 'text',
			),
		),
	);

	$common_top = fn( string $p ) => array(
		array(
			'key'          => "field_{$p}_catch",
			'label'        => 'キャッチコピー',
			'name'         => 'catch',
			'type'         => 'text',
			'instructions' => '例：「大きさ」だけに集中できる感覚教具',
		),
		array(
			'key'          => "field_{$p}_lead",
			'label'        => '短い説明',
			'name'         => 'lead',
			'type'         => 'textarea',
			'rows'         => 3,
			'new_lines'    => 'br',
			'instructions' => 'タイトル下に表示される2〜3行の説明',
		),
		array(
			'key'          => "field_{$p}_guideline",
			'label'        => '取り入れる目安',
			'name'         => 'guideline',
			'type'         => 'text',
			'instructions' => '例：3歳頃〜6歳頃',
		),
		array_merge( $list( "{$p}_recommend", 'こんな姿がみられたら', '項目を追加' ), array( 'name' => 'recommend' ) ),
	);

	// 教具
	acf_add_local_field_group( array(
		'key'      => 'group_tool',
		'title'    => '教具の内容',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'tool' ) ) ),
		'position' => 'acf_after_title',
		'fields'   => array_merge(
			$common_top( 'tool' ),
			array(
				array(
					'key'   => 'field_tool_aim_direct',
					'label' => '直接目的',
					'name'  => 'aim_direct',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_tool_aim_indirect',
					'label' => '間接目的',
					'name'  => 'aim_indirect',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_tool_overview',
					'label'        => '教具の概要',
					'name'         => 'overview',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => 0,
				),
				array_merge( $list( 'tool_abilities', '育つ力', '項目を追加' ), array( 'name' => 'abilities' ) ),
				array(
					'key'          => 'field_tool_video_url',
					'label'        => '提示方法（YouTube URL）',
					'name'         => 'video_url',
					'type'         => 'url',
					'instructions' => '限定公開の動画URLを貼り付けてください',
				),
				array(
					'key'          => 'field_tool_steps',
					'label'        => '活用方法',
					'name'         => 'steps',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'ステップを追加',
					'sub_fields'   => array(
						array(
							'key'          => 'field_tool_steps_heading',
							'label'        => '見出し',
							'name'         => 'heading',
							'type'         => 'text',
							'instructions' => '例：ステップ1：運ぶ・置く',
						),
						array(
							'key'           => 'field_tool_steps_images',
							'label'         => '画像',
							'name'          => 'images',
							'type'          => 'gallery',
							'return_format' => 'id',
						),
						array(
							'key'          => 'field_tool_steps_body',
							'label'        => '本文',
							'name'         => 'body',
							'type'         => 'wysiwyg',
							'toolbar'      => 'basic',
							'media_upload' => 0,
						),
					),
				),
				array(
					'key'           => 'field_tool_related',
					'label'         => '関連する活動',
					'name'          => 'related',
					'type'          => 'relationship',
					'post_type'     => array( 'activity', 'tool' ),
					'filters'       => array( 'search', 'post_type', 'taxonomy' ),
					'max'           => 3,
					'return_format' => 'id',
				),
			)
		),
	) );

	// 活動
	acf_add_local_field_group( array(
		'key'      => 'group_activity',
		'title'    => '活動の内容',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'activity' ) ) ),
		'position' => 'acf_after_title',
		'fields'   => array_merge(
			$common_top( 'activity' ),
			array(
				array(
					'key'   => 'field_activity_aim',
					'label' => '目的',
					'name'  => 'aim',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_activity_overview',
					'label'        => '活動の概要',
					'name'         => 'overview',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => 0,
				),
				array(
					'key'           => 'field_activity_template_pdf',
					'label'         => 'PDFファイル',
					'name'          => 'template_pdf',
					'type'          => 'file',
					'return_format' => 'url',
					'mime_types'    => 'pdf',
					'instructions'  => '型紙・ワークシートなどのPDF。「用意するもの」の上にリンクが出ます（任意）',
				),
				array(
					'key'          => 'field_activity_template_url',
					'label'        => 'リンク先URL',
					'name'         => 'template_url',
					'type'         => 'url',
					'instructions' => 'PDFの代わりに、Googleドライブなど外部のURLにリンクする場合（任意）。PDFがあればPDFが優先されます',
				),
				array(
					'key'          => 'field_activity_template_label',
					'label'        => 'リンクの文字',
					'name'         => 'template_label',
					'type'         => 'text',
					'placeholder'  => 'ダウンロードする',
					'instructions' => '空欄なら「ダウンロードする」と表示されます',
				),
				array(
					'key'          => 'field_activity_materials',
					'label'        => '用意するもの',
					'name'         => 'materials',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'ものを追加',
					'sub_fields'   => array(
						array(
							'key'   => 'field_activity_materials_name',
							'label' => '名前',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_activity_materials_url',
							'label' => '購入先URL（任意）',
							'name'  => 'url',
							'type'  => 'url',
						),
						array(
							'key'   => 'field_activity_materials_note',
							'label' => '補足（任意）',
							'name'  => 'note',
							'type'  => 'text',
						),
					),
				),
				array(
					'key'           => 'field_activity_materials_image',
					'label'         => '用意するものの画像',
					'name'          => 'materials_image',
					'type'          => 'image',
					'return_format' => 'id',
				),
				array(
					'key'          => 'field_activity_howto',
					'label'        => '作り方',
					'name'         => 'howto',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => '手順を追加',
					'instructions' => '番号は上から順に自動で付きます。ドラッグで並べ替えできます。文中で改行すると、そのまま改行して表示されます',
					'sub_fields'   => array(
						array(
							'key'   => 'field_activity_howto_text',
							'label' => '手順',
							'name'  => 'text',
							'type'  => 'textarea',
							'rows'  => 2,
						),
					),
				),
				array(
					'key'          => 'field_activity_presentation',
					'label'        => '提示方法',
					'name'         => 'presentation',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => 0,
				),
				array(
					'key'          => 'field_activity_video_url',
					'label'        => '提示方法の動画（YouTube URL・任意）',
					'name'         => 'video_url',
					'type'         => 'url',
				),
				array_merge( $list( 'activity_levelup', '難易度の上げ方', '項目を追加' ), array( 'name' => 'levelup' ) ),
				array(
					'key'           => 'field_activity_related',
					'label'         => '関連する活動',
					'name'          => 'related',
					'type'          => 'relationship',
					'post_type'     => array( 'activity', 'tool' ),
					'filters'       => array( 'search', 'post_type', 'taxonomy' ),
					'max'           => 3,
					'return_format' => 'id',
				),
			)
		),
	) );

	// カテゴリーの設定（タブの色・見出し・関連資料）
	acf_add_local_field_group( array(
		'key'      => 'group_area',
		'title'    => 'カテゴリーの表示設定',
		'location' => array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'area' ) ) ),
		'fields'   => array(
			array(
				'key'          => 'field_area_order',
				'label'        => '順番',
				'name'         => 'order',
				'type'         => 'number',
				'step'         => 1,
				'instructions' => 'タブの並び順。小さい数字ほど左に表示されます（空欄は最後）',
			),
			array(
				'key'          => 'field_area_color',
				'label'        => 'タブの色',
				'name'         => 'area_color',
				'type'         => 'color_picker',
			),
			array(
				'key'          => 'field_area_color_light1',
				'label'        => '薄い色①',
				'name'         => 'area_color_light1',
				'type'         => 'color_picker',
				'instructions' => '一覧の「関連資料」の背景（空欄ならタブの色から自動で作ります）',
			),
			array(
				'key'          => 'field_area_color_light2',
				'label'        => '薄い色②',
				'name'         => 'area_color_light2',
				'type'         => 'color_picker',
				'instructions' => '一覧の「発達ステップ」の背景（空欄ならタブの色から自動で作ります）',
			),
			array(
				'key'          => 'field_area_list_heading',
				'label'        => '一覧の見出し',
				'name'         => 'list_heading',
				'type'         => 'text',
				'instructions' => '例：指先の活動（空欄ならカテゴリー名を表示）',
			),
			array(
				'key'          => 'field_area_step_title',
				'label'        => 'ステップの見出し',
				'name'         => 'step_title',
				'type'         => 'text',
				'placeholder'  => '発達ステップ',
				'instructions' => '一覧のステップ欄の見出し（空欄なら「発達ステップ」）。ステップには、「表示するカテゴリー」でこのカテゴリーを選んだサブカテゴリーが並びます',
			),
			array(
				'key'          => 'field_area_links',
				'label'        => '関連資料',
				'name'         => 'area_links',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => '資料を追加',
				'instructions' => '一覧の右上「関連資料」に並ぶリンク（別タブで開きます）。1件もなければ「関連資料」欄は表示しません',
				'sub_fields'   => array(
					array(
						'key'   => 'field_area_links_label',
						'label' => 'リンクの文字',
						'name'  => 'label',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_area_links_url',
						'label' => 'URL',
						'name'  => 'url',
						'type'  => 'url',
					),
				),
			),
		),
	) );

	// サブカテゴリーの設定（どのカテゴリーのタブに出すか）
	acf_add_local_field_group( array(
		'key'      => 'group_stage',
		'title'    => 'サブカテゴリーの表示設定',
		'location' => array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'stage' ) ) ),
		'fields'   => array(
			array(
				'key'          => 'field_stage_order',
				'label'        => '順番',
				'name'         => 'order',
				'type'         => 'number',
				'step'         => 1,
				'instructions' => '発達ステップ・タブの並び順。小さい数字ほど左（01側）に表示されます（空欄は最後）',
			),
			array(
				'key'           => 'field_stage_area',
				'label'         => '表示するカテゴリー',
				'name'          => 'stage_area',
				'type'          => 'taxonomy',
				'taxonomy'      => 'area',
				'field_type'    => 'select',
				'allow_null'    => 1,
				'add_term'      => 0,
				'save_terms'    => 0,
				'load_terms'    => 0,
				'return_format' => 'id',
				'instructions'  => '一覧の「発達ステップ」とタブ（例：握る）を表示するカテゴリーを選びます。下の階層のサブカテゴリー（例：把握反射）には設定不要です。並び順は「順番」で決まります',
			),
		),
	) );

	// ラベルの設定（どのカテゴリーの絞り込みに出すか）
	acf_add_local_field_group( array(
		'key'      => 'group_motion',
		'title'    => 'ラベルの表示設定',
		'location' => array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'hand_motion' ) ) ),
		'fields'   => array(
			array(
				'key'          => 'field_motion_order',
				'label'        => '順番',
				'name'         => 'order',
				'type'         => 'number',
				'step'         => 1,
				'instructions' => '絞り込みの並び順。小さい数字ほど先に表示されます（空欄は最後）',
			),
			array(
				'key'           => 'field_motion_area',
				'label'         => '表示するカテゴリー',
				'name'          => 'motion_area',
				'type'          => 'taxonomy',
				'taxonomy'      => 'area',
				'field_type'    => 'multi_select',
				'allow_null'    => 1,
				'add_term'      => 0,
				'save_terms'    => 0,
				'load_terms'    => 0,
				'return_format' => 'id',
				'instructions'  => '絞り込みと記事の編集画面に、このラベルを表示するカテゴリー（複数選択可）',
			),
		),
	) );

	// サイト設定
	acf_add_local_field_group( array(
		'key'      => 'group_settings',
		'title'    => 'サイト設定',
		'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'monte-settings' ) ) ),
		'fields'   => array(
			array(
				'key'   => 'field_timeline_url',
				'label' => '発達タイムラインのURL',
				'name'  => 'timeline_url',
				'type'  => 'url',
			),
			array(
				'key'   => 'field_record_sheet_url',
				'label' => '発達記録シートのURL',
				'name'  => 'record_sheet_url',
				'type'  => 'url',
			),
			array(
				'key'          => 'field_ga_id',
				'label'        => 'Google アナリティクスの測定ID',
				'name'         => 'ga_id',
				'type'         => 'text',
				'placeholder'  => 'G-XXXXXXXXXX',
				'instructions' => '入力するとアクセス解析が有効になります（ログイン中の閲覧は計測しません）',
			),
		),
	) );
} );
