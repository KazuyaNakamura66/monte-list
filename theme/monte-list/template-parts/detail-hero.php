<?php
/**
 * 詳細ページ上部（パンくず・メイン画像・タイトル・目安・おすすめ）。教具・活動共通。
 */

$post_id = get_the_ID();
$top     = monte_post_area( $post_id );
$motion  = monte_post_term( $post_id, 'hand_motion', false );
$tab     = monte_post_tab( $post_id ); // 開いたタブ（なければ最初に当てはまるタブ）

// タブの下の階層にも登録されていれば、その一番下の階層まで表示する。
$tab_path = $tab ? array( $tab ) : array();
if ( $tab ) {
	foreach ( wp_get_post_terms( $post_id, 'stage' ) as $term ) {
		$path = array_reverse( array_map( fn( $id ) => get_term( $id, 'stage' ), get_ancestors( $term->term_id, 'stage', 'taxonomy' ) ) );
		$path[] = $term;
		if ( $path[0]->term_id === $tab->term_id && count( $path ) > count( $tab_path ) ) {
			$tab_path = $path;
		}
	}
}

// 入手方法は登録順（購入 → レンタル → 手作り）で並べる。
$acquire   = wp_get_post_terms( $post_id, 'acquire', array( 'orderby' => 'term_id' ) );
$acquire   = is_wp_error( $acquire ) ? array() : $acquire;
$catch     = get_field( 'catch' );
$lead      = get_field( 'lead' );
$guideline = get_field( 'guideline' );
$recommend = monte_list_items( get_field( 'recommend' ) );

$crumbs = array_filter( array_merge( array( $top ), $tab_path ) );

// サブカテゴリー（その下の階層も）は、カテゴリー一覧の開いたタブへ。
$top_url   = $top ? get_term_link( $top ) : '';
$crumb_url = function ( WP_Term $crumb ) use ( $top_url, $tab ): string {
	if ( 'stage' === $crumb->taxonomy && $top_url && ! is_wp_error( $top_url ) ) {
		return monte_with_tab( $top_url, $tab );
	}
	$link = get_term_link( $crumb );
	return is_wp_error( $link ) ? '' : $link;
};
?>
<div class="container">
	<?php if ( $crumbs ) : ?>
		<nav class="breadcrumb" aria-label="パンくずリスト">
			<ol>
				<?php foreach ( $crumbs as $crumb ) : ?>
					<li><a href="<?php echo esc_url( $crumb_url( $crumb ) ); ?>"><?php echo esc_html( $crumb->name ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		</nav>
	<?php endif; ?>

	<div class="detail-hero">
		<div class="detail-hero__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'monte-main', array( 'class' => 'detail-hero__img', 'alt' => '' ) ); ?>
			<?php else : ?>
				<div class="detail-hero__img detail-hero__img--empty"></div>
			<?php endif; ?>
		</div>

		<div class="detail-hero__info">
			<div class="detail-hero__top">
				<?php if ( $top || $motion ) : ?>
					<div class="pills">
						<?php foreach ( array_filter( array( $top, $motion ) ) as $term ) : ?>
							<span class="pill"><?php echo esc_html( $term->name ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<span></span>
				<?php endif; ?>
				<button class="bookmark bookmark--static" type="button" data-bookmark="<?php echo esc_attr( $post_id ); ?>" aria-pressed="false" aria-label="保存する">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4h10v16l-5-4-5 4z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
				</button>
			</div>

			<h1 class="detail-hero__title">
				<?php the_title(); ?>
				<?php foreach ( $acquire as $term ) : ?>
					<span class="label"><?php echo esc_html( $term->name ); ?></span>
				<?php endforeach; ?>
			</h1>

			<?php if ( $catch ) : ?>
				<p class="detail-hero__catch"><?php echo esc_html( $catch ); ?></p>
			<?php endif; ?>
			<?php if ( $lead ) : ?>
				<p class="detail-hero__lead"><?php echo wp_kses_post( $lead ); ?></p>
			<?php endif; ?>

			<?php if ( $guideline ) : ?>
				<dl class="detail-hero__row">
					<dt>取り入れる目安</dt>
					<dd><?php echo esc_html( $guideline ); ?></dd>
				</dl>
			<?php endif; ?>

			<?php if ( $recommend ) : ?>
				<dl class="detail-hero__row">
					<dt>こんな姿がみられたら</dt>
					<dd>
						<ul class="dot-list">
							<?php foreach ( $recommend as $item ) : ?>
								<li><?php echo esc_html( $item ); ?></li>
							<?php endforeach; ?>
						</ul>
					</dd>
				</dl>
			<?php endif; ?>
		</div>
	</div>
</div>
