<?php
/**
 * 管理画面のサイドメニュー: カテゴリー > 教具・活動 > 一覧・追加・分類 の形で表示する。
 * 標準の「教具」「活動」メニューは残し、メニューを折りたたんだ幅のときだけ使う。
 */

defined( 'ABSPATH' ) || exit;

const MONTE_ADMIN_TAXONOMIES = array( 'stage', 'hand_motion', 'acquire' );

// カテゴリーのアイコン（Dashicons）。ここにないカテゴリーは既定のアイコンになる。
const MONTE_ADMIN_AREA_ICONS = array(
	'fine-motor'     => 'dashicons-thumbs-up',
	'gross-motor'    => 'dashicons-universal-access',
	'practical-life' => 'dashicons-admin-home',
	'sensorial'      => 'dashicons-visibility',
	'language'       => 'dashicons-format-chat',
	'math'           => 'dashicons-calculator',
	'culture'        => 'dashicons-admin-site-alt3',
);

function monte_admin_nav_enabled(): bool {
	return current_user_can( 'edit_posts' ) && monte_top_areas();
}

/** slug が大分類なら term を返す。 */
function monte_admin_top_area( string $slug ): ?WP_Term {
	$term = $slug ? get_term_by( 'slug', $slug, 'area' ) : null;
	return $term && ! $term->parent ? $term : null;
}

/** URL（なければ移動元の URL）から、操作中のカテゴリーを読む。 */
function monte_admin_request_area(): string {
	// phpcs:disable WordPress.Security.NonceVerification
	$slug = sanitize_title( wp_unslash( $_GET['monte_area'] ?? '' ) );
	if ( ! $slug && 'edit.php' === $GLOBALS['pagenow'] ) {
		$slug = sanitize_title( wp_unslash( $_GET['area'] ?? '' ) );
	}
	if ( ! $slug ) {
		$referer = wp_unslash( $_GET['wp_http_referer'] ?? '' ) ?: (string) wp_get_referer();
		wp_parse_str( (string) wp_parse_url( $referer, PHP_URL_QUERY ), $query );
		$slug = sanitize_title( $query['monte_area'] ?? '' );
	}
	// phpcs:enable WordPress.Security.NonceVerification
	return monte_admin_top_area( $slug ) ? $slug : '';
}

/** カテゴリーの「すべて」（教具と活動をまとめた一覧）を表示中か。画面は活動一覧を土台にする。 */
function monte_admin_is_all_list(): bool {
	// phpcs:ignore WordPress.Security.NonceVerification
	return 'edit.php' === ( $GLOBALS['pagenow'] ?? '' ) && ! empty( $_GET['monte_all'] ) && 'activity' === ( $_GET['post_type'] ?? '' );
}

/** 表示中の画面が、メニューのどこにあたるか。 */
function monte_admin_nav_current(): array {
	global $pagenow;
	// phpcs:disable WordPress.Security.NonceVerification
	$type = sanitize_key( $_GET['post_type'] ?? '' );
	$area = monte_admin_request_area();
	$page = '';

	if ( 'edit.php' === $pagenow ) {
		$page = 'list';
	} elseif ( 'post-new.php' === $pagenow ) {
		$page = 'new';
	} elseif ( 'post.php' === $pagenow ) {
		$post = get_post( absint( $_GET['post'] ?? 0 ) );
		if ( $post ) {
			$page     = 'list';
			$type     = $post->post_type;
			$top_area = in_array( $type, MONTE_POST_TYPES, true ) ? monte_post_area( $post->ID ) : null;
			$area     = $top_area ? $top_area->slug : $area;
		}
	} elseif ( in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) ) {
		$page = sanitize_key( $_GET['taxonomy'] ?? '' );
	}
	// phpcs:enable WordPress.Security.NonceVerification

	return in_array( $type, MONTE_POST_TYPES, true )
		? array( 'area' => $area, 'type' => $type, 'page' => $page )
		: array( 'area' => '', 'type' => '', 'page' => '' );
}

