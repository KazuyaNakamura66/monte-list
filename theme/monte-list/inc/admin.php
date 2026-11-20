<?php
/**
 * 管理画面の調整。
 */

defined( 'ABSPATH' ) || exit;

// 標準の「投稿」は使わないので非表示にする。
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit.php' );
} );

/** 編集画面で選択するカテゴリー: 記事のカテゴリー → 開いたメニューのカテゴリー → 最初のカテゴリー。 */
function monte_admin_selected_area( WP_Post $post, array $areas ): string {
	$post_area = monte_post_area( $post->ID );
	$selected  = $post_area ? $post_area->slug : monte_admin_request_area();
	return in_array( $selected, wp_list_pluck( $areas, 'slug' ), true ) ? $selected : $areas[0]->slug;
}

/**
 * 編集画面の「カテゴリー」欄。1 つだけ選ぶ形にし、必ずどれかにチェックを入れておく。
 * カテゴリーが決まっていれば、そのカテゴリーだけを表示する。
 * 新規追加では固定し、保存済みの記事は「変更する」で他を選べる。
 */
function monte_area_meta_box( WP_Post $post ): void {
	$areas = monte_top_areas();
	if ( ! $areas ) {
		echo '<p>カテゴリーが登録されていません。</p>';
		return;
	}
	$selected   = monte_admin_selected_area( $post, $areas );
	$is_decided = monte_post_area( $post->ID ) || monte_admin_request_area();
	$is_new     = 'auto-draft' === $post->post_status;
	$can_assign = current_user_can( get_taxonomy( 'area' )->cap->assign_terms );
	if ( $is_decided && $is_new ) {
		$areas = array_values( array_filter( $areas, fn( WP_Term $area ) => $selected === $area->slug ) );
	}
	?>
	<ul>
		<?php foreach ( $areas as $area ) : ?>
			<li<?php echo $is_decided && $selected !== $area->slug ? ' data-area-other hidden' : ''; ?>>
				<label>
					<input type="radio" name="tax_input[area][]" value="<?php echo esc_attr( $area->term_id ); ?>" data-area-slug="<?php echo esc_attr( $area->slug ); ?>"<?php checked( $selected, $area->slug ); ?><?php disabled( ! $can_assign ); ?>>
					<?php echo esc_html( $area->name ); ?>
				</label>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php if ( $is_decided && ! $is_new && $can_assign ) : ?>
		<button class="button-link" type="button" data-area-change>変更する</button>
	<?php endif; ?>
	<?php
}

