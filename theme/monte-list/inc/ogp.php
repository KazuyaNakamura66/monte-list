<?php
/**
 * LINE・SNS などで URL を共有したときのカード表示（OGP）。
 * noindex とは別物で、検索結果には出ないまま共有時の見た目だけ整える。
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	$site  = get_bloginfo( 'name' );
	$title = $site;
	$desc  = 'おうちでできるモンテッソーリの活動・教具をまとめたカタログです。';
	$url   = home_url( add_query_arg( array() ) );
	$image = '';
	$type  = 'website';

	if ( is_singular( MONTE_POST_TYPES ) ) {
		$id    = get_queried_object_id();
		$title = get_the_title( $id ) . ' | ' . $site;
		$lead  = function_exists( 'get_field' ) ? ( get_field( 'catch', $id ) ?: get_field( 'lead', $id ) ) : '';
		$desc  = $lead ? wp_strip_all_tags( $lead ) : $desc;
		$url   = get_permalink( $id );
		$image = get_the_post_thumbnail_url( $id, 'monte-main' ) ?: '';
		$type  = 'article';
	} elseif ( is_tax() ) {
		$title = single_term_title( '', false ) . ' | ' . $site;
	}

	$tags = array(
		'og:site_name'   => $site,
		'og:title'       => $title,
		'og:description' => $desc,
		'og:url'         => $url,
		'og:type'        => $type,
		'og:locale'      => 'ja_JP',
		'og:image'       => $image,
	);
	foreach ( $tags as $property => $content ) {
		if ( $content ) {
			printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $property ), esc_attr( $content ) );
		}
	}
	printf( '<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary' );
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
}, 5 );
