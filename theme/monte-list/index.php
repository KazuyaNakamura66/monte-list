<?php
/**
 * 一覧（トップ・領域別・検索）共通テンプレート。
 * front-page.php / taxonomy-area.php / search.php がない場合もここに来る。
 */

get_header();
get_template_part( 'template-parts/catalog' );
get_footer();