/** サブカテゴリー・ラベルのチェックボックス（子の段階は親の下に字下げして出す）。 */
function monte_term_checklist( string $taxonomy, array $terms, array $checked, bool $can_assign ): void {
	echo '<ul class="monte-stage-list">';
	foreach ( $terms as $term ) {
		printf(
			'<li><label><input type="checkbox" name="tax_input[%s][]" value="%d"%s%s> %s</label>',
			esc_attr( $taxonomy ),
			(int) $term->term_id,
			checked( in_array( $term->term_id, $checked, true ), true, false ),
			disabled( ! $can_assign, true, false ),
			esc_html( $term->name )
		);
		$children = monte_sorted_terms( array( 'taxonomy' => $taxonomy, 'parent' => $term->term_id ) );
		if ( $children ) {
			monte_term_checklist( $taxonomy, $children, $checked, $can_assign );
		}
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * 編集画面のサブカテゴリー・ラベルの欄。選択中のカテゴリーのものだけを表示する。
 * 他のカテゴリーの欄は無効にしてあるので、保存されない。
 */
function monte_area_terms_meta_box( WP_Post $post, string $taxonomy, callable $terms_for_area ): void {
	static $script_printed = false;

	$areas = monte_top_areas();
	if ( ! $areas ) {
		return;
	}
	$object     = get_taxonomy( $taxonomy );
	$selected   = monte_admin_selected_area( $post, $areas );
	$checked    = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
	$checked    = is_wp_error( $checked ) ? array() : array_map( 'intval', $checked );
	$can_assign = current_user_can( $object->cap->assign_terms );

	printf( '<input type="hidden" name="tax_input[%s][]" value="0">', esc_attr( $taxonomy ) );
	foreach ( $areas as $area ) {
		$terms = $terms_for_area( $area->term_id );
		printf( '<fieldset class="monte-stage-group" data-stage-area="%s" data-required-label="%s"%s>', esc_attr( $area->slug ), esc_attr( $object->labels->name ), $selected === $area->slug ? '' : ' hidden disabled' );
		printf( '<legend class="screen-reader-text">%sの%s</legend>', esc_html( $area->name ), esc_html( $object->labels->name ) );
		if ( $terms ) {
			monte_term_checklist( $taxonomy, $terms, $checked, $can_assign );
		} else {
			printf( '<p>このカテゴリーの%sはありません。</p>', esc_html( $object->labels->name ) );
		}
		echo '</fieldset>';
	}

	if ( $script_printed ) {
		return;
	}
	$script_printed = true;
	?>
	<script>
		(() => {
			const clearError = (group) => group.querySelector(".monte-required-message")?.remove();

			document.addEventListener("click", (e) => {
				const button = e.target.closest?.("[data-area-change]");
				if (!button) return;
				document.querySelectorAll("[data-area-other]").forEach((item) => {
					item.hidden = false;
				});
				button.remove();
			});

			document.addEventListener("change", (e) => {
				if (e.target.closest?.("[data-stage-area]")) clearError(e.target.closest("[data-stage-area]"));
				if (e.target.name !== "tax_input[area][]") return;
				const slug = e.target.dataset.areaSlug;
				document.querySelectorAll("[data-stage-area]").forEach((group) => {
					const on = group.dataset.stageArea === slug;
					group.hidden = !on;
					group.disabled = !on;
					clearError(group);
				});
			});

			// 公開するときだけ、サブカテゴリー・ラベルの選択を確認する（選択肢がないカテゴリーは対象外）
			document.addEventListener("submit", (e) => {
				if (e.target.id !== "post" || e.submitter?.id !== "publish") return;
				const missing = [...document.querySelectorAll("[data-stage-area]:not([hidden])")].filter(
					(group) => group.querySelector("input[type=checkbox]") && !group.querySelector("input[type=checkbox]:checked")
				);
				missing.forEach((group) => {
					clearError(group);
					const message = document.createElement("p");
					message.className = "monte-required-message";
					message.setAttribute("role", "alert");
					message.textContent = `${group.dataset.requiredLabel}を選択してください。`;
					group.prepend(message);
				});
				if (missing.length) {
					e.preventDefault();
					missing[0].closest(".postbox")?.scrollIntoView({ block: "center" });
					missing[0].querySelector("input[type=checkbox]").focus({ preventScroll: true });
				}
			}, true);
		})();
	</script>
	<?php
}

/** 公開に必要なのに未選択の分類名。選択肢がないカテゴリーのサブカテゴリー・ラベルは対象外。 */
function monte_missing_required_terms( int $post_id, array $tax_input ): array {
	$selected = function ( string $taxonomy ) use ( $post_id, $tax_input ): array {
		if ( isset( $tax_input[ $taxonomy ] ) ) {
			return array_values( array_filter( array_map( 'absint', (array) $tax_input[ $taxonomy ] ) ) );
		}
		$ids = $post_id ? wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) ) : array();
		return is_wp_error( $ids ) ? array() : array_map( 'intval', $ids );
	};

	$area = get_term( $selected( 'area' )[0] ?? 0, 'area' );
	if ( ! $area instanceof WP_Term ) {
		return array( get_taxonomy( 'area' )->labels->name );
	}
	$area_id = $area->parent ? $area->parent : $area->term_id;

	$missing = array();
	foreach ( array( 'stage' => monte_area_stages( $area_id ), 'hand_motion' => monte_area_motions( $area_id ) ) as $taxonomy => $terms ) {
		$allowed = array();
		foreach ( $terms as $term ) {
			$allowed = array_merge( $allowed, array( $term->term_id ), get_term_children( $term->term_id, $taxonomy ) );
		}
		if ( $allowed && ! array_intersect( $allowed, $selected( $taxonomy ) ) ) {
			$missing[] = get_taxonomy( $taxonomy )->labels->name;
		}
	}
	return $missing;
}

