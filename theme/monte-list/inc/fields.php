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
		array_merge( $list( "{$p}_recommend", 'こんな子におすすめ', '項目を追加' ), array( 'name' => 'recommend' ) ),
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
					'key'          => 'field_activity_overview',
					'label'        => '活動の概要',
					'name'         => 'overview',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => 0,
				),
				array(
					'key'           => 'field_activity_template_file',
					'label'         => '型紙ファイル',
					'name'          => 'template_file',
					'type'          => 'file',
					'return_format' => 'array',
					'instructions'  => '「型紙をダウンロードする」リンクになります（任意）',
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

	// 領域の設定（タブの色）
	acf_add_local_field_group( array(
		'key'      => 'group_area',
		'title'    => '領域の表示設定',
		'location' => array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'area' ) ) ),
		'fields'   => array(
			array(
				'key'          => 'field_area_color',
				'label'        => 'タブの色',
				'name'         => 'area_color',
				'type'         => 'color_picker',
							),
		),
	) );

	// 発達段階の設定（どの領域のタブに出すか）
	acf_add_local_field_group( array(
		'key'      => 'group_stage',
		'title'    => '発達段階の表示設定',
		'location' => array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'stage' ) ) ),
		'fields'   => array(
			array(
				'key'           => 'field_stage_area',
				'label'         => '表示する領域',
				'name'          => 'stage_area',
				'type'          => 'taxonomy',
				'taxonomy'      => 'area',
				'field_type'    => 'select',
				'allow_null'    => 1,
				'add_term'      => 0,
				'save_terms'    => 0,
				'load_terms'    => 0,
				'return_format' => 'id',
				'instructions'  => '一覧のタブ（例：にぎる・つまむ期）として表示する領域。親の発達段階のみ設定してください',
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
