<?php
/**
 * 検索エンジンにインデックスさせない。
 * WP の「表示設定」に関係なく、常に noindex を出す。
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'wp_robots', function ( array $robots ): array {
	$robots['noindex']  = true;
	$robots['nofollow'] = true;
	unset( $robots['max-image-preview'] );
	return $robots;
} );

add_action( 'send_headers', function () {
	header( 'X-Robots-Tag: noindex, nofollow', true );
} );