// 画面の確認を通らずに公開されそうなときは、下書きとして保存する。
add_filter( 'wp_insert_post_data', function ( array $data, array $postarr ): array {
	if ( ! is_admin() || ! in_array( $data['post_type'], MONTE_POST_TYPES, true ) || ! in_array( $data['post_status'], array( 'publish', 'future', 'pending' ), true ) ) {
		return $data;
	}
	$post_id   = (int) ( $postarr['ID'] ?? 0 );
	$tax_input = (array) ( $postarr['tax_input'] ?? array() );

	// クイック編集などで、公開済みの記事を直すだけのときは止めない
	if ( ! isset( $tax_input['area'] ) && in_array( get_post_status( $post_id ), array( 'publish', 'future', 'pending' ), true ) ) {
		return $data;
	}
	$missing = monte_missing_required_terms( $post_id, $tax_input );
	if ( $missing ) {
		$data['post_status'] = 'draft';
		set_transient( 'monte_required_' . get_current_user_id(), $missing, MINUTE_IN_SECONDS );
	}
	return $data;
}, 10, 2 );

// 下書きに戻したときは「公開しました」と表示しない。
add_filter( 'redirect_post_location', function ( string $location ): string {
	return get_transient( 'monte_required_' . get_current_user_id() ) ? add_query_arg( 'message', 10, $location ) : $location;
} );

add_action( 'admin_notices', function () {
	$key     = 'monte_required_' . get_current_user_id();
	$missing = get_transient( $key );
	if ( $missing ) {
		delete_transient( $key );
		printf( '<div class="notice notice-error"><p>%sが未選択のため、公開せず下書きとして保存しました。</p></div>', esc_html( implode( '・', $missing ) ) );
	}
} );

function monte_stage_meta_box( WP_Post $post ): void {
	monte_area_terms_meta_box( $post, 'stage', 'monte_area_stages' );
}

function monte_motion_meta_box( WP_Post $post ): void {
	monte_area_terms_meta_box( $post, 'hand_motion', 'monte_area_motions' );
}

// 左メニューの「サブカテゴリー」「ラベル」は、開いたカテゴリーのものだけを一覧にする。
add_filter( 'get_terms_args', function ( array $args, array $taxonomies ): array {
	static $running = false;
	$taxonomy = array_values( $taxonomies )[0] ?? '';
	if ( $running || 'edit-tags.php' !== ( $GLOBALS['pagenow'] ?? '' ) || 1 !== count( $taxonomies ) || ! in_array( $taxonomy, array( 'stage', 'hand_motion' ), true ) ) {
		return $args;
	}
	$area = monte_admin_top_area( monte_admin_request_area() );
	if ( ! $area ) {
		return $args;
	}
	$running = true;
	$terms   = 'stage' === $taxonomy ? monte_area_stages( $area->term_id ) : monte_area_motions( $area->term_id );
	$ids     = array();
	foreach ( $terms as $term ) {
		$ids = array_merge( $ids, array( $term->term_id ), get_term_children( $term->term_id, $taxonomy ) );
	}
	$running         = false;
	$args['include'] = $ids ?: array( 0 ); // 該当がないカテゴリーは 0 件にする
	return $args;
}, 10, 2 );

// その画面で追加するものは、開いたカテゴリーを「表示するカテゴリー」の初期値にする。
foreach ( array( 'field_stage_area', 'field_motion_area' ) as $monte_field_key ) {
	add_filter( "acf/load_field/key={$monte_field_key}", function ( array $field ): array {
		if ( 'edit-tags.php' === ( $GLOBALS['pagenow'] ?? '' ) ) {
			$area = monte_admin_top_area( monte_admin_request_area() );
			if ( $area ) {
				$field['default_value'] = 'multi_select' === $field['field_type'] ? array( $area->term_id ) : $area->term_id;
			}
		}
		return $field;
	} );
}

// 教具一覧・活動一覧の先頭に写真を表示する。
foreach ( array( 'tool', 'activity' ) as $monte_type ) {
	add_filter( "manage_{$monte_type}_posts_columns", function ( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['monte_thumb'] = '写真';
			}
			$new[ $key ] = $label;
		}
		return $new;
	} );

	add_action( "manage_{$monte_type}_posts_custom_column", function ( string $column, int $post_id ) {
		if ( 'monte_thumb' !== $column ) {
			return;
		}
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, 'thumbnail', array( 'class' => 'monte-thumb', 'alt' => '' ) );
		} else {
			echo '<span class="monte-thumb monte-thumb--empty" aria-label="写真なし"></span>';
		}
	}, 10, 2 );
}

add_action( 'admin_head-edit.php', function () {
	echo '<style>
		.column-monte_thumb { width: 72px; }
		.monte-thumb { display: block; width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
		.monte-thumb--empty { background: #f0ebe6; }
	</style>';
} );
