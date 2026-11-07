<?php
/**
 * 活動の詳細ページ（例: ボール落とし）。
 */

get_header();

while ( have_posts() ) :
	the_post();

	$overview     = get_field( 'overview' );
	$template     = get_field( 'template_file' );
	$materials    = get_field( 'materials' ) ?: array();
	$mat_image    = get_field( 'materials_image' );
	$presentation = get_field( 'presentation' );
	$video_url    = get_field( 'video_url' );
	$levelup      = monte_list_items( get_field( 'levelup' ) );
	$related      = get_field( 'related' ) ?: array();
	?>
	<main class="detail">
		<?php get_template_part( 'template-parts/detail-hero' ); ?>

		<div class="container">
			<div class="detail-body">
				<?php if ( $overview ) : ?>
					<section class="detail-section">
						<h2 class="detail-section__label">活動の概要</h2>
						<div class="detail-section__content prose"><?php echo wp_kses_post( $overview ); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $template || $materials || $mat_image ) : ?>
					<section class="detail-section">
						<h2 class="detail-section__label">用意するもの</h2>
						<div class="detail-section__content">
							<?php if ( $template ) : ?>
								<p class="download">
									<a href="<?php echo esc_url( $template['url'] ); ?>" download>型紙をダウンロードする</a>
								</p>
							<?php endif; ?>

							<div class="materials<?php echo $mat_image ? ' has-image' : ''; ?>">
								<?php if ( $materials ) : ?>
									<ul class="materials__list">
										<?php foreach ( $materials as $m ) : ?>
											<li>
												<?php echo esc_html( $m['name'] ); ?>
												<?php if ( ! empty( $m['url'] ) ) : ?>
													<a class="materials__buy" href="<?php echo esc_url( $m['url'] ); ?>" target="_blank" rel="noopener">購入先<span aria-hidden="true"> ↗</span></a>
												<?php endif; ?>
												<?php if ( ! empty( $m['note'] ) ) : ?>
													<ul><li><?php echo esc_html( $m['note'] ); ?></li></ul>
												<?php endif; ?>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
								<?php if ( $mat_image ) : ?>
									<div class="materials__image"><?php echo wp_get_attachment_image( $mat_image, 'monte-card', false, array( 'alt' => '' ) ); ?></div>
								<?php endif; ?>
							</div>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( $presentation || $video_url ) : ?>
					<section class="detail-section">
						<h2 class="detail-section__label">提示方法</h2>
						<div class="detail-section__content">
							<?php if ( $presentation ) : ?>
								<div class="prose"><?php echo wp_kses_post( $presentation ); ?></div>
							<?php endif; ?>
							<?php monte_video( $video_url ); ?>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( $levelup ) : ?>
					<section class="detail-section">
						<h2 class="detail-section__label">難易度の上げ方</h2>
						<div class="detail-section__content">
							<ul class="plain-list">
								<?php foreach ( $levelup as $item ) : ?>
									<li><?php echo esc_html( $item ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( $related ) : ?>
					<section class="related">
						<h2 class="related__title">関連する活動</h2>
						<div class="card-grid card-grid--small">
							<?php
							global $post;
							foreach ( $related as $related_id ) {
								$post = get_post( $related_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
								setup_postdata( $post );
								get_template_part( 'template-parts/card' );
							}
							wp_reset_postdata();
							?>
						</div>
					</section>
				<?php endif; ?>

				<?php get_template_part( 'template-parts/detail-nav' ); ?>
			</div>
		</div>
	</main>
	<?php
endwhile;

get_footer();
