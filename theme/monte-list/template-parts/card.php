<?php
/**
 * 一覧カード。ループ内で使う。
 */

$post_id  = get_the_ID();
$tag      = monte_card_tag( $post_id );
$subtitle = monte_card_subtitle( $post_id );
?>
<article class="card">
	<div class="card__media">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'monte-card', array( 'class' => 'card__img', 'alt' => '', 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<div class="card__img card__img--empty"></div>
		<?php endif; ?>

		<?php if ( $tag ) : ?>
			<span class="card__tag"><?php echo esc_html( $tag ); ?></span>
		<?php endif; ?>

		<?php if ( monte_has_video( $post_id ) ) : ?>
			<span class="card__play" aria-label="動画あり"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 7l8 5-8 5z" fill="currentColor"/></svg></span>
		<?php endif; ?>
	</div>

	<div class="card__body">
		<h3 class="card__title"><a class="card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( $subtitle ) : ?>
			<p class="card__subtitle"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>
	</div>

	<button class="bookmark" type="button" data-bookmark="<?php echo esc_attr( $post_id ); ?>" aria-pressed="false" aria-label="保存する">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4h10v16l-5-4-5 4z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
	</button>
</article>
