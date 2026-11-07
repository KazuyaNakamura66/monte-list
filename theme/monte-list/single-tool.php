<?php
/**
 * 教具の詳細ページ（例: ピンクタワー）。
 */

get_header();

while ( have_posts() ) :
	the_post();

	$aim_direct   = get_field( 'aim_direct' );
	$aim_indirect = get_field( 'aim_indirect' );
	$overview     = get_field( 'overview' );
	$abilities    = monte_list_items( get_field( 'abilities' ) );
	$video_url    = get_field( 'video_url' );
	$steps        = get_field( 'steps' ) ?: array();
	?>
	<main class="detail">
		<?php get_template_part( 'template-parts/detail-hero' ); ?>

		<div class="container">
			<div class="detail-body">
				<?php if ( $aim_direct || $aim_indirect || $overview ) : ?>
					<section class="detail-section">
						<h2 class="detail-section__label">教具の概要</h2>
						<div class="detail-section__content">
							<?php if ( $aim_direct || $aim_indirect ) : ?>
								<div class="aims">
									<?php if ( $aim_direct ) : ?>
										<div class="aim">
											<span class="aim__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="13" r="7" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="11" cy="13" r="3.5" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M11 13l8-8m-3 0h3v3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
											<div class="aim__text"><span class="aim__label">直接目的</span><?php echo esc_html( $aim_direct ); ?></div>
										</div>
									<?php endif; ?>
									<?php if ( $aim_indirect ) : ?>
										<div class="aim">
											<span class="aim__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21v-9m0 0c0-4-3-6-8-6 0 4 3 6 8 6zm0 0c0-4 3-6 8-6 0 4-3 6-8 6z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg></span>
											<div class="aim__text"><span class="aim__label">間接目的</span><?php echo esc_html( $aim_indirect ); ?></div>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<?php if ( $overview ) : ?>
								<div class="prose"><?php echo wp_kses_post( $overview ); ?></div>
							<?php endif; ?>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( $abilities ) : ?>
					<section class="detail-section">
						<h2 class="detail-section__label">育つ力</h2>
						<div class="detail-section__content">
							<ul class="bullet-list">
								<?php foreach ( $abilities as $item ) : ?>
									<li><?php echo esc_html( $item ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( $video_url ) : ?>
					<section class="detail-section">
						<h2 class="detail-section__label">提示方法</h2>
						<div class="detail-section__content"><?php monte_video( $video_url ); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $steps ) : ?>
					<section class="detail-section">
						<h2 class="detail-section__label">活用方法</h2>
						<div class="detail-section__content">
							<?php foreach ( $steps as $step ) : ?>
								<div class="step">
									<?php if ( ! empty( $step['heading'] ) ) : ?>
										<h3 class="step__heading"><?php echo esc_html( $step['heading'] ); ?></h3>
									<?php endif; ?>
									<?php if ( ! empty( $step['images'] ) ) : ?>
										<div class="step__images">
											<?php foreach ( $step['images'] as $image_id ) : ?>
												<?php echo wp_get_attachment_image( $image_id, 'medium_large', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
									<?php if ( ! empty( $step['body'] ) ) : ?>
										<div class="prose"><?php echo wp_kses_post( $step['body'] ); ?></div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
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
