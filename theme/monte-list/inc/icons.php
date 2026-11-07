<?php
/**
 * 線画アイコン（インライン SVG）。
 */

defined( 'ABSPATH' ) || exit;

function monte_icon( string $name, string $class = 'icon' ): void {
	$paths = array(
		'filter'   => '<path d="M3 6h5m5 0h8M3 12h11m5 0h2M3 18h3m5 0h10"/><circle cx="10.5" cy="6" r="2.2"/><circle cx="16.5" cy="12" r="2.2"/><circle cx="8.5" cy="18" r="2.2"/>',
		'close'    => '<path d="M6 6l12 12M18 6L6 18"/>',
		'chevron'  => '<path d="M7 10l5 5 5-5"/>',
		'hand'     => '<path d="M8 13V6.5a1.5 1.5 0 013 0V12m0-6.5V4.5a1.5 1.5 0 013 0V12m0-6a1.5 1.5 0 013 0v6m0-3.5a1.5 1.5 0 013 0V15a6 6 0 01-6 6h-1.5a6 6 0 01-4.6-2.2L4.6 15a1.5 1.5 0 012.3-1.9L8 14.5"/>',
		'basket'   => '<path d="M3 10h18l-1.6 9a2 2 0 01-2 1.6H6.6a2 2 0 01-2-1.6zM7.5 10L11 4m5.5 6L13 4M9 14v3m3-3v3m3-3v3"/>',
		'play'     => '<circle cx="12" cy="12" r="9"/><path d="M10 8.5l5 3.5-5 3.5z"/>',
		'play-off' => '<circle cx="12" cy="12" r="9"/><path d="M10 8.5l5 3.5-5 3.5zM5.5 5.5l13 13"/>',
		'scissors' => '<circle cx="6" cy="6" r="2.5"/><circle cx="6" cy="18" r="2.5"/><path d="M8 7.5L20 18M8 16.5L20 6"/>',
		'bag'      => '<path d="M5 8h14l-1 12H6zM9 8V6.5a3 3 0 016 0V8"/>',
		'house'    => '<path d="M4 11l8-7 8 7M6 9.5V20h12V9.5M10 20v-5h4v5"/>',
		'rental'   => '<path d="M4 12a8 8 0 0113.7-5.7L20 8.5M20 4v4.5h-4.5M20 12a8 8 0 01-13.7 5.7L4 15.5M4 20v-4.5h4.5"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return;
	}
	printf(
		'<svg class="%s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
		esc_attr( $class ),
		$paths[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput -- 固定の SVG
	);
}

/** 入手方法のスラッグ → 標準アイコン名。 */
function monte_acquire_icon_name( string $slug ): string {
	$map = array(
		'handmade'   => 'scissors',
		'buy'        => 'bag',
		'substitute' => 'house',
		'rental'     => 'rental',
	);
	return $map[ $slug ] ?? '';
}