/** 記事の種類ごとのメニュー項目。 */
function monte_admin_nav_links( string $type, string $area ): array {
	$object = get_post_type_object( $type );
	$links  = array();

	if ( current_user_can( $object->cap->edit_posts ) ) {
		$links['list'] = array( $object->labels->all_items, add_query_arg( array( 'post_type' => $type, 'area' => $area ), admin_url( 'edit.php' ) ) );
	}
	if ( current_user_can( $object->cap->create_posts ) ) {
		$links['new'] = array( $object->labels->add_new_item, add_query_arg( array( 'post_type' => $type, 'monte_area' => $area ), admin_url( 'post-new.php' ) ) );
	}
	foreach ( MONTE_ADMIN_TAXONOMIES as $taxonomy ) {
		$tax = get_taxonomy( $taxonomy );
		if ( current_user_can( $tax->cap->manage_terms ) ) {
			$links[ $taxonomy ] = array( $tax->labels->name, add_query_arg( array( 'taxonomy' => $taxonomy, 'post_type' => $type, 'monte_area' => $area ), admin_url( 'edit-tags.php' ) ) );
		}
	}
	return $links;
}

add_action( 'adminmenu', function () {
	if ( ! monte_admin_nav_enabled() ) {
		return;
	}
	$current = monte_admin_nav_current();
	$is_all  = monte_admin_is_all_list();
	?>
	<li class="monte-nav" id="monte-nav">
		<ul class="monte-nav__areas">
			<?php foreach ( monte_top_areas() as $area ) : ?>
				<?php
				$is_open = $current['area'] === $area->slug;
				$list_id = 'monte-nav-' . $area->slug;
				?>
				<li class="monte-nav__area" data-area="<?php echo esc_attr( $area->slug ); ?>">
					<button class="monte-nav__toggle" type="button" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $list_id ); ?>">
						<span class="dashicons <?php echo esc_attr( MONTE_ADMIN_AREA_ICONS[ $area->slug ] ?? 'dashicons-category' ); ?>" aria-hidden="true"></span>
						<?php echo esc_html( $area->name ); ?>
					</button>
					<ul class="monte-nav__types" id="<?php echo esc_attr( $list_id ); ?>"<?php echo $is_open ? '' : ' hidden'; ?>>
						<li class="monte-nav__type<?php echo $is_open && $is_all ? ' is-current' : ''; ?>">
							<a class="monte-nav__type-link" href="<?php echo esc_url( add_query_arg( array( 'post_type' => 'activity', 'area' => $area->slug, 'monte_all' => 1 ), admin_url( 'edit.php' ) ) ); ?>">
								<span class="dashicons dashicons-list-view" aria-hidden="true"></span>
								すべて
							</a>
						</li>
						<?php foreach ( array( 'tool', 'activity' ) as $type ) : ?>
							<?php
							$object     = get_post_type_object( $type );
							$links      = monte_admin_nav_links( $type, $area->slug );
							$is_current = $is_open && ! $is_all && $current['type'] === $type;
							if ( empty( $links['list'] ) ) {
								continue;
							}
							?>
							<li class="monte-nav__type<?php echo $is_current ? ' is-current' : ''; ?>">
								<a class="monte-nav__type-link" href="<?php echo esc_url( $links['list'][1] ); ?>">
									<span class="dashicons <?php echo esc_attr( $object->menu_icon ); ?>" aria-hidden="true"></span>
									<?php echo esc_html( $object->labels->menu_name ); ?>
								</a>
								<?php if ( $is_current ) : ?>
									<ul class="monte-nav__sub">
										<?php foreach ( $links as $key => $link ) : ?>
											<li><a href="<?php echo esc_url( $link[1] ); ?>"<?php echo $current['page'] === $key ? ' class="is-current" aria-current="page"' : ''; ?>><?php echo esc_html( $link[0] ); ?></a></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
		<script>
			(() => {
				const KEY = "monte-admin-open-areas";
				const nav = document.getElementById("monte-nav");
				document.getElementById("menu-dashboard")?.after(nav);

				const areas = [...nav.querySelectorAll(".monte-nav__area")];
				const parts = (area) => [area.querySelector(".monte-nav__toggle"), area.querySelector(".monte-nav__types")];
				const setOpen = (area, open) => {
					const [toggle, list] = parts(area);
					toggle.setAttribute("aria-expanded", String(open));
					list.hidden = !open;
				};

				let saved = [];
				try {
					saved = JSON.parse(localStorage.getItem(KEY) || "[]");
				} catch {}
				if (Array.isArray(saved)) {
					areas.filter((area) => saved.includes(area.dataset.area)).forEach((area) => setOpen(area, true));
				}

				nav.addEventListener("click", (e) => {
					const toggle = e.target.closest(".monte-nav__toggle");
					if (!toggle) return;
					setOpen(toggle.parentElement, toggle.getAttribute("aria-expanded") !== "true");
					const open = areas.filter((area) => !parts(area)[1].hidden).map((area) => area.dataset.area);
					try {
						localStorage.setItem(KEY, JSON.stringify(open));
					} catch {}
				});
			})();
		</script>
	</li>
	<?php
} );

