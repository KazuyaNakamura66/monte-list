<?php
/**
 * 関連する活動（管理画面で選んだ最大3件）。教具・活動共通。
 */

$related = get_field( 'related' ) ?: array();
if ( ! $related ) {
	return;
}
?>
<section class="related">
	<h2 class="related__title">関連する活動</h2>
	<div class="card-grid card-grid--small">
		<?php
		global $post;
		foreach ( $related as $related_id ) {
			$post = get_post( $related_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			if ( ! $post || 'publish' !== $post->post_status ) {
				continue;
			}
			setup_postdata( $post );
			get_template_part( 'template-parts/card' );
		}
		wp_reset_postdata();
		?>
	</div>
</section>
