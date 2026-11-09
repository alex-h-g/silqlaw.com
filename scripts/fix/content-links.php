<?php
/**
 * Point links and images inside articles/pages straight at their final address. Dry run by default.
 *  - Links to retired URLs (theme/silq/inc/redirects.php) → the page they redirect to, so no link goes through a redirect.
 *  - Images whose file is missing but whose media-library copy exists elsewhere → the working copy.
 *
 *   wp eval-file scripts/fix/content-links.php [commit]
 *
 * @package silq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with wp eval-file\n" );
}
global $wpdb;
$commit  = in_array( 'commit', $args ?? array(), true );
$uploads = wp_get_upload_dir();

$swap = array();
foreach ( silq_redirects() as $old => $new ) {
	foreach ( array( 'https://silqlaw.com', 'https://www.silqlaw.com', 'http://silqlaw.com', '' ) as $prefix ) {
		$swap[ 'href="' . $prefix . $old . '"' ] = 'href="' . $prefix . $new . '"';
		$swap[ 'href="' . $prefix . rtrim( $old, '/' ) . '"' ] = 'href="' . $prefix . $new . '"';
	}
}

// Missing image file => media-library file that exists (relative to uploads/).
$images = array(
	'2025/07/importing-alcohol-reduced-421x1024.webp'                         => '2026/06/importing-alcohol-reduced-scaled-1.webp',
	'2025/10/Draft-Startup-Promotion-and-Development-Act-%E2%80%94-Summary.png' => '2026/06/Draft-Startup-Promotion-and-Development-Act-%E2%80%94-Summary.png',
	'2025/10/Draft-Startup-Promotion-and-Development-Act-—-Summary.png'      => '2026/06/Draft-Startup-Promotion-and-Development-Act-—-Summary.png',
);
foreach ( $images as $from => $to ) {
	$from_file = $uploads['basedir'] . '/' . rawurldecode( $from );
	$to_file   = $uploads['basedir'] . '/' . rawurldecode( $to );
	if ( file_exists( $from_file ) ) {
		continue; // not broken here
	}
	if ( ! file_exists( $to_file ) ) {
		WP_CLI::warning( "replacement for {$from} not found — left as is" );
		continue;
	}
	$swap[ '/wp-content/uploads/' . $from ] = '/wp-content/uploads/' . $to;
}

$rows = $wpdb->get_results( "SELECT ID, post_title, post_content FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ('post','page')" );
$n    = 0;
foreach ( $rows as $r ) {
	$new = strtr( $r->post_content, $swap );
	if ( $new === $r->post_content ) {
		continue;
	}
	$changes = array();
	foreach ( $swap as $a => $b ) {
		if ( str_contains( $r->post_content, $a ) ) {
			$changes[] = str_replace( array( 'href="', '"' ), '', $a ) . ' → ' . str_replace( array( 'href="', '"' ), '', $b );
		}
	}
	WP_CLI::log( "{$r->ID} {$r->post_title}\n    " . implode( "\n    ", array_unique( $changes ) ) );
	$n++;
	if ( $commit ) {
		$wpdb->update( $wpdb->posts, array( 'post_content' => $new ), array( 'ID' => $r->ID ) );
		clean_post_cache( (int) $r->ID );
	}
}
// Yoast keeps its own copy of each page's address (canonical, sitemap, schema). Drop stale copies so Yoast rebuilds them:
// pages whose address changed (e.g. the merged Real Estate page) and pages that are no longer published.
$t = $wpdb->prefix . 'yoast_indexable';
if ( $wpdb->get_var( "SHOW TABLES LIKE '{$t}'" ) === $t ) {
	$stale = array();
	foreach ( $wpdb->get_results( "SELECT id, object_id, permalink FROM {$t} WHERE object_type = 'post'" ) as $r ) {
		$p = get_post( (int) $r->object_id );
		if ( ! $p || ! in_array( $p->post_status, array( 'publish', 'inherit' ), true ) ) {
			$stale[] = (int) $r->id;
			continue;
		}
		if ( $r->permalink && untrailingslashit( $r->permalink ) !== untrailingslashit( (string) get_permalink( $p ) ) ) {
			$stale[] = (int) $r->id;
			WP_CLI::log( "Yoast address for {$p->ID} {$p->post_title}: {$r->permalink} → " . get_permalink( $p ) );
		}
	}
	WP_CLI::log( sprintf( 'Yoast: %d stale entries %s', count( $stale ), $commit ? 'removed (rebuilt on next visit)' : 'to remove' ) );
	if ( $commit && $stale ) {
		$ids = implode( ',', $stale );
		$wpdb->query( "DELETE FROM {$t} WHERE id IN ({$ids})" );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}yoast_indexable_hierarchy WHERE indexable_id IN ({$ids}) OR ancestor_id IN ({$ids})" );
		foreach ( array( 11966, 12012 ) as $pid ) {
			clean_post_cache( $pid );
		}
	}
}

WP_CLI::success( sprintf( '%d page(s) %s.', $n, $commit ? 'updated' : 'would be updated — add "commit" to write' ) );
