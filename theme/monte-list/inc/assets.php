<?php
/**
 * CSS / JS / フォントの読み込み。
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	wp_enqueue_style(
		'monte-fonts',
		'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Cormorant+Garamond:wght@500&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'monte-main', "{$uri}/assets/css/main.css", array( 'monte-fonts' ), filemtime( "{$dir}/assets/css/main.css" ) );
	wp_enqueue_script( 'monte-main', "{$uri}/assets/js/main.js", array(), filemtime( "{$dir}/assets/js/main.js" ), array( 'strategy' => 'defer' ) );
} );

add_filter( 'wp_resource_hints', function ( array $urls, string $type ): array {
	if ( 'preconnect' === $type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );
