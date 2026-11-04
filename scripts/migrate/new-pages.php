<?php
/**
 * Create new service pages from content/*.html (idempotent: updates if the slug exists). Dry run by default.
 * Run from the repo root on the server:  wp eval-file scripts/migrate/new-pages.php [commit]
 *
 * @package silq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with wp eval-file\n" );
}
$commit = in_array( 'commit', $args ?? array(), true );
$dir    = dirname( __DIR__, 2 ) . '/content';
$parent = get_page_by_path( 'our-services' );

$pages = array(
	array(
		'slug'       => 'construction-property-development',
		'title'      => 'Construction & Property Development',
		'file'       => 'construction-property-development.html',
		'eyebrow'    => 'Property & building',
		'standfirst' => 'Land checks, building permits, land allocation, condominium registration and construction contracts — for owners, developers and contractors.',
		'img'        => 10808,
		'seo_title'  => 'Construction & Property Development Lawyers in Thailand | SILQ Law',
		'seo_desc'   => 'Building permits, land subdivision, condominium registration and construction contracts in Thailand, explained. Advice for owners, developers and contractors.',
	),
);

foreach ( $pages as $p ) {
	$content  = (string) file_get_contents( $dir . '/' . $p['file'] );
	$existing = get_page_by_path( 'our-services/' . $p['slug'] );
	WP_CLI::log( sprintf( '%s /our-services/%s/ (%d words)', $existing ? 'update' : 'create', $p['slug'], str_word_count( wp_strip_all_tags( $content ) ) ) );
	if ( ! $commit ) {
		continue;
	}
	$id = wp_insert_post(
		array(
			'ID'           => $existing ? $existing->ID : 0,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_parent'  => $parent->ID,
			'post_title'   => $p['title'],
			'post_name'    => $p['slug'],
			'post_content' => $content,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	update_post_meta( $id, '_wp_page_template', 'page-practice.php' );
	update_post_meta( $id, '_silq_eyebrow', $p['eyebrow'] );
	update_post_meta( $id, '_silq_standfirst', $p['standfirst'] );
	update_post_meta( $id, '_yoast_wpseo_title', $p['seo_title'] );
	update_post_meta( $id, '_yoast_wpseo_metadesc', $p['seo_desc'] );
	set_post_thumbnail( $id, $p['img'] );
	WP_CLI::log( "  → ID {$id}" );
}
WP_CLI::success( $commit ? 'Committed.' : 'Dry run. Add "commit" to write.' );
