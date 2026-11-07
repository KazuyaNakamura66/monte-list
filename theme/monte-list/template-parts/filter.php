<?php
/**
 * 絞り込みダイアログ。
 * 送信すると GET パラメータ（hand[] / source[] / video）付きで一覧を再表示する。
 *
 * @var array $args { base_url: string }
 */

$f       = monte_filters();
$motions = monte_sorted_terms( array( 'taxonomy' => 'hand_motion', 'parent' => 0 ) );
$sources = monte_sorted_terms( array( 'taxonomy' => 'acquire', 'parent' => 0 ) );

$visible_motions = array_slice( $motions, 0, MONTE_MOTION_VISIBLE );
$more_motions    = array_slice( $motions, MONTE_MOTION_VISIBLE );
$more_selected   = (bool) array_intersect( wp_list_pluck( $more_motions, 'slug' ), $f['hand'] );

$chip = function ( WP_Term $term, string $name, array $selected ) {
	?>
	<label class="chip">
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( in_array( $term->slug, $selected, true ) ); ?>>
		<span class="chip__face"><?php echo esc_html( $term->name ); ?></span>
	</label>
	<?php
};
?>
<dialog class="filter-dialog" data-filter-dialog aria-labelledby="filter-title">
	<form class="filter" method="get" action="<?php echo esc_url( $args['base_url'] ); ?>" data-filter-form>
		<?php if ( $f['period'] ) : ?>
			<input type="hidden" name="period" value="<?php echo esc_attr( $f['period'] ); ?>">
		<?php endif; ?>
		<?php if ( get_search_query() ) : ?>
			<input type="hidden" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
		<?php endif; ?>

		<div class="filter__header">
			<h2 class="filter__title" id="filter-title"><?php monte_icon( 'filter' ); ?>絞り込み</h2>
			<button class="filter__close" type="button" data-filter-cancel aria-label="閉じる"><?php monte_icon( 'close' ); ?></button>
		</div>

		<?php if ( $motions ) : ?>
			<fieldset class="filter__group" data-chip-group>
				<legend class="filter__legend"><?php monte_icon( 'hand' ); ?>手の動き</legend>
				<div class="chips">
					<button class="chip chip--all" type="button" data-chip-all>すべて</button>
					<?php foreach ( $visible_motions as $term ) : ?>
						<?php $chip( $term, 'hand', $f['hand'] ); ?>
					<?php endforeach; ?>
					<?php if ( $more_motions ) : ?>
						<button class="chip chip--more" type="button" data-chip-more aria-expanded="<?php echo $more_selected ? 'true' : 'false'; ?>">
							<span class="chip__face">その他<?php monte_icon( 'chevron', 'chip__chevron' ); ?></span>
						</button>
						<div class="chips__more" data-chip-more-list <?php echo $more_selected ? '' : 'hidden'; ?>>
							<?php foreach ( $more_motions as $term ) : ?>
								<?php $chip( $term, 'hand', $f['hand'] ); ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</fieldset>
		<?php endif; ?>

		<?php if ( $sources ) : ?>
			<fieldset class="filter__group" data-chip-group>
				<legend class="filter__legend"><?php monte_icon( 'basket' ); ?>入手方法</legend>
				<div class="chips">
					<button class="chip chip--all" type="button" data-chip-all>すべて</button>
					<?php foreach ( $sources as $term ) : ?>
						<?php $chip( $term, 'source', $f['source'] ); ?>
					<?php endforeach; ?>
				</div>
			</fieldset>
		<?php endif; ?>

		<fieldset class="filter__group">
			<legend class="filter__legend"><?php monte_icon( 'play' ); ?>提示動画</legend>
			<div class="chips">
				<label class="chip">
					<input type="radio" name="video" value="" <?php checked( '', $f['video'] ); ?>>
					<span class="chip__face">すべて</span>
				</label>
				<label class="chip">
					<input type="radio" name="video" value="yes" <?php checked( 'yes', $f['video'] ); ?>>
					<span class="chip__face">あり</span>
				</label>
				<label class="chip">
					<input type="radio" name="video" value="no" <?php checked( 'no', $f['video'] ); ?>>
					<span class="chip__face">なし</span>
				</label>
			</div>
		</fieldset>

		<div class="filter__footer">
			<button class="filter__clear" type="button" data-filter-clear>選択をクリア</button>
			<div class="filter__actions">
				<button class="btn btn--outline" type="button" data-filter-cancel>キャンセル</button>
				<button class="btn btn--primary" type="submit">絞り込む</button>
			</div>
		</div>
	</form>
</dialog>
