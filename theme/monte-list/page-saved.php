<?php
/**
 * 保存した活動の一覧。中身は assets/js/main.js が読み込む。
 */

get_header();
?>
<?php get_template_part( 'template-parts/site-header' ); ?>

<main class="catalog">
	<div class="container">
		<h1 class="catalog__heading">保存した活動</h1>
		<p class="saved__note">保存した活動は、このブラウザにだけ記録されます。</p>

		<div class="card-grid" data-saved-list data-endpoint="<?php echo esc_url( rest_url( 'monte/v1/cards' ) ); ?>"></div>
		<p class="catalog__empty" data-saved-empty hidden>
			まだ保存した活動はありません。<br>
			気になる活動のしおりボタンを押すと、ここに表示されます。
		</p>

		<p class="saved__back"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">一覧に戻る</a></p>
	</div>
</main>
<?php
get_footer();
