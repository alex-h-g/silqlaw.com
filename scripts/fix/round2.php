<?php
/**
 * 5 Nov fixes. Dry run by default.  wp eval-file scripts/fix/round2.php [commit]
 *  - Switch off the old Elementor Theme Builder templates (header, footer, tag archive). They overrode the theme,
 *    e.g. every /topics/… page. Set to draft with their display conditions cleared — nothing is deleted.
 *  - Create the Topics page at /topics/ (theme template page-topics.php).
 *  - Phone number in page content: old numbers → +66 92 258 4527.
 *
 * @package silq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with wp eval-file\n" );
}
global $wpdb;
$commit = in_array( 'commit', $args ?? array(), true );
$log    = fn( string $m ) => WP_CLI::log( ( $commit ? '✓ ' : '· ' ) . $m );

// 1. Elementor Theme Builder templates.
$conditions = get_option( 'elementor_pro_theme_builder_conditions', array() );
$templates  = get_posts( array( 'post_type' => 'elementor_library', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_key' => '_elementor_template_type', 'meta_value' => array( 'header', 'footer', 'archive', 'single', 'single-post', 'single-page', 'search-results', 'error-404' ), 'meta_compare' => 'IN' ) );
foreach ( $templates as $t ) {
	$log( sprintf( 'Elementor template %d "%s" (%s) → draft, conditions cleared', $t->ID, $t->post_title, get_post_meta( $t->ID, '_elementor_template_type', true ) ) );
	if ( $commit ) {
		update_post_meta( $t->ID, '_silq_old_conditions', get_post_meta( $t->ID, '_elementor_conditions', true ) );
		update_post_meta( $t->ID, '_elementor_conditions', array() );
		wp_update_post( array( 'ID' => $t->ID, 'post_status' => 'draft' ) );
		foreach ( $conditions as $loc => $list ) {
			unset( $conditions[ $loc ][ $t->ID ] );
			if ( empty( $conditions[ $loc ] ) ) {
				unset( $conditions[ $loc ] );
			}
		}
	}
}
if ( $commit ) {
	update_option( 'elementor_pro_theme_builder_conditions', $conditions );
}

// 2. Topics page.
$topics = get_page_by_path( 'topics' );
if ( ! $topics || 'publish' !== $topics->post_status ) {
	$log( 'create page /topics/' );
	if ( $commit ) {
		$id = wp_insert_post( array( 'ID' => $topics->ID ?? 0, 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Topics', 'post_name' => 'topics', 'post_content' => '' ) );
		update_post_meta( $id, '_wp_page_template', 'page-topics.php' );
		update_post_meta( $id, '_yoast_wpseo_title', 'Topics — Thai Law Articles by Subject | SILQ Law' );
		update_post_meta( $id, '_yoast_wpseo_metadesc', 'Browse SILQ Law articles by topic: property, foreign investment, company registration, visas, nominee rules, tourism and more.' );
	}
}

// 3. Phone number.
$old = '/(?:\+66\s?|0)(?:89[\s-]?920[\s-]?4451|2[\s-]?107[\s-]?1882)/';
$map = array( 'tel:+66899204451' => 'tel:+66922584527', 'wa.me/66899204451' => 'wa.me/66922584527', 'tel:+6621071882' => 'tel:+66922584527' );
$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_status IN ('publish','draft','private') AND post_type IN ('post','page') AND post_content REGEXP '899204451|89 920 4451|089 920 4451|2 107 1882|21071882'" );
foreach ( $ids as $id ) {
	$c = (string) get_post_field( 'post_content', $id );
	$n = preg_replace( $old, '+66 92 258 4527', strtr( $c, $map ) );
	$n = str_replace( '+66899204451', '+66922584527', $n );
	if ( $n !== $c ) {
		$log( "phone number → +66 92 258 4527 in {$id} " . get_the_title( $id ) );
		if ( $commit ) {
			wp_update_post( array( 'ID' => (int) $id, 'post_content' => $n ) );
		}
	}
}

if ( $commit ) {
	flush_rewrite_rules( false );
}
WP_CLI::success( $commit ? 'Committed.' : 'Dry run. Add "commit" to write.' );