add_filter( 'admin_body_class', function ( string $classes ): string {
	$classes .= monte_admin_nav_enabled() ? ' monte-has-nav' : '';
	return $classes . ( monte_admin_is_all_list() ? ' monte-all-list' : '' );
} );

add_action( 'admin_enqueue_scripts', function () {
	$dir = get_template_directory();
	wp_enqueue_style( 'monte-admin', get_template_directory_uri() . '/assets/css/admin.css', array(), filemtime( "{$dir}/assets/css/admin.css" ) );
} );

// カテゴリーで絞った一覧は、見出しにもカテゴリー名を出す（例: 微細運動の活動）。
add_action( 'load-edit.php', function () {
	$current = monte_admin_nav_current();
	$area    = monte_admin_top_area( $current['area'] );
	$is_all  = monte_admin_is_all_list();
	if ( ! $area && ! $is_all ) {
		return;
	}
	$labels       = get_post_type_object( $current['type'] )->labels;
	$name         = $is_all ? 'すべて' : $labels->name;
	$labels->name = $area ? "{$area->name}の{$name}" : $name;
	if ( $is_all ) {
		$labels->search_items = '記事を検索'; // 教具も検索対象になる
	}
} );

// ---------- カテゴリーの「すべて」 ----------
add_action( 'pre_get_posts', function ( WP_Query $query ) {
	if ( is_admin() && $query->is_main_query() && monte_admin_is_all_list() ) {
		$query->set( 'post_type', MONTE_POST_TYPES );
	}
} );

// 検索後は WordPress が $post_type に配列を入れるので、画面用に活動へ戻す（edit.php が文字列として使う）。
add_action( 'wp', function () {
	if ( is_admin() && monte_admin_is_all_list() ) {
		$GLOBALS['post_type'] = 'activity'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	}
} );

// 列: 写真の前に「種類」を足し、「入手方法」は出さない。
add_filter( 'manage_activity_posts_columns', function ( array $columns ): array {
	if ( ! monte_admin_is_all_list() ) {
		return $columns;
	}
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'monte_thumb' === $key ) {
			$new['monte_type'] = '種類';
		}
		if ( 'taxonomy-acquire' !== $key ) {
			$new[ $key ] = $label;
		}
	}
	return $new;
}, 20 );

foreach ( array( 'tool', 'activity' ) as $monte_type ) { // helpers.php より先に読み込まれるので定数は使わない
	add_action( "manage_{$monte_type}_posts_custom_column", function ( string $column, int $post_id ) {
		if ( 'monte_type' === $column ) {
			echo esc_html( get_post_type_object( get_post_type( $post_id ) )->labels->singular_name );
		}
	}, 10, 2 );
}

// 検索・絞り込みをしても「すべて」のカテゴリー一覧にとどまる。
add_action( 'restrict_manage_posts', function () {
	if ( ! monte_admin_is_all_list() ) {
		return;
	}
	echo '<input type="hidden" name="monte_all" value="1">';
	$area = monte_admin_request_area();
	if ( $area ) {
		printf( '<input type="hidden" name="area" value="%s">', esc_attr( $area ) );
	}
} );

