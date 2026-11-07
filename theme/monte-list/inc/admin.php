<?php
/**
 * 管理画面の調整。
 */

defined( 'ABSPATH' ) || exit;

// 標準の「投稿」は使わないので非表示にする。
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit.php' );
} );
