<?php
/**
 * Monte List theme setup.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/inc/post-types.php';
require_once __DIR__ . '/inc/fields.php';
require_once __DIR__ . '/inc/default-terms.php';
require_once __DIR__ . '/inc/noindex.php';
require_once __DIR__ . '/inc/admin.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/icons.php';
require_once __DIR__ . '/inc/assets.php';
require_once __DIR__ . '/inc/saved.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
} );