/** カテゴリー内の記事数。 */
function monte_admin_area_count( string $type, WP_Term $area, array $statuses, int $author = 0 ): int {
	$query = new WP_Query( array(
		'post_type'      => $type,
		'post_status'    => $statuses,
		'author'         => $author,
		'fields'         => 'ids',
		'posts_per_page' => 1,
		'tax_query'      => array( array( 'taxonomy' => 'area', 'field' => 'term_id', 'terms' => $area->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	return (int) $query->found_posts;
}

// カテゴリーで絞った一覧の「すべて・公開済み…」は、そのカテゴリーの件数にし、リンク先も絞ったままにする。
foreach ( array( 'tool', 'activity' ) as $monte_type ) {
	add_filter( "views_edit-{$monte_type}", function ( array $views ) use ( $monte_type ): array {
		// phpcs:disable WordPress.Security.NonceVerification
		$area    = monte_admin_top_area( sanitize_title( wp_unslash( $_GET['area'] ?? '' ) ) );
		$current = sanitize_key( $_GET['post_status'] ?? '' );
		$is_mine = ! empty( $_GET['author'] ) && get_current_user_id() === (int) $_GET['author'];
		// phpcs:enable WordPress.Security.NonceVerification
		if ( ! $area || monte_admin_is_all_list() ) {
			return $views;
		}
		$all_statuses = array_values( get_post_stati( array( 'show_in_admin_all_list' => true ) ) );

		foreach ( $views as $key => $html ) {
			$statuses = in_array( $key, array( 'all', 'mine' ), true ) ? $all_statuses : array( $key );
			$count    = monte_admin_area_count( $monte_type, $area, $statuses, 'mine' === $key ? get_current_user_id() : 0 );
			if ( ! $count && 'all' !== $key ) {
				unset( $views[ $key ] );
				continue;
			}
			$is_current = 'mine' === $key ? $is_mine : ( ! $is_mine && ( 'all' === $key ? in_array( $current, array( '', 'all' ), true ) : $current === $key ) );

			$html = preg_replace( '/ class="current"| aria-current="page"/', '', $html );
			$html = preg_replace( '/<span class="count">.*?<\/span>/', '<span class="count">(' . number_format_i18n( $count ) . ')</span>', $html );
			$html = preg_replace_callback(
				'/<a href="([^"]*)"/',
				fn( array $m ) => '<a href="' . esc_url( add_query_arg( 'area', $area->slug, html_entity_decode( $m[1] ) ) ) . '"' . ( $is_current ? ' class="current" aria-current="page"' : '' ),
				$html
			);
			$views[ $key ] = $html;
		}
		return $views;
	}, 20 );
}

// 活動の件数だけを数える表示や、活動用の一括操作は「すべて」では使わない。
add_filter( 'views_edit-activity', fn( array $views ): array => monte_admin_is_all_list() ? array() : $views );
add_filter( 'bulk_actions-edit-activity', function ( array $actions ): array {
	if ( monte_admin_is_all_list() ) {
		unset( $actions['edit'] );
	}
	return $actions;
} );
add_filter( 'post_row_actions', function ( array $actions ): array {
	if ( monte_admin_is_all_list() ) {
		unset( $actions['inline hide-if-no-js'] );
	}
	return $actions;
} );

// 一覧・編集画面の「追加」ボタンからも、表示中のカテゴリーを引き継ぐ。
foreach ( array( 'load-edit.php', 'load-post.php' ) as $monte_hook ) {
	add_action( $monte_hook, function () {
		$current = monte_admin_nav_current();
		if ( ! $current['area'] ) {
			return;
		}
		add_filter( 'admin_url', function ( string $url, string $path ) use ( $current ): string {
			return "post-new.php?post_type={$current['type']}" === $path
				? add_query_arg( 'monte_area', $current['area'], $url )
				: $url;
		}, 10, 2 );
	} );
}

// カテゴリーのメニューから追加したときは、そのカテゴリーにチェックを入れておく。
add_action( 'wp_insert_post', function ( int $post_id, WP_Post $post, bool $update ) {
	if ( $update || 'post-new.php' !== ( $GLOBALS['pagenow'] ?? '' ) || 'auto-draft' !== $post->post_status || ! in_array( $post->post_type, MONTE_POST_TYPES, true ) ) {
		return;
	}
	$area = monte_admin_top_area( monte_admin_request_area() );
	if ( $area ) {
		wp_set_object_terms( $post_id, $area->term_id, 'area' );
	}
}, 10, 3 );
