<?php
/**
 * 保存した活動ページ（/saved/）。
 * 保存先はブラウザ（localStorage）なので、ページ側の JS が ID を送り、
 * カードの HTML を REST API から受け取って表示する。
 */

defined( 'ABSPATH' ) || exit;

const MONTE_REWRITE_VERSION = 1;

add_action( 'init', function () {
	add_rewrite_rule( '^saved/?$', 'index.php?monte_saved=1', 'top' );

	// ルールを追加・変更したときだけ一度書き換える。
	if ( (int) get_option( 'monte_rewrite_version' ) < MONTE_REWRITE_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'monte_rewrite_version', MONTE_REWRITE_VERSION );
	}
} );

add_filter( 'query_vars', function ( array $vars ): array {
	$vars[] = 'monte_saved';
	return $vars;
} );

add_filter( 'template_include', function ( string $template ): string {
	if ( get_query_var( 'monte_saved' ) ) {
		return get_template_directory() . '/page-saved.php';
	}
	return $template;
} );

add_filter( 'document_title_parts', function ( array $parts ): array {
	if ( get_query_var( 'monte_saved' ) ) {
		$parts['title'] = '保存した活動';
	}
	return $parts;
} );

function monte_saved_url(): string {
	return home_url( '/saved/' );
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'monte/v1', '/cards', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'args'                => array(
			'ids' => array(
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			),
		),
		'callback'            => function ( WP_REST_Request $request ) {
			$ids = array_slice( array_filter( array_map( 'absint', explode( ',', $request['ids'] ) ) ), 0, 100 );
			if ( ! $ids ) {
				return array( 'html' => '', 'ids' => array() );
			}

			$query = new WP_Query( array(
				'post_type'      => MONTE_POST_TYPES,
				'post_status'    => 'publish',
				'post__in'       => array_reverse( $ids ), // 新しく保存した順
				'orderby'        => 'post__in',
				'posts_per_page' => count( $ids ),
				'no_found_rows'  => true,
			) );

			ob_start();
			while ( $query->have_posts() ) {
				$query->the_post();
				get_template_part( 'template-parts/card' );
			}
			wp_reset_postdata();

			return array(
				'html' => ob_get_clean(),
				'ids'  => array_map( 'strval', wp_list_pluck( $query->posts, 'ID' ) ),
			);
		},
	) );
} );
