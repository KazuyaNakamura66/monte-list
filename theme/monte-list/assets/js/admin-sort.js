/* global jQuery, monteSort */
/**
 * 管理画面の記事一覧で、行をドラッグして並び順を変える。
 */
jQuery(function ($) {
	const $list = $('#the-list');
	if (!$list.length || $list.find('tr.no-items').length) return;

	$('body').addClass('monte-sortable');
	$('.wp-header-end').after(
		'<p class="monte-sort-note">行をドラッグすると、サイトの一覧での表示順を変更できます。</p>'
	);

	$list.sortable({
		items: '> tr',
		axis: 'y',
		cursor: 'grabbing',
		helper(e, tr) {
			// ドラッグ中も列幅を保つ
			const $orig = tr.children();
			const $helper = tr.clone();
			$helper.children().each(function (i) {
				$(this).width($orig.eq(i).width());
			});
			return $helper;
		},
		update() {
			const ids = $list.children('tr').map(function () {
				return this.id.replace('post-', '');
			}).get();

			$list.sortable('disable').css('opacity', 0.6);
			$.post(monteSort.ajaxUrl, {
				action: 'monte_sort',
				nonce: monteSort.nonce,
				postType: monteSort.postType,
				ids,
			})
				.fail(() => {
					alert('並び順を保存できませんでした。ページを再読み込みしてやり直してください。');
				})
				.always(() => {
					$list.sortable('enable').css('opacity', 1);
				});
		},
	});
});
