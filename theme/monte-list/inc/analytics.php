<?php
/**
 * Google アナリティクス。
 * 管理画面「サイト設定」に測定ID（G-から始まる）を入れたときだけ読み込む。
 * ログイン中（管理者・編集者）の閲覧は計測しない。
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	if ( is_user_logged_in() ) {
		return;
	}
	$id = strtoupper( trim( monte_option( 'ga_id' ) ) );
	if ( ! preg_match( '/^G-[A-Z0-9]+$/', $id ) ) {
		return;
	}
	?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $id ); ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', <?php echo wp_json_encode( $id ); ?>);
</script>
	<?php
}, 1 );
