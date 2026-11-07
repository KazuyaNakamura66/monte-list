<?php
/**
 * ページが見つからないとき。
 */

get_header();
get_template_part( 'template-parts/site-header' );
?>
<main class="not-found">
	<div class="container">
		<p class="not-found__code">404</p>
		<h1 class="not-found__title">ページが見つかりませんでした</h1>
		<p class="not-found__text">お探しのページは、移動または削除された可能性があります。<br>キーワードで探すか、一覧からお探しください。</p>

		<form class="search not-found__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="nf-search">検索</label>
			<input id="nf-search" class="search__input" type="search" name="s" placeholder="活動名・キーワードで検索">
		</form>

		<p><a class="btn btn--primary btn--link" href="<?php echo esc_url( home_url( '/' ) ); ?>">活動一覧へ</a></p>
	</div>
</main>
<?php
get_footer();
