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
